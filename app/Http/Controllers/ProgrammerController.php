<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class ProgrammerController extends Controller
{
    /**
     * Programmer Dashboard
     */
    public function dashboard(): View
    {
        $stats = [
            'total_dokumens' => Dokumen::count(),
            'dokumens_operator' => Dokumen::where('current_handler', 'operator')
                ->orWhere('created_by', 'operator')
                ->whereNull('current_handler')
                ->count(),
            'dokumens_verifikasi' => Dokumen::where('current_handler', 'team_verifikasi')->count(),
            'dokumens_perpajakan' => Dokumen::where('current_handler', 'perpajakan')->count(),
            'dokumens_akutansi' => Dokumen::where('current_handler', 'akutansi')->count(),
            'dokumens_pembayaran' => Dokumen::where('current_handler', 'pembayaran')->count(),
        ];

        return view('programmer.dashboard', compact('stats'));
    }

    /**
     * Show bulk direct to payment form
     */
    public function showDirectToPaymentForm(): View
    {
        return view('programmer.bulk-to-payment');
    }

    /**
     * Preview documents before sending to payment
     */
    public function previewDocuments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agendas' => 'required|string',
        ]);

        // Parse nomor_agenda list (support comma, newline, space separated)
        $nomorAgendas = $this->parseNomorAgendaList($validated['nomor_agendas']);

        // Find documents
        $found = [];
        $notFound = [];

        foreach ($nomorAgendas as $nomorAgenda) {
            $dokumen = Dokumen::where('nomor_agenda', $nomorAgenda)->first();

            if ($dokumen) {
                $found[] = [
                    'id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'uraian_spp' => $dokumen->uraian_spp,
                    'nilai_rupiah' => number_format((float) ($dokumen->nilai_rupiah ?? 0), 0, ',', '.'),
                    'current_handler' => $dokumen->current_handler ?? 'operator',
                    'status' => $dokumen->status,
                ];
            } else {
                $notFound[] = $nomorAgenda;
            }
        }

        return response()->json([
            'success' => true,
            'found' => $found,
            'not_found' => $notFound,
            'total_found' => count($found),
            'total_not_found' => count($notFound),
        ]);
    }

    /**
     * Bulk send documents directly to payment, bypassing normal workflow
     */
    public function bulkDirectToPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agendas' => 'required|string',
        ]);

        $user = Auth::user();

        // Parse nomor_agenda list
        $nomorAgendas = $this->parseNomorAgendaList($validated['nomor_agendas']);

        $processed = 0;
        $failed = 0;
        $errors = [];
        $processedDocs = [];

        DB::beginTransaction();
        try {
            foreach ($nomorAgendas as $nomorAgenda) {
                try {
                    $dokumen = Dokumen::where('nomor_agenda', $nomorAgenda)->first();

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Dokumen {$nomorAgenda} tidak ditemukan";
                        continue;
                    }

                    // Skip if already at pembayaran
                    if ($dokumen->current_handler === 'pembayaran') {
                        $failed++;
                        $errors[] = "Dokumen {$nomorAgenda} sudah di Pembayaran";
                        continue;
                    }

                    // Direct send to pembayaran (bypass approval workflow)
                    $this->sendDirectToPembayaran($dokumen, $user->name ?? 'Programmer');

                    $processed++;
                    $processedDocs[] = $nomorAgenda;

                    Log::info("Programmer bulk direct-to-payment: Document {$nomorAgenda} sent to pembayaran by {$user->name}");

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing {$nomorAgenda}: " . $e->getMessage();
                    Log::error("Programmer bulk error for {$nomorAgenda}: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'processed_docs' => $processedDocs,
                'errors' => $errors,
                'message' => "Berhasil mengirim {$processed} dokumen ke Pembayaran" . ($failed > 0 ? ", {$failed} gagal" : '')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programmer bulk direct-to-payment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send document directly to pembayaran using existing workflow
     * Document stays visible in current role with "Terkirim ke Pembayaran" status
     */
    private function sendDirectToPembayaran(Dokumen $dokumen, string $performedBy): void
    {
        $currentRole = $dokumen->current_handler ?? 'operator';

        // Normalize role name for consistency
        $normalizedCurrentRole = strtolower($currentRole);
        if ($normalizedCurrentRole === 'verifikasi') {
            $normalizedCurrentRole = 'team_verifikasi';
        }

        // 1. Ensure current role has received_at and processed_at set (stops deadline)
        $currentRoleData = $dokumen->getDataForRole($normalizedCurrentRole);
        if (!$currentRoleData) {
            // Create role data if doesn't exist
            $currentRoleData = DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => $normalizedCurrentRole,
                'received_at' => now(),
                'processed_at' => now(), // Stops deadline
            ]);
        } else {
            // Update to stop deadline
            $currentRoleData->update([
                'processed_at' => now(),
            ]);
        }

        // 2. Set current role's display_status to show "Terkirim ke Pembayaran"
        $dokumen->setDisplayStatusForRole($normalizedCurrentRole, 'terkirim_ke_pembayaran');

        // 3. Set status as approved for current role (so it shows as completed)
        $dokumen->setStatusForRole(
            $normalizedCurrentRole,
            DokumenStatus::STATUS_APPROVED,
            $performedBy,
            'Bulk direct to payment by programmer'
        );

        // 4. Create pembayaran role data with received_at (starts their deadline)
        $pembayaranData = $dokumen->getDataForRole('pembayaran');
        if (!$pembayaranData) {
            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'pembayaran',
                'received_at' => now(),
                'processed_at' => null,
            ]);
        } else {
            $pembayaranData->update([
                'received_at' => $pembayaranData->received_at ?? now(),
                'processed_at' => null,
            ]);
        }

        // 5. Set pembayaran status to pending (appears in their inbox)
        $dokumen->setStatusForRole(
            'pembayaran',
            DokumenStatus::STATUS_PENDING,
            $performedBy,
            'Received via bulk direct from programmer'
        );

        // 6. Update main document - keep current_handler as 'pembayaran' for inbox visibility
        // But the document also stays visible in previous role with "terkirim" status
        $dokumen->update([
            'current_handler' => 'pembayaran',
            'current_stage' => 'payment',
            'status' => 'sent_to_pembayaran',
            'last_action_status' => 'bulk_direct_to_payment',
        ]);

        // 7. Log activity
        \App\Models\DokumenActivityLog::create([
            'dokumen_id' => $dokumen->id,
            'stage' => 'pembayaran',
            'action' => 'bulk_direct_to_payment',
            'action_description' => "Dokumen dikirim langsung ke Pembayaran dari {$normalizedCurrentRole} (Programmer bulk operation)",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'method' => 'bulk_direct_to_payment',
                'from_role' => $normalizedCurrentRole,
                'skip_workflow' => true,
            ],
        ]);

        Log::info("Bulk direct to payment: {$dokumen->nomor_agenda}", [
            'from_role' => $normalizedCurrentRole,
            'to_role' => 'pembayaran',
            'by' => $performedBy
        ]);
    }


    /**
     * Parse nomor_agenda list from various input formats
     */
    private function parseNomorAgendaList(string $input): array
    {
        // Replace common separators with newline
        $normalized = str_replace([',', ';', "\t"], "\n", $input);

        // Split by newline and clean up
        $items = explode("\n", $normalized);

        $result = [];
        foreach ($items as $item) {
            $trimmed = trim($item);
            if (!empty($trimmed)) {
                $result[] = $trimmed;
            }
        }

        return array_unique($result);
    }

    /**
     * Check if user is programmer
     */
    public function isProgrammer(): bool
    {
        return Auth::user()?->role === 'programmer';
    }
}
