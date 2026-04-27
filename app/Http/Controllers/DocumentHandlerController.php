<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DocumentHandlerController extends Controller
{
    private const WORKFLOW_ROLES = [
        'operator',
        'team_verifikasi',
        'perpajakan',
        'akutansi',
        'pembayaran',
    ];

    public function update(Request $request, Dokumen $dokumen): JsonResponse
    {
        $validated = $request->validate([
            'target_handler' => ['required', 'string', 'max:50'],
        ]);

        $userRole = $this->normalizeRole((string) (auth()->user()?->role ?? ''));

        if (in_array($userRole, ['admin', 'programmer'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin dan programmer tidak menggunakan alur pengurus dokumen.',
            ], 403);
        }

        $currentHandler = $this->normalizeRole((string) ($dokumen->current_handler ?? 'operator'));
        $targetHandler = $this->normalizeRole($validated['target_handler']);

        if (!$this->isValidTarget($targetHandler)) {
            throw ValidationException::withMessages([
                'target_handler' => 'Pengurus dokumen tidak valid.',
            ]);
        }

        if ($userRole !== $currentHandler) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengurus dokumen saat ini yang boleh mengubah kolom Pengurus Dokumen.',
            ], 403);
        }

        if ($this->hasPendingApproval($dokumen)) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen sedang menunggu approval. Pengurus tidak dapat diubah sampai approval diproses.',
            ], 422);
        }

        if ($targetHandler === $currentHandler) {
            return response()->json([
                'success' => true,
                'message' => 'Pengurus dokumen tidak berubah.',
                'handler' => $targetHandler,
            ]);
        }

        try {
            DB::beginTransaction();

            if ($targetHandler === 'team_verifikasi') {
                $this->moveDirectlyToTeamVerifikasi($dokumen, $currentHandler);
                $message = 'Dokumen langsung masuk ke daftar Team Verifikasi.';
            } elseif ($targetHandler === 'operator') {
                $this->returnDirectlyToOperator($dokumen, $currentHandler);
                $message = 'Dokumen dikembalikan ke Operator.';
            } elseif (str_starts_with($targetHandler, 'bagian_')) {
                $this->returnDirectlyToBagian($dokumen, $currentHandler, $targetHandler);
                $message = 'Dokumen dikembalikan ke bagian terkait.';
            } else {
                $this->sendToApprovalInbox($dokumen, $currentHandler, $targetHandler);
                $message = 'Dokumen dikirim ke inbox approval ' . Dokumen::getRoleDisplayNameIndo($targetHandler) . '.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'handler' => $targetHandler,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update document handler', [
                'document_id' => $dokumen->id,
                'current_handler' => $currentHandler,
                'target_handler' => $targetHandler,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah pengurus dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function moveDirectlyToTeamVerifikasi(Dokumen $dokumen, string $sourceRole): void
    {
        $this->markSourceProcessed($dokumen, $sourceRole);
        $this->clearPendingStatuses($dokumen);

        $dokumen->setStatusForRole('team_verifikasi', DokumenStatus::STATUS_APPROVED, $sourceRole);

        $teamVerifikasiData = $dokumen->getDataForRole('team_verifikasi');
        $teamVerifikasiReceivedAt = $teamVerifikasiData?->received_at ?? now();

        if ($sourceRole === 'operator' && $teamVerifikasiData?->received_at && $teamVerifikasiData?->processed_at) {
            $pausedSeconds = $teamVerifikasiData->processed_at->diffInSeconds(now());
            $teamVerifikasiReceivedAt = $teamVerifikasiData->received_at->copy()->addSeconds($pausedSeconds);
        }

        $dokumen->setDataForRole('team_verifikasi', [
            'received_at' => $teamVerifikasiReceivedAt,
            'processed_at' => null,
        ]);

        if ($sourceRole === 'operator') {
            $dokumen->setDisplayStatusForRole('operator', 'terkirim');
        } else {
            $dokumen->setDisplayStatusForRole($sourceRole, 'dikembalikan');
        }
        $dokumen->setDisplayStatusForRole('team_verifikasi', 'sedang_diproses');

        $dokumen->update([
            'current_handler' => 'team_verifikasi',
            'status' => $sourceRole === 'operator' ? 'sedang diproses' : 'returned_to_verifikasi',
            'current_stage' => 'reviewer',
            'last_action_status' => $sourceRole === 'operator' ? 'auto_accepted_by_verifikasi' : 'returned_to_verifikasi',
            'return_source' => $sourceRole === 'operator' ? null : $sourceRole,
            'return_reason' => $sourceRole === 'operator' ? null : 'Dikembalikan melalui perubahan Pengurus Dokumen.',
            'returned_at' => $sourceRole === 'operator' ? null : now(),
        ]);

        ActivityLogHelper::logSent($dokumen, 'team_verifikasi', $sourceRole);
        ActivityLogHelper::logReceived($dokumen, 'team_verifikasi');
    }

    private function returnDirectlyToOperator(Dokumen $dokumen, string $sourceRole): void
    {
        $this->markSourceProcessed($dokumen, $sourceRole);
        $this->clearPendingStatuses($dokumen);

        $dokumen->update([
            'current_handler' => 'operator',
            'status' => 'returned_to_operator',
            'return_source' => $sourceRole,
            'return_reason' => 'Dikembalikan melalui perubahan Pengurus Dokumen.',
            'returned_at' => now(),
            'last_action_status' => 'returned_to_operator',
        ]);

        $dokumen->setDisplayStatusForRole($sourceRole, 'dikembalikan');
        $dokumen->setDisplayStatusForRole('operator', 'dikembalikan');
        ActivityLogHelper::logReturned($dokumen, 'operator', 'Dikembalikan melalui perubahan Pengurus Dokumen.', $sourceRole);
    }

    private function returnDirectlyToBagian(Dokumen $dokumen, string $sourceRole, string $targetHandler): void
    {
        $this->markSourceProcessed($dokumen, $sourceRole);
        $this->clearPendingStatuses($dokumen);

        $bagianCode = strtoupper(substr($targetHandler, strlen('bagian_')));

        $dokumen->update([
            'current_handler' => $targetHandler,
            'status' => 'returned_to_bidang',
            'return_source' => $bagianCode,
            'return_reason' => 'Dikembalikan melalui perubahan Pengurus Dokumen.',
            'returned_at' => now(),
            'last_action_status' => 'returned_to_bidang',
        ]);

        $dokumen->setDisplayStatusForRole($sourceRole, 'dikembalikan');
        ActivityLogHelper::logReturned($dokumen, $bagianCode, 'Dikembalikan melalui perubahan Pengurus Dokumen.', $sourceRole);
    }

    private function sendToApprovalInbox(Dokumen $dokumen, string $sourceRole, string $targetRole): void
    {
        $this->markSourceProcessed($dokumen, $sourceRole);

        if ($dokumen->status && str_starts_with((string) $dokumen->status, 'returned_')) {
            $dokumen->update([
                'return_source' => null,
                'return_reason' => null,
                'returned_at' => null,
                'pengembalian_awaiting_fix' => false,
                'returned_from_perpajakan_fixed_at' => now(),
            ]);
        }

        $labelMap = [
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
        ];

        $dokumen->sendToInbox($labelMap[$targetRole] ?? $targetRole);

        ActivityLogHelper::logSent($dokumen, $targetRole, $sourceRole);
    }

    private function markSourceProcessed(Dokumen $dokumen, string $sourceRole): void
    {
        if (!in_array($sourceRole, self::WORKFLOW_ROLES, true)) {
            return;
        }

        $sourceData = $dokumen->getDataForRole($sourceRole);
        if ($sourceData && $sourceData->received_at && !$sourceData->processed_at) {
            $sourceData->processed_at = now();
            $sourceData->save();
        }
    }

    private function clearPendingStatuses(Dokumen $dokumen): void
    {
        $dokumen->roleStatuses()
            ->where('status', DokumenStatus::STATUS_PENDING)
            ->update([
                'status' => DokumenStatus::STATUS_RETURNED,
                'status_changed_at' => now(),
                'changed_by' => auth()->user()?->name ?? 'System',
                'notes' => 'Dibatalkan karena Pengurus Dokumen diubah.',
            ]);
    }

    private function hasPendingApproval(Dokumen $dokumen): bool
    {
        return $dokumen->roleStatuses()
            ->where('status', DokumenStatus::STATUS_PENDING)
            ->exists();
    }

    private function isValidTarget(string $targetHandler): bool
    {
        if (in_array($targetHandler, self::WORKFLOW_ROLES, true)) {
            return true;
        }

        if (!str_starts_with($targetHandler, 'bagian_')) {
            return false;
        }

        $bagianCode = strtoupper(substr($targetHandler, strlen('bagian_')));

        return Bagian::active()
            ->whereRaw('UPPER(kode) = ?', [$bagianCode])
            ->exists();
    }

    private function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));

        return match ($role) {
            'verifikasi', 'team verifikasi', 'tim verifikasi', 'ibu yuni', 'ibu b' => 'team_verifikasi',
            'tim perpajakan', 'team perpajakan' => 'perpajakan',
            'akuntansi', 'tim akuntansi', 'team akuntansi', 'team akutansi', 'tim akutansi' => 'akutansi',
            'tim pembayaran', 'team pembayaran' => 'pembayaran',
            default => str_replace(' ', '_', $role),
        };
    }
}
