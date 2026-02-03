<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DibayarKepada;
use App\Models\Dokumen;
use App\Models\DokumenActivityLog;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Models\User;
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

    /**
     * Check if user is programmer
     */
    public function isProgrammer(): bool
    {
        return Auth::user()?->role === 'programmer';
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
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
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

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
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
            DB::transaction(function () {
                // Disable foreign key checks
                Schema::disableForeignKeyConstraints();

                // Truncate all document-related tables in correct order
                DokumenActivityLog::truncate();
                DokumenRoleData::truncate();
                DokumenStatus::truncate();
                DibayarKepada::truncate();
                DokumenPR::truncate();
                DokumenPO::truncate();
                Dokumen::truncate();

                // Re-enable foreign key checks
                Schema::enableForeignKeyConstraints();
            });

            Log::warning("Database cleanup performed", [
                'performed_by' => Auth::user()->name ?? 'Programmer',
                'performed_at' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dibersihkan. Semua dokumen telah dihapus.',
            ]);

        } catch (\Exception $e) {
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
}

