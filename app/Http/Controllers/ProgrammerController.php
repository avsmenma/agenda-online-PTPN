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

                    // Simulate manual workflow - step by step through each role
                    $this->simulateManualWorkflow($dokumen, $user->name ?? 'Programmer');

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
     * Simulate manual workflow - step-by-step role transitions
     * Each step: sendToRoleInbox -> approveFromRoleInbox
     * This ensures proper deadline tracking and status consistency
     */
    private function simulateManualWorkflow(Dokumen $dokumen, string $performedBy): void
    {
        $currentRole = $dokumen->current_handler ?? 'operator';

        // Normalize role name
        $normalizedCurrentRole = strtolower($currentRole);
        if ($normalizedCurrentRole === 'verifikasi') {
            $normalizedCurrentRole = 'team_verifikasi';
        }

        // Get workflow path based on current position
        $workflowPath = $this->getWorkflowPath($normalizedCurrentRole);

        Log::info("Bulk workflow simulation starting for {$dokumen->nomor_agenda}", [
            'from_role' => $normalizedCurrentRole,
            'path' => $workflowPath,
            'performed_by' => $performedBy
        ]);

        // Execute each step in the workflow
        foreach ($workflowPath as $index => $targetRole) {
            // Step 1: Send to target role's inbox
            $dokumen->sendToRoleInbox($targetRole, $performedBy);

            Log::info("Bulk workflow: {$dokumen->nomor_agenda} sent to {$targetRole} inbox");

            // Step 2: Auto-approve from inbox (sets received_at, processed_at, deadline)
            $dokumen->approveFromRoleInbox($targetRole);

            Log::info("Bulk workflow: {$dokumen->nomor_agenda} approved in {$targetRole}");
        }

        // Log final activity
        \App\Models\DokumenActivityLog::create([
            'dokumen_id' => $dokumen->id,
            'stage' => 'pembayaran',
            'action' => 'bulk_workflow_complete',
            'action_description' => "Dokumen dipercepat ke Pembayaran via workflow simulasi dari {$normalizedCurrentRole} (Programmer bulk operation)",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'method' => 'simulate_manual_workflow',
                'from_role' => $normalizedCurrentRole,
                'workflow_path' => $workflowPath,
            ],
        ]);

        Log::info("Bulk workflow completed: {$dokumen->nomor_agenda}", [
            'from_role' => $normalizedCurrentRole,
            'to_role' => 'pembayaran',
            'steps' => count($workflowPath),
            'by' => $performedBy
        ]);
    }

    /**
     * Get workflow path to reach pembayaran from current role
     */
    private function getWorkflowPath(string $currentRole): array
    {
        // Define paths from each role to pembayaran
        // Direct path: skip perpajakan/akutansi, go straight to pembayaran
        $paths = [
            'operator' => ['team_verifikasi', 'pembayaran'],
            'team_verifikasi' => ['pembayaran'],
            'perpajakan' => ['pembayaran'],
            'akutansi' => ['pembayaran'],
            'pembayaran' => [], // Already at destination
        ];

        return $paths[strtolower($currentRole)] ?? ['pembayaran'];
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
