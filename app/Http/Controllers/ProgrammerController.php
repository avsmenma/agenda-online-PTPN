<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DibayarKepada;
use App\Models\Dokumen;
use App\Models\DocumentTracking;
use App\Models\DokumenActivityLog;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Models\User;
use App\Traits\LogsProgrammerActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class ProgrammerController extends Controller
{
    use LogsProgrammerActivity;
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
     * Show bulk send to role form (Verifikasi / Perpajakan / Akutansi)
     */
    public function showBulkSendToRoleForm(): View
    {
        return view('programmer.bulk-send-to-role');
    }

    /**
     * Preview documents before bulk-send to a specific role
     */
    public function previewBulkSendToRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agendas' => 'required|string',
            'target_role'   => 'required|in:team_verifikasi,perpajakan,akutansi',
        ]);

        $targetRole    = $validated['target_role'];
        $nomorAgendas  = $this->parseNomorAgendaList($validated['nomor_agendas']);

        // Full workflow order — used to compare positions
        $workflowOrder = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $targetIndex   = array_search($targetRole, $workflowOrder);

        $actionable = [];
        $skipped    = [];
        $notFound   = [];

        foreach ($nomorAgendas as $nomorAgenda) {
            $dokumen = Dokumen::where('nomor_agenda', $nomorAgenda)->first();

            if (!$dokumen) {
                $notFound[] = $nomorAgenda;
                continue;
            }

            $currentHandler = strtolower($dokumen->current_handler ?? 'operator');
            if ($currentHandler === 'verifikasi') {
                $currentHandler = 'team_verifikasi';
            }

            $currentIndex = array_search($currentHandler, $workflowOrder);

            // If current position is already AT or PAST target → skip
            if ($currentIndex !== false && $currentIndex >= $targetIndex) {
                $skipped[] = [
                    'nomor_agenda'    => $dokumen->nomor_agenda,
                    'current_handler' => $currentHandler,
                    'reason'          => 'Sudah berada di atau melewati ' . $targetRole,
                ];
                continue;
            }

            $actionable[] = [
                'id'              => $dokumen->id,
                'nomor_agenda'    => $dokumen->nomor_agenda,
                'nomor_spp'       => $dokumen->nomor_spp,
                'uraian_spp'      => $dokumen->uraian_spp,
                'nilai_rupiah'    => number_format((float)($dokumen->nilai_rupiah ?? 0), 0, ',', '.'),
                'current_handler' => $currentHandler,
                'status'          => $dokumen->status,
            ];
        }

        return response()->json([
            'success'          => true,
            'target_role'      => $targetRole,
            'actionable'       => $actionable,
            'skipped'          => $skipped,
            'not_found'        => $notFound,
            'total_actionable' => count($actionable),
            'total_skipped'    => count($skipped),
            'total_not_found'  => count($notFound),
        ]);
    }

    /**
     * Bulk send documents to a chosen intermediate role (Verifikasi / Perpajakan / Akutansi)
     */
    public function bulkSendToRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agendas' => 'required|string',
            'target_role'   => 'required|in:team_verifikasi,perpajakan,akutansi',
        ]);

        set_time_limit(300);
        DB::disableQueryLog();

        $user       = Auth::user();
        $targetRole = $validated['target_role'];

        $nomorAgendas = $this->parseNomorAgendaList($validated['nomor_agendas']);

        // Full workflow order
        $workflowOrder = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $targetIndex   = array_search($targetRole, $workflowOrder);

        $processed    = 0;
        $failed       = 0;
        $skipped      = 0;
        $errors       = [];
        $processedDocs = [];

        $chunks = array_chunk($nomorAgendas, 50);

        Log::info("Programmer bulk-send-to-role: Start — target={$targetRole}, " . count($nomorAgendas) . " docs, " . count($chunks) . " chunks", [
            'by' => $user->name ?? 'Programmer',
        ]);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $nomorAgenda) {
                    try {
                        $dokumen = Dokumen::where('nomor_agenda', $nomorAgenda)->first();

                        if (!$dokumen) {
                            $failed++;
                            $errors[] = "Dokumen {$nomorAgenda} tidak ditemukan";
                            continue;
                        }

                        $currentHandler = strtolower($dokumen->current_handler ?? 'operator');
                        if ($currentHandler === 'verifikasi') {
                            $currentHandler = 'team_verifikasi';
                        }

                        $currentIndex = array_search($currentHandler, $workflowOrder);

                        // Skip if already at or past target
                        if ($currentIndex !== false && $currentIndex >= $targetIndex) {
                            $skipped++;
                            continue;
                        }

                        // Simulate workflow only up to target role
                        $this->simulateWorkflowToTarget($dokumen, $targetRole, $user->name ?? 'Programmer');

                        $processed++;
                        $processedDocs[] = $nomorAgenda;

                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "Error {$nomorAgenda}: " . $e->getMessage();
                        Log::error("Programmer bulk-send-to-role error for {$nomorAgenda}: " . $e->getMessage());
                    }
                }

                DB::commit();
                Log::info("Chunk " . ($chunkIndex + 1) . "/" . count($chunks) . " committed — {$processed} processed so far");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Chunk " . ($chunkIndex + 1) . " failed: " . $e->getMessage());
                $failed += count($chunk);
                $errors[] = "Chunk " . ($chunkIndex + 1) . " gagal: " . $e->getMessage();
            }
        }

        // Audit trail
        $roleLabels = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan'      => 'Perpajakan',
            'akutansi'        => 'Akutansi',
        ];
        $this->logActivity('bulk_send_to_role', null,
            "Bulk Send ke {$roleLabels[$targetRole]}: {$processed} berhasil" .
            ($skipped > 0 ? ", {$skipped} dilewati"  : '') .
            ($failed > 0  ? ", {$failed} gagal"       : '') .
            (count($processedDocs) <= 10 ? ' [' . implode(', ', array_slice($processedDocs, 0, 10)) . ']' : '')
        );

        return response()->json([
            'success'        => true,
            'processed'      => $processed,
            'skipped'        => $skipped,
            'failed'         => $failed,
            'processed_docs' => $processedDocs,
            'errors'         => $errors,
            'message'        => "Berhasil mengirim {$processed} dokumen ke {$roleLabels[$targetRole]}" .
                               ($skipped > 0 ? ", {$skipped} dilewati" : '') .
                               ($failed > 0  ? ", {$failed} gagal"     : ''),
        ]);
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
     * Uses chunked processing to avoid gateway timeout on large batches
     */
    public function bulkDirectToPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agendas' => 'required|string',
        ]);

        // Prevent PHP timeout for large batches (max 5 minutes for safety)
        set_time_limit(300);

        // Disable query log to reduce memory usage
        DB::disableQueryLog();

        $user = Auth::user();

        // Parse nomor_agenda list
        $nomorAgendas = $this->parseNomorAgendaList($validated['nomor_agendas']);

        $processed = 0;
        $failed = 0;
        $errors = [];
        $processedDocs = [];

        // Process in chunks of 50 documents to avoid timeout
        $chunks = array_chunk($nomorAgendas, 50);

        Log::info("Programmer bulk direct-to-payment: Starting processing {$this->countItems($nomorAgendas)} documents in " . count($chunks) . " chunks", [
            'total_documents' => count($nomorAgendas),
            'chunk_size' => 50,
            'chunks' => count($chunks),
            'by' => $user->name ?? 'Programmer',
        ]);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $nomorAgenda) {
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

                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "Error processing {$nomorAgenda}: " . $e->getMessage();
                        Log::error("Programmer bulk error for {$nomorAgenda}: " . $e->getMessage());
                    }
                }

                DB::commit();

                Log::info("Programmer bulk direct-to-payment: Chunk " . ($chunkIndex + 1) . "/" . count($chunks) . " committed ({$processed} processed so far)");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Programmer bulk direct-to-payment chunk " . ($chunkIndex + 1) . " failed: " . $e->getMessage());

                // Continue with next chunk instead of failing entirely
                $failed += count($chunk);
                $errors[] = "Chunk " . ($chunkIndex + 1) . " gagal: " . $e->getMessage();
            }
        }

        Log::info("Programmer bulk direct-to-payment: Completed. {$processed} processed, {$failed} failed", [
            'by' => $user->name ?? 'Programmer',
        ]);

        // Catat ke audit trail
        $this->logActivity('bulk_to_payment', null,
            "Bulk ke Pembayaran: {$processed} dokumen berhasil" . ($failed > 0 ? ", {$failed} gagal" : '') .
            (count($processedDocs) <= 10 ? ' [' . implode(', ', array_slice($processedDocs, 0, 10)) . ']' : '')
        );

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'failed' => $failed,
            'processed_docs' => $processedDocs,
            'errors' => $errors,
            'message' => "Berhasil mengirim {$processed} dokumen ke Pembayaran" . ($failed > 0 ? ", {$failed} gagal" : '')
        ]);
    }

    /**
     * Helper to count array items (avoids count() type issues)
     */
    private function countItems(array $items): int
    {
        return count($items);
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

        // Track sender role through workflow
        $senderRole = $normalizedCurrentRole;

        // Execute each step in the workflow
        foreach ($workflowPath as $index => $targetRole) {
            // Step 1: Send to target role's inbox with proper sender role
            $dokumen->sendToRoleInbox($targetRole, $senderRole);

            Log::info("Bulk workflow: {$dokumen->nomor_agenda} sent to {$targetRole} inbox from {$senderRole}");

            // Step 2: Auto-approve from inbox (sets received_at, processed_at, deadline)
            $dokumen->approveFromRoleInbox($targetRole);

            Log::info("Bulk workflow: {$dokumen->nomor_agenda} approved in {$targetRole}");

            // Update sender role for next step
            $senderRole = $targetRole;
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

        // Safety net: ensure status_pembayaran reflects tanggal_dibayar for docs already paid
        $dokumen->refresh();
        if ($dokumen->tanggal_dibayar && $dokumen->status_pembayaran !== 'sudah_dibayar') {
            $dokumen->status_pembayaran = 'sudah_dibayar';
            $dokumen->save();
            Log::info("Bulk workflow: corrected status_pembayaran to sudah_dibayar for {$dokumen->nomor_agenda} (tanggal_dibayar exists)");
        }

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
        // Full path: go through all roles so dokumen_role_data records are created
        // This ensures documents appear in rekapan keterlambatan for all roles
        $paths = [
            'operator' => ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'],
            'team_verifikasi' => ['perpajakan', 'akutansi', 'pembayaran'],
            'perpajakan' => ['akutansi', 'pembayaran'],
            'akutansi' => ['pembayaran'],
            'pembayaran' => [], // Already at destination
        ];

        return $paths[strtolower($currentRole)] ?? ['pembayaran'];
    }

    /**
     * Get workflow path to reach a specific target role from current role
     */
    private function getWorkflowPathToTarget(string $currentRole, string $targetRole): array
    {
        $workflowOrder = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $currentIndex = array_search(strtolower($currentRole), $workflowOrder);
        $targetIndex  = array_search(strtolower($targetRole), $workflowOrder);

        if ($currentIndex === false || $targetIndex === false || $currentIndex >= $targetIndex) {
            return [];
        }

        return array_slice($workflowOrder, $currentIndex + 1, $targetIndex - $currentIndex);
    }

    /**
     * Simulate workflow step-by-step from current position to a target position
     */
    private function simulateWorkflowToTarget(Dokumen $dokumen, string $targetRole, string $performedBy): void
    {
        $currentRole = $dokumen->current_handler ?? 'operator';
        $normalizedCurrentRole = strtolower($currentRole);
        if ($normalizedCurrentRole === 'verifikasi') {
            $normalizedCurrentRole = 'team_verifikasi';
        }

        $workflowPath = $this->getWorkflowPathToTarget($normalizedCurrentRole, $targetRole);

        if (empty($workflowPath)) {
            return; // Already at or past the target
        }

        Log::info("Bulk workflow simulation starting for {$dokumen->nomor_agenda}", [
            'from_role' => $normalizedCurrentRole,
            'to_target' => $targetRole,
            'path' => $workflowPath,
            'performed_by' => $performedBy
        ]);

        $senderRole = $normalizedCurrentRole;

        foreach ($workflowPath as $index => $stepRole) {
            $dokumen->sendToRoleInbox($stepRole, $senderRole);
            Log::info("Bulk workflow: {$dokumen->nomor_agenda} sent to {$stepRole} inbox from {$senderRole}");
            $dokumen->approveFromRoleInbox($stepRole);
            Log::info("Bulk workflow: {$dokumen->nomor_agenda} approved in {$stepRole}");
            
            $senderRole = $stepRole;
        }

        \App\Models\DokumenActivityLog::create([
            'dokumen_id' => $dokumen->id,
            'stage' => $targetRole,
            'action' => 'bulk_workflow_to_role_complete',
            'action_description' => "Dokumen dilanjutkan ke {$targetRole} via workflow simulasi dari {$normalizedCurrentRole} (Programmer bulk operation)",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'method' => 'simulate_workflow_to_target',
                'from_role' => $normalizedCurrentRole,
                'target_role' => $targetRole,
                'workflow_path' => $workflowPath,
            ],
        ]);

        Log::info("Bulk workflow completed: {$dokumen->nomor_agenda}", [
            'from_role' => $normalizedCurrentRole,
            'to_role' => $targetRole,
            'steps' => count($workflowPath),
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
     * Document Tools page - view document IDs and edit role timestamps
     */
    public function documentTools(Request $request): View
    {
        $documents = Dokumen::select('id', 'nomor_agenda', 'nomor_spp', 'current_handler')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('programmer.document-tools', compact('documents'));
    }

    /**
     * Search documents by nomor_agenda or nomor_spp
     */
    public function searchDocuments(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $role = $request->get('role', '');

        $query = Dokumen::select('id', 'nomor_agenda', 'nomor_spp', 'current_handler');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if (!empty($role)) {
            $query->where('current_handler', $role);
        }

        $documents = $query->orderBy('id', 'desc')->limit(50)->get();

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    /**
     * Get role data for a specific document and role
     */
    public function getRoleData(Request $request): JsonResponse
    {
        $docId = $request->get('doc_id');
        $role = $request->get('role');

        if (!$docId || !$role) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ID dan Role required',
            ], 400);
        }

        // Try to find by ID first, then by nomor_agenda
        $dokumen = Dokumen::find($docId);
        if (!$dokumen) {
            $dokumen = Dokumen::where('nomor_agenda', $docId)->first();
        }

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
            ], 404);
        }

        // Get role data
        $roleData = DokumenRoleData::where('dokumen_id', $dokumen->id)
            ->where('role_code', $role)
            ->first();

        if (!$roleData) {
            // Create empty role data if not exists
            $roleData = DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => $role,
            ]);
        }

        return response()->json([
            'success' => true,
            'dokumen_id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'data' => [
                'received_at' => $roleData->received_at?->format('Y-m-d H:i:s'),
                'processed_at' => $roleData->processed_at?->format('Y-m-d H:i:s'),
                'deadline_at' => $roleData->deadline_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Update role timestamps
     */
    public function updateTimestamps(Request $request): JsonResponse
    {
        $docId = $request->get('doc_id');
        $role = $request->get('role');

        if (!$docId || !$role) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ID dan Role required',
            ], 400);
        }

        // Find dokumen
        $dokumen = Dokumen::find($docId);
        if (!$dokumen) {
            $dokumen = Dokumen::where('nomor_agenda', $docId)->first();
        }

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
            ], 404);
        }

        // Get or create role data
        $roleData = DokumenRoleData::firstOrCreate([
            'dokumen_id' => $dokumen->id,
            'role_code' => $role,
        ]);

        // Update timestamps - use has() to check if field is submitted (even if empty)
        $receivedAt = $request->get('received_at');
        $processedAt = $request->get('processed_at');
        $deadlineAt = $request->get('deadline_at');

        // Log input values for debugging
        Log::info("Programmer updating timestamps - Input values", [
            'dokumen_id' => $dokumen->id,
            'role' => $role,
            'received_at_input' => $receivedAt,
            'processed_at_input' => $processedAt,
            'deadline_at_input' => $deadlineAt,
        ]);

        // Update received_at if provided
        if (!empty($receivedAt)) {
            $parsedReceivedAt = \Carbon\Carbon::parse($receivedAt);
            $roleData->received_at = $parsedReceivedAt;
            Log::info("Parsed received_at: " . $parsedReceivedAt->toDateTimeString());
        }

        // Update processed_at if provided
        if (!empty($processedAt)) {
            $parsedProcessedAt = \Carbon\Carbon::parse($processedAt);
            $roleData->processed_at = $parsedProcessedAt;
            Log::info("Parsed processed_at: " . $parsedProcessedAt->toDateTimeString());
        }

        // Update deadline_at if provided
        if (!empty($deadlineAt)) {
            $parsedDeadlineAt = \Carbon\Carbon::parse($deadlineAt);
            $roleData->deadline_at = $parsedDeadlineAt;
            Log::info("Parsed deadline_at: " . $parsedDeadlineAt->toDateTimeString());
        }

        // Save changes
        $saved = $roleData->save();

        // Log the final result
        Log::info("Programmer updated timestamps - Result", [
            'dokumen_id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'role' => $role,
            'saved' => $saved,
            'final_received_at' => $roleData->received_at,
            'final_processed_at' => $roleData->processed_at,
            'final_deadline_at' => $roleData->deadline_at,
            'updated_by' => Auth::user()->name ?? 'Programmer',
        ]);

        // Catat ke audit trail
        $this->logActivity('update_timestamps',
            ['type' => 'Dokumen', 'id' => $dokumen->id, 'description' => $dokumen->nomor_agenda],
            "Update timestamp role '{$role}' untuk dokumen {$dokumen->nomor_agenda}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Timestamps berhasil diupdate',
            'data' => [
                'received_at' => $roleData->received_at?->format('Y-m-d H:i:s'),
                'processed_at' => $roleData->processed_at?->format('Y-m-d H:i:s'),
                'deadline_at' => $roleData->deadline_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }


    // ============================================
    // USER MANAGEMENT METHODS
    // ============================================

    /**
     * User Management page - view and edit all users
     */
    public function userManagement(Request $request): View
    {
        $roleFilter = $request->get('role', '');

        $query = User::orderBy('name');

        if (!empty($roleFilter)) {
            $query->where('role', $roleFilter);
        }

        $users = $query->paginate(20);

        // Get all available roles for filter dropdown
        $roles = User::select('role')->distinct()->pluck('role')->filter()->values();

        return view('programmer.user-management', compact('users', 'roles', 'roleFilter'));
    }

    /**
     * Get user data for editing
     */
    public function getUserData(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'bagian_code' => $user->bagian_code,
                'phone_number' => $user->phone_number,
            ],
        ]);
    }

    /**
     * Update user data
     */
    public function updateUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string',
            'bagian_code' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8',
        ]);

        $user = User::find($validated['id']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Check for unique username (excluding current user)
        $existingUsername = User::where('username', $validated['username'])
            ->where('id', '!=', $user->id)
            ->first();
        if ($existingUsername) {
            return response()->json([
                'success' => false,
                'message' => 'Username sudah digunakan',
            ], 422);
        }

        // Check for unique email (excluding current user)
        $existingEmail = User::where('email', $validated['email'])
            ->where('id', '!=', $user->id)
            ->first();
        if ($existingEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah digunakan',
            ], 422);
        }

        // Update user data
        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->bagian_code = $validated['bagian_code'] ?? null;
        $user->phone_number = $validated['phone_number'] ?? null;

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        Log::info("Programmer updated user", [
            'user_id' => $user->id,
            'updated_by' => Auth::user()->name ?? 'Programmer',
        ]);

        // Catat ke audit trail
        $this->logActivity('user_update', $user,
            "Update user: {$user->name} (@{$user->username}), role: {$user->role}"
        );

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
        ]);
    }

    /**
     * Store (create) a new user
     */
    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'bagian_code' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:500',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'bagian_code' => $validated['bagian_code'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Log::info("Programmer created user", [
            'user_id' => $user->id,
            'created_by' => Auth::user()->name ?? 'Programmer',
        ]);

        // Catat ke audit trail
        $this->logActivity('user_create', $user,
            "Buat user baru: {$user->name} (@{$user->username}), role: {$user->role}"
        );

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat',
        ]);
    }

    /**
     * Delete a user
     */
    public function destroyUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Prevent deleting self
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri',
            ], 403);
        }

        $userName = $user->name;
        $userUsername = $user->username;
        $userRole = $user->role;
        $userId = $user->id;

        // Catat ke audit trail SEBELUM delete (agar relasi masih ada)
        $this->logActivity('user_delete',
            ['type' => 'User', 'id' => $userId, 'description' => "{$userName} (@{$userUsername})"],
            "Hapus user: {$userName} (@{$userUsername}), role: {$userRole}"
        );

        $user->delete();

        Log::warning("Programmer deleted user", [
            'deleted_user_id' => $userId,
            'deleted_user_name' => $userName,
            'deleted_by' => Auth::user()->name ?? 'Programmer',
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$userName} berhasil dihapus",
        ]);
    }

    /**
     * Reset 2FA for another user (admin action by programmer)
     */
    public function resetUserTwoFactor(int $id, \App\Services\FonnteWhatsAppService $whatsAppService): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $pendingRequest = \App\Models\TwoFactorResetRequest::query()
            ->where('requester_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if (!$pendingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Reset 2FA hanya bisa dilakukan jika ada request dari user (status pending).',
            ], 403);
        }

        $programmer = Auth::user();

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $pendingRequest, $programmer) {
            $user->refresh();
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->two_factor_confirmed_at = null;
            $user->two_factor_recovery_codes = null;
            $user->save();

            $pendingRequest->refresh();
            $pendingRequest->status = 'approved';
            $pendingRequest->programmer_id = $programmer?->id;
            $pendingRequest->handled_at = now();
            $pendingRequest->save();
        });

        try {
            $message = 'Pengajuan reset 2FA Anda telah disetujui. 2FA pada akun Anda sudah dinonaktifkan. Silakan aktifkan kembali 2FA setelah login.';
            $userPhone = trim((string) ($user->phone_number ?? ''));
            if ($userPhone !== '' && config('fonnte.enabled', true)) {
                $whatsAppService->sendMessage($userPhone, $message);
            }

            $fallbackEnabled = filter_var(config('notifications.fallback_email_enabled', true), FILTER_VALIDATE_BOOL);
            $userEmail = trim((string) ($user->email ?? ''));
            if ($fallbackEnabled && $userEmail !== '') {
                \Illuminate\Support\Facades\Mail::to($userEmail)->send(
                    new \App\Mail\TwoFactorResetStatusMail($user, $pendingRequest, 'approved')
                );
            }
        } catch (\Throwable $e) {
        }

        Log::warning("Programmer reset 2FA for user", [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'reset_by' => Auth::user()->name ?? 'Programmer',
        ]);

        // Catat ke audit trail
        $this->logActivity('reset_2fa', $user, "Reset 2FA user via request #{$pendingRequest->id}: {$user->name} (@{$user->username})");

        return response()->json([
            'success' => true,
            'message' => "2FA berhasil direset untuk user {$user->name}",
        ]);
    }

    // ============================================
    // DATABASE CLEANUP METHODS
    // ============================================

    /**
     * Database Tools page - cleanup database
     */
    public function databaseTools(): View
    {
        return view('programmer.database-tools');
    }

    /**
     * Preview cleanup - count records in each table
     */
    public function previewCleanup(): JsonResponse
    {
        $counts = [
            'dokumens' => Dokumen::count(),
            'dokumen_pos' => DokumenPO::count(),
            'dokumen_prs' => DokumenPR::count(),
            'dokumen_role_data' => DokumenRoleData::count(),
            'dokumen_statuses' => DokumenStatus::count(),
            'dokumen_activity_logs' => DokumenActivityLog::count(),
            'dibayar_kepadas' => DibayarKepada::count(),
        ];

        $total = array_sum($counts);

        return response()->json([
            'success' => true,
            'counts' => $counts,
            'total' => $total,
        ]);
    }

    /**
     * Perform database cleanup with transaction
     */
    public function performCleanup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confirmation' => 'required|string',
        ]);

        // Security check - require exact confirmation text
        if ($validated['confirmation'] !== 'HAPUS SEMUA') {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi tidak valid. Ketik "HAPUS SEMUA" untuk melanjutkan.',
            ], 422);
        }

        try {
            // Disable foreign key checks
            Schema::disableForeignKeyConstraints();

            // Truncate all document-related tables in correct order
            // Note: TRUNCATE is DDL in MySQL (auto-commit), so we don't wrap in transaction
            // DocumentTracking must be truncated to prevent orphaned tracking records
            DocumentTracking::truncate();
            DokumenActivityLog::truncate();
            DokumenRoleData::truncate();
            DokumenStatus::truncate();
            DibayarKepada::truncate();
            DokumenPR::truncate();
            DokumenPO::truncate();
            Dokumen::truncate();

            // Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            Log::warning("Database cleanup performed", [
                'performed_by' => Auth::user()->name ?? 'Programmer',
                'performed_at' => now()->toDateTimeString(),
            ]);

            // Catat ke audit trail (HIGH RISK)
            $this->logActivity('database_cleanup', null,
                'Melakukan TRUNCATE semua tabel dokumen (dokumens, dokumen_pos, dokumen_prs, dll)'
            );

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dibersihkan. Semua dokumen telah dihapus.',
            ]);

        } catch (\Exception $e) {
            // Make sure FK constraints are re-enabled even on error
            Schema::enableForeignKeyConstraints();

            Log::error("Database cleanup failed", [
                'error' => $e->getMessage(),
                'performed_by' => Auth::user()->name ?? 'Programmer',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan database: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // BULK SET DATE PAYMENT METHODS
    // ============================================

    /**
     * Show bulk set date payment form
     */
    public function showBulkSetDatePaymentForm(): View
    {
        return view('programmer.bulk-set-date-payment');
    }

    /**
     * Preview bulk set date payment - validate paired inputs
     */
    public function previewBulkSetDatePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_agenda_list' => 'required|string',
            'tanggal_list' => 'required|string',
        ]);

        // Parse both inputs line by line
        $agendaLines = $this->parseLinesStrict($validated['nomor_agenda_list']);
        $dateLines = $this->parseLinesStrict($validated['tanggal_list']);

        // Validate count match
        if (count($agendaLines) !== count($dateLines)) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah nomor agenda (' . count($agendaLines) . ') tidak sama dengan jumlah tanggal (' . count($dateLines) . '). Pastikan jumlah baris sama.',
            ], 422);
        }

        if (count($agendaLines) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak boleh kosong.',
            ], 422);
        }

        // Build pairs and validate each
        $pairs = [];
        $totalValid = 0;
        $totalInvalid = 0;

        foreach ($agendaLines as $i => $agenda) {
            $tanggal = $dateLines[$i];
            $pair = [
                'index' => $i + 1,
                'nomor_agenda' => $agenda,
                'tanggal_input' => $tanggal,
                'tanggal_valid' => false,
                'tanggal_parsed' => null,
                'found' => false,
                'valid' => false,
                'reason' => '',
                'nomor_spp' => null,
                'uraian_spp' => null,
                'nilai_rupiah' => null,
                'current_handler' => null,
                'status_pembayaran' => null,
                'tanggal_dibayar_existing' => null,
            ];

            // Validate date format dd/mm/yyyy
            try {
                $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tanggal);
                if ($parsedDate && $parsedDate->format('d/m/Y') === $tanggal) {
                    $pair['tanggal_valid'] = true;
                    $pair['tanggal_parsed'] = $parsedDate->format('Y-m-d');
                } else {
                    $pair['reason'] = 'Format tanggal tidak valid (gunakan dd/mm/yyyy)';
                    $totalInvalid++;
                    $pairs[] = $pair;
                    continue;
                }
            } catch (\Exception $e) {
                $pair['reason'] = 'Format tanggal tidak valid (gunakan dd/mm/yyyy)';
                $totalInvalid++;
                $pairs[] = $pair;
                continue;
            }

            // Find document
            $dokumen = Dokumen::where('nomor_agenda', $agenda)->first();
            if (!$dokumen) {
                $pair['reason'] = 'Dokumen tidak ditemukan';
                $totalInvalid++;
                $pairs[] = $pair;
                continue;
            }

            $pair['found'] = true;
            $pair['nomor_spp'] = $dokumen->nomor_spp;
            $pair['uraian_spp'] = $dokumen->uraian_spp;
            $pair['nilai_rupiah'] = number_format((float) ($dokumen->nilai_rupiah ?? 0), 0, ',', '.');
            $pair['current_handler'] = $dokumen->current_handler;
            $pair['status_pembayaran'] = $dokumen->status_pembayaran;
            $pair['tanggal_dibayar_existing'] = $dokumen->tanggal_dibayar?->format('d/m/Y');

            // Check if document is at pembayaran
            if ($dokumen->current_handler !== 'pembayaran') {
                $pair['reason'] = 'Dokumen belum di Pembayaran (posisi: ' . ucfirst($dokumen->current_handler ?? 'operator') . ')';
                $totalInvalid++;
                $pairs[] = $pair;
                continue;
            }

            // Check if already paid
            if (!empty($dokumen->tanggal_dibayar)) {
                $pair['reason'] = 'Sudah dibayar pada ' . $dokumen->tanggal_dibayar->format('d/m/Y');
                $totalInvalid++;
                $pairs[] = $pair;
                continue;
            }

            // Valid!
            $pair['valid'] = true;
            $totalValid++;
            $pairs[] = $pair;
        }

        return response()->json([
            'success' => true,
            'pairs' => $pairs,
            'total' => count($pairs),
            'total_valid' => $totalValid,
            'total_invalid' => $totalInvalid,
        ]);
    }

    /**
     * Execute bulk set date payment
     */
    public function executeBulkSetDatePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pairs' => 'required|array|min:1',
            'pairs.*.nomor_agenda' => 'required|string',
            'pairs.*.tanggal' => 'required|string',
        ]);

        $user = Auth::user();
        $processed = 0;
        $failed = 0;
        $errors = [];
        $processedDocs = [];

        DB::beginTransaction();
        try {
            foreach ($validated['pairs'] as $pair) {
                try {
                    $dokumen = Dokumen::where('nomor_agenda', $pair['nomor_agenda'])->first();

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Dokumen {$pair['nomor_agenda']} tidak ditemukan";
                        continue;
                    }

                    if ($dokumen->current_handler !== 'pembayaran') {
                        $failed++;
                        $errors[] = "Dokumen {$pair['nomor_agenda']} belum di Pembayaran";
                        continue;
                    }

                    if (!empty($dokumen->tanggal_dibayar)) {
                        $failed++;
                        $errors[] = "Dokumen {$pair['nomor_agenda']} sudah dibayar";
                        continue;
                    }

                    // Parse date
                    $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $pair['tanggal']);

                    // Update document
                    $dokumen->update([
                        'tanggal_dibayar' => $parsedDate,
                        'status_pembayaran' => 'sudah_dibayar',
                        'status' => 'completed',
                    ]);

                    $processed++;
                    $processedDocs[] = $pair['nomor_agenda'];

                    Log::info("Programmer bulk set date payment: Document {$pair['nomor_agenda']} set tanggal_dibayar={$parsedDate->format('d/m/Y')} by {$user->name}");

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing {$pair['nomor_agenda']}: " . $e->getMessage();
                    Log::error("Programmer bulk set date error for {$pair['nomor_agenda']}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Catat ke audit trail
            $this->logActivity('bulk_set_date', null,
                "Bulk set tanggal pembayaran: {$processed} dokumen berhasil" . ($failed > 0 ? ", {$failed} gagal" : '') .
                (count($processedDocs) <= 10 ? ' [' . implode(', ', array_slice($processedDocs, 0, 10)) . ']' : '')
            );

            return response()->json([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'processed_docs' => $processedDocs,
                'errors' => $errors,
                'message' => "Berhasil set tanggal pembayaran untuk {$processed} dokumen" . ($failed > 0 ? ", {$failed} gagal" : ''),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programmer bulk set date payment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse lines strictly - split by newline only, preserve order
     */
    private function parseLinesStrict(string $input): array
    {
        $lines = explode("\n", $input);
        $result = [];
        foreach ($lines as $line) {
            $trimmed = trim(str_replace("\r", '', $line));
            if (!empty($trimmed)) {
                $result[] = $trimmed;
            }
        }
        return $result;
    }

    // ============================================
    // ACTIVITY LOGS METHODS
    // ============================================

    /**
     * Activity Logs page - view all document activity history
     */
    public function activityLogs(Request $request): View
    {
        $query = DokumenActivityLog::with('dokumen:id,nomor_agenda,nomor_spp,bagian,uraian_spp')
            ->orderBy('action_at', 'desc');

        // Filter by nomor_agenda or nomor_spp
        if ($search = $request->get('search')) {
            $query->whereHas('dokumen', function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                  ->orWhere('nomor_spp', 'like', "%{$search}%");
            });
        }

        // Filter by bagian
        if ($bagian = $request->get('bagian')) {
            $query->whereHas('dokumen', function ($q) use ($bagian) {
                $q->where('bagian', $bagian);
            });
        }

        // Filter by performed_by (role)
        if ($performedBy = $request->get('performed_by')) {
            $query->where('performed_by', $performedBy);
        }

        // Filter by date range
        if ($dateFrom = $request->get('date_from')) {
            $query->where('action_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo = $request->get('date_to')) {
            $query->where('action_at', '<=', $dateTo . ' 23:59:59');
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get unique bagian values for filter dropdown
        $bagianList = Dokumen::select('bagian')->distinct()->whereNotNull('bagian')->orderBy('bagian')->pluck('bagian');

        // Get unique performed_by values for filter dropdown
        $performerList = DokumenActivityLog::select('performed_by')->distinct()->whereNotNull('performed_by')->orderBy('performed_by')->pluck('performed_by');

        return view('programmer.activity-logs', compact('logs', 'bagianList', 'performerList'));
    }

    // ============================================
    // DATABASE EXPORT/BACKUP METHODS
    // ============================================

    /**
     * Export/download database as .sql file using mysqldump
     */
    public function exportDatabase(string $database): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    {
        // Map parameter to DB connection name
        $connectionMap = [
            'agenda' => 'mysql',
            'cashbank' => 'cash_bank_new',
        ];

        if (!isset($connectionMap[$database])) {
            return response()->json([
                'success' => false,
                'message' => 'Database tidak valid. Pilih: agenda atau cashbank',
            ], 422);
        }

        $connectionName = $connectionMap[$database];
        $config = config("database.connections.{$connectionName}");

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => "Konfigurasi database '{$connectionName}' tidak ditemukan.",
            ], 500);
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $dbName = $config['database'];

        // Build mysqldump command
        $command = [
            'mysqldump',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--add-drop-table',
            $dbName,
        ];

        // Add password if set
        if (!empty($password)) {
            $command[] = '--password=' . $password;
        }

        Log::info("Database export started", [
            'database' => $database,
            'connection' => $connectionName,
            'db_name' => $dbName,
            'by' => Auth::user()->name ?? 'Programmer',
        ]);

        // Generate filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i');
        $filename = "{$dbName}_{$timestamp}.sql";

        try {
            // Test that mysqldump is available first
            $testProcess = new \Symfony\Component\Process\Process(['mysqldump', '--version']);
            $testProcess->setTimeout(10);
            $testProcess->run();

            if (!$testProcess->isSuccessful()) {
                Log::error("mysqldump not available", ['output' => $testProcess->getErrorOutput()]);
                return response()->json([
                    'success' => false,
                    'message' => 'mysqldump tidak tersedia di server. Pastikan mysql-client sudah terinstall.',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("mysqldump check failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'mysqldump tidak tersedia di server: ' . $e->getMessage(),
            ], 500);
        }

        // Stream the dump as a download
        return response()->streamDownload(function () use ($command, $dbName) {
            $process = new \Symfony\Component\Process\Process($command);
            $process->setTimeout(600); // 10 minutes timeout for large databases
            $process->run(function ($type, $buffer) {
                if ($type === \Symfony\Component\Process\Process::OUT) {
                    echo $buffer;
                }
            });

            if (!$process->isSuccessful()) {
                Log::error("mysqldump failed for {$dbName}", [
                    'error' => $process->getErrorOutput(),
                ]);
            }
        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

