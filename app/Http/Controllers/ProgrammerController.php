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
                    'nilai_rupiah' => number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
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
     * Send document directly to pembayaran, bypassing normal workflow
     */
    private function sendDirectToPembayaran(Dokumen $dokumen, string $performedBy): void
    {
        // Mark all intermediate role data as processed
        $intermediateRoles = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi'];

        foreach ($intermediateRoles as $roleCode) {
            $roleData = $dokumen->getDataForRole($roleCode);

            if (!$roleData) {
                // Create role data if doesn't exist
                $roleData = DokumenRoleData::create([
                    'dokumen_id' => $dokumen->id,
                    'role_code' => $roleCode,
                    'received_at' => now(),
                    'processed_at' => now(),
                ]);
            } else if (!$roleData->processed_at) {
                $roleData->update(['processed_at' => now()]);
            }

            // Set status as approved for this role
            $dokumen->setStatusForRole($roleCode, DokumenStatus::STATUS_APPROVED, $performedBy, 'Bulk direct to payment by programmer');
        }

        // Create pembayaran role data
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
                'received_at' => now(),
                'processed_at' => null,
            ]);
        }

        // Set pembayaran status to pending
        $dokumen->setStatusForRole('pembayaran', DokumenStatus::STATUS_PENDING, $performedBy, 'Received via bulk direct from programmer');

        // Update main document fields
        $dokumen->update([
            'current_handler' => 'pembayaran',
            'current_stage' => 'payment',
            'status' => 'sent_to_pembayaran',
            'last_action_status' => 'bulk_direct_to_payment',
        ]);

        // Log activity
        \App\Models\DokumenActivityLog::create([
            'dokumen_id' => $dokumen->id,
            'stage' => 'pembayaran',
            'action' => 'bulk_direct_to_payment',
            'action_description' => 'Dokumen dikirim langsung ke Pembayaran melalui bulk operation (Programmer)',
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'method' => 'bulk_direct_to_payment',
                'skip_workflow' => true,
            ],
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
