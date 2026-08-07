<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Services\DocumentReturnNotifier;
use App\Support\HandlerOptions;
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

        if (in_array($userRole, ['admin', 'programmer'], true) || str_starts_with($userRole, 'bagian_')) {
            return response()->json([
                'success' => false,
                'message' => 'Role ini tidak menggunakan alur pengurus dokumen.',
            ], 403);
        }

        $currentHandler = $this->normalizeRole((string) ($dokumen->current_handler ?? 'operator'));
        $targetHandler = $this->normalizeRole($validated['target_handler']);
        $canReceiveReturnedBagian = $userRole === 'team_verifikasi'
            && $targetHandler === 'team_verifikasi'
            && $dokumen->status === 'returned_to_bidang';

        if (!$this->isValidTarget($targetHandler)) {
            throw ValidationException::withMessages([
                'target_handler' => 'Pengurus dokumen tidak valid.',
            ]);
        }

        if (!$canReceiveReturnedBagian && $userRole !== $currentHandler) {
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

        if ($canReceiveReturnedBagian) {
            try {
                DB::beginTransaction();

                $this->receiveBackFromBagian($dokumen);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen sudah kembali ke Tim Verifikasi.',
                    'handler' => $targetHandler,
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Failed to receive document back from bagian', [
                    'document_id' => $dokumen->id,
                    'current_handler' => $currentHandler,
                    'target_handler' => $targetHandler,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengembalikan dokumen ke Tim Verifikasi: ' . $e->getMessage(),
                ], 500);
            }
        }

        if ($targetHandler === $currentHandler) {
            return response()->json([
                'success' => true,
                'message' => 'Pengurus dokumen tidak berubah.',
                'handler' => $targetHandler,
            ]);
        }

        // Bagian wajib terisi sebelum dokumen MENINGGALKAN Operator menuju alur
        // keuangan. Draft quick-add boleh dibuat tanpa bagian, tapi tak boleh
        // dikirim lanjut tanpa bagian — kalau tidak, dokumen tak akan terlihat di
        // monitoring role Bagian (view-only) yang scoping-nya berdasarkan kolom `bagian`.
        $movingIntoWorkflow = in_array($targetHandler, self::WORKFLOW_ROLES, true) && $targetHandler !== 'operator';
        if ($currentHandler === 'operator' && $movingIntoWorkflow && empty(trim((string) $dokumen->bagian))) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom Bagian wajib diisi sebelum dokumen dikirim ke alur berikutnya.',
            ], 422);
        }

        // Dokumen hanya boleh dikembalikan ke bagian ASALNYA SENDIRI. Dropdown sudah
        // dipangkas ke satu pilihan (App\Support\HandlerOptions::forDokumen), tapi opsi
        // itu ditanam di data baris yang dikirim ke klien — jadi bisa diakali. Penegakan
        // sebenarnya ada di sini; tanpa ini pemangkasan dropdown cuma kosmetik.
        $alasanPengembalian = '';
        if (str_starts_with($targetHandler, 'bagian_')) {
            $penolakan = $this->tolakBilaBukanBagianAsal($dokumen, $targetHandler);
            if ($penolakan !== null) {
                return $penolakan;
            }

            // Alasan pengembalian WAJIB — dulu jalur ini menulis kalimat tetap
            // "Dikembalikan melalui perubahan Pengurus Dokumen." yang tidak memberi
            // tahu Bagian apa yang harus diperbaiki. Ambang 10..1000 karakter
            // disamakan dengan jalur pengembalian khusus (TeamVerifikasiController::
            // returnToBidang) supaya kedua pintu memakai aturan yang sama.
            $alasanPengembalian = trim((string) $request->input('return_reason', ''));

            if (mb_strlen($alasanPengembalian) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan pengembalian wajib diisi, minimal 10 karakter.',
                ], 422);
            }

            if (mb_strlen($alasanPengembalian) > 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan pengembalian maksimal 1000 karakter.',
                ], 422);
            }
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
                $this->returnDirectlyToBagian($dokumen, $currentHandler, $targetHandler, $alasanPengembalian);
                $message = 'Dokumen dikembalikan ke bagian terkait.';
            } else {
                $this->sendToApprovalInbox($dokumen, $currentHandler, $targetHandler);
                $message = 'Dokumen dikirim ke inbox approval ' . Dokumen::getRoleDisplayNameIndo($targetHandler) . '.';
            }

            DB::commit();

            // Notifikasi SETELAH commit — panggilan HTTP ke gateway WhatsApp tidak
            // boleh menahan transaksi database, dan kegagalannya tidak boleh
            // membatalkan pengembalian yang sudah sah.
            if (str_starts_with($targetHandler, 'bagian_')) {
                DocumentReturnNotifier::kirim(
                    $dokumen,
                    strtoupper(substr($targetHandler, strlen('bagian_'))),
                    $alasanPengembalian
                );
            }

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
            $dokumen->setDisplayStatusForRole($sourceRole, 'terkirim_verifikasi');
        }
        $dokumen->setDisplayStatusForRole('team_verifikasi', 'sedang_diproses');

        $dokumen->update([
            'current_handler' => 'team_verifikasi',
            'status' => $sourceRole === 'operator' ? 'sedang diproses' : 'returned_to_verifikasi',
            'current_stage' => 'reviewer',
            'last_action_status' => $sourceRole === 'operator' ? 'auto_accepted_by_verifikasi' : 'returned_to_verifikasi',
            'return_source' => $sourceRole === 'operator' ? null : $sourceRole,
            'return_reason' => $sourceRole === 'operator' ? null : 'Diserahkan kembali melalui perubahan Pengurus Dokumen.',
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

    private function returnDirectlyToBagian(Dokumen $dokumen, string $sourceRole, string $targetHandler, string $alasan): void
    {
        $this->markSourceProcessed($dokumen, $sourceRole);
        $this->clearPendingStatuses($dokumen);

        $bagianCode = strtoupper(substr($targetHandler, strlen('bagian_')));

        $dokumen->update([
            'current_handler' => $sourceRole === 'team_verifikasi' ? 'team_verifikasi' : $targetHandler,
            'status' => 'returned_to_bidang',
            'return_source' => $bagianCode,
            'return_reason' => $alasan,
            'returned_at' => now(),
            // Stempel KHUSUS kembali-ke-Bagian. Beda dari `returned_at` di atas yang
            // ditimpa setiap pengembalian ke siapa pun (termasuk ke Operator).
            'tanggal_kembali_ke_bagian' => now(),
            'last_action_status' => 'returned_to_bidang',
        ]);

        $dokumen->setDisplayStatusForRole($sourceRole, 'dikembalikan');
        ActivityLogHelper::logReturned($dokumen, $bagianCode, $alasan, $sourceRole);
    }

    private function receiveBackFromBagian(Dokumen $dokumen): void
    {
        $previousReturnSource = $dokumen->return_source;
        $teamVerifikasiData = $dokumen->getDataForRole('team_verifikasi');

        if ($teamVerifikasiData && $teamVerifikasiData->received_at && $teamVerifikasiData->processed_at) {
            $pausedSeconds = $teamVerifikasiData->processed_at->diffInSeconds(now());
            $teamVerifikasiData->received_at = $teamVerifikasiData->received_at->copy()->addSeconds($pausedSeconds);
            $teamVerifikasiData->processed_at = null;
            $teamVerifikasiData->save();
        } else {
            $dokumen->setDataForRole('team_verifikasi', [
                'received_at' => $teamVerifikasiData?->received_at ?? now(),
                'processed_at' => null,
            ]);
        }

        $dokumen->update([
            'current_handler' => 'team_verifikasi',
            'status' => 'sedang diproses',
            'current_stage' => 'reviewer',
            'return_source' => null,
            'return_reason' => null,
            'returned_at' => null,
            'last_action_status' => 'returned_from_bidang',
            // Stempel hasil koreksi Bagian. Ditaruh DI SINI, bukan di update(),
            // karena method ini hanya terpanggil lewat gerbang
            // $canReceiveReturnedBagian yang menuntut status dokumen
            // 'returned_to_bidang' — forward biasa operator→verifikasi jatuh ke
            // moveDirectlyToTeamVerifikasi() dan tidak boleh ikut terstempel.
            //
            // Sengaja DITIMPA tiap siklus koreksi (keputusan user 2026-08-07):
            // kolomnya sepasang dengan tanggal_kembali_ke_bagian yang juga ditimpa
            // tiap pengembalian, jadi keduanya selalu menunjuk siklus yang sama.
            'tanggal_hasil_koreksi_bagian' => now(),
        ]);

        $dokumen->setDisplayStatusForRole('team_verifikasi', 'sedang_diproses');
        ActivityLogHelper::log(
            $dokumen,
            'returned_from_bidang',
            'Dokumen kembali dari bagian ke Tim Verifikasi',
            'reviewer',
            'team_verifikasi',
            ['from' => $previousReturnSource]
        );
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

    /**
     * Kembalikan JsonResponse penolakan bila $targetHandler bukan bagian asal
     * dokumen; null bila boleh lanjut.
     *
     * Pencocokan memakai peta yang SAMA dengan penyusun dropdown
     * (App\Support\HandlerOptions::bagianMap) supaya apa yang ditawarkan UI dan apa
     * yang diterima server tidak pernah berbeda aturan — termasuk toleransi baris
     * lama yang menyimpan nama bagian, bukan kodenya.
     */
    private function tolakBilaBukanBagianAsal(Dokumen $dokumen, string $targetHandler): ?JsonResponse
    {
        $bagianDokumen = strtoupper(trim((string) $dokumen->bagian));

        if ($bagianDokumen === '') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini belum memiliki Bagian, sehingga tidak bisa dikembalikan ke bagian manapun.',
            ], 422);
        }

        $asal = HandlerOptions::bagianMap()[$bagianDokumen] ?? null;

        if ($asal === null) {
            return response()->json([
                'success' => false,
                'message' => "Bagian dokumen ('{$dokumen->bagian}') tidak terdaftar sebagai bagian aktif.",
            ], 422);
        }

        if ($asal['value'] !== $targetHandler) {
            return response()->json([
                'success' => false,
                'message' => "Dokumen hanya boleh dikembalikan ke bagian asalnya, yaitu {$asal['label']}.",
            ], 422);
        }

        return null;
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
        return \App\Support\Role::normalize($role);
    }
}
