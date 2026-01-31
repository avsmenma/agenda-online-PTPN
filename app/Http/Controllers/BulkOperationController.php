<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BulkOperationController extends Controller
{
    /**
     * Bulk approve multiple documents
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1|max:50',
            'document_ids.*' => 'required|integer|exists:dokumens,id',
        ]);

        $user = Auth::user();
        $role = $user->role;

        // Verify user is team_verifikasi
        if ($role !== 'team_verifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only Team Verifikasi can perform bulk operations'
            ], 403);
        }

        $documentIds = $validated['document_ids'];
        $processed = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($documentIds as $docId) {
                try {
                    $dokumen = Dokumen::with('roleData')->find($docId);

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Document ID {$docId} not found";
                        continue;
                    }

                    // Verify document is assigned to this user
                    if ($dokumen->current_handler !== $role) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} not assigned to you";
                        continue;
                    }

                    // Mark current role data as processed
                    if ($dokumen->latestRoleData) {
                        $dokumen->latestRoleData->update([
                            'processed_at' => now(),
                        ]);
                    }

                    // Update document status to approved
                    $dokumen->update([
                        'status' => 'approved_by_team_verifikasi',
                        'current_handler' => null, // Document completed at this stage
                    ]);

                    $processed++;

                    Log::info("Bulk approve: Document {$dokumen->nomor_agenda} approved by {$user->name}");

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing document {$docId}: " . $e->getMessage();
                    Log::error("Bulk approve error for document {$docId}: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'errors' => $errors,
                'message' => "Successfully approved {$processed} document(s)" . ($failed > 0 ? ", {$failed} failed" : '')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk approve transaction failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject multiple documents
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1|max:50',
            'document_ids.*' => 'required|integer|exists:dokumens,id',
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $role = $user->role;

        // Verify user is team_verifikasi
        if ($role !== 'team_verifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only Team Verifikasi can perform bulk operations'
            ], 403);
        }

        $documentIds = $validated['document_ids'];
        $reason = $validated['reason'];
        $processed = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($documentIds as $docId) {
                try {
                    $dokumen = Dokumen::with('roleData')->find($docId);

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Document ID {$docId} not found";
                        continue;
                    }

                    // Verify document is assigned to this user
                    if ($dokumen->current_handler !== $role) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} not assigned to you";
                        continue;
                    }

                    // Mark current role data as processed
                    if ($dokumen->latestRoleData) {
                        $dokumen->latestRoleData->update([
                            'processed_at' => now(),
                        ]);
                    }

                    // Update document status to rejected
                    $dokumen->update([
                        'status' => 'rejected_by_team_verifikasi',
                        'current_handler' => 'operator', // Return to operator
                        'rejection_reason' => $reason,
                        'rejected_at' => now(),
                        'rejected_by' => $user->name,
                    ]);

                    // Create role data for operator (returned)
                    DokumenRoleData::create([
                        'dokumen_id' => $dokumen->id,
                        'role_code' => 'operator',
                        'received_at' => now(),
                        'processed_at' => null,
                        'deadline_at' => null,
                    ]);

                    $processed++;

                    Log::info("Bulk reject: Document {$dokumen->nomor_agenda} rejected by {$user->name}. Reason: {$reason}");

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing document {$docId}: " . $e->getMessage();
                    Log::error("Bulk reject error for document {$docId}: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'errors' => $errors,
                'message' => "Successfully rejected {$processed} document(s)" . ($failed > 0 ? ", {$failed} failed" : '')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk reject transaction failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk forward multiple documents to next role
     * Supports: team_verifikasi -> perpajakan/akuntansi/pembayaran
     *           perpajakan -> akutansi/pembayaran
     *           akutansi -> pembayaran
     */
    public function bulkForward(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1|max:50',
            'document_ids.*' => 'required|integer|exists:dokumens,id',
            'target_role' => 'required|in:perpajakan,akuntansi,akutansi,pembayaran',
        ]);

        $user = Auth::user();
        $role = $user->role;

        // Define allowed sender roles and their target options
        $allowedRoles = [
            'team_verifikasi' => ['perpajakan', 'akuntansi', 'akutansi', 'pembayaran'],
            'perpajakan' => ['akuntansi', 'akutansi', 'pembayaran'],
            'akutansi' => ['pembayaran'],
        ];

        // Verify user role is allowed to perform bulk operations
        if (!isset($allowedRoles[$role])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Your role cannot perform bulk operations'
            ], 403);
        }

        // Normalize target role (akuntansi and akutansi are the same)
        $targetRole = $validated['target_role'];
        if ($targetRole === 'akutansi') {
            $targetRole = 'akuntansi';
        }
        // For database operations, use 'akutansi' as that's what's in the system
        $targetRoleDb = $targetRole === 'akuntansi' ? 'akutansi' : $targetRole;

        // Verify target is allowed for this sender role
        if (!in_array($validated['target_role'], $allowedRoles[$role])) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized: Your role cannot send to {$targetRole}"
            ], 403);
        }

        $documentIds = $validated['document_ids'];
        $processed = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($documentIds as $docId) {
                try {
                    $dokumen = Dokumen::with('roleData')->find($docId);

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Document ID {$docId} not found";
                        continue;
                    }

                    // Check if document is accessible by current role
                    $senderRoleData = $dokumen->getDataForRole($role);

                    if (!$senderRoleData) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} not accessible to your role";
                        continue;
                    }

                    // Check if already sent to target role (prevent duplicate sends)
                    $targetRoleData = $dokumen->getDataForRole($targetRoleDb);
                    if ($targetRoleData && $targetRoleData->received_at) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} already sent to {$targetRole}";
                        continue;
                    }

                    // Mark current role data as processed
                    if ($senderRoleData) {
                        $senderRoleData->processed_at = now();
                        $senderRoleData->save();
                    }

                    // Map handler to inbox role format (consistent with single send)
                    $inboxRoleMap = [
                        'perpajakan' => 'Perpajakan',
                        'akuntansi' => 'Akutansi',
                        'akutansi' => 'Akutansi',
                        'pembayaran' => 'Pembayaran',
                    ];
                    $inboxRole = $inboxRoleMap[$targetRole] ?? $targetRole;

                    // Use sendToInbox method - this properly routes document through inbox
                    // and sets status to pending_approval_* WITHOUT changing current_handler
                    $dokumen->sendToInbox($inboxRole);

                    // For perpajakan, reset deadline so they must set it after approval
                    if ($targetRole === 'perpajakan') {
                        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
                        if ($perpajakanRoleData) {
                            $perpajakanRoleData->deadline_at = null;
                            $perpajakanRoleData->deadline_days = null;
                            $perpajakanRoleData->deadline_note = null;
                            $perpajakanRoleData->processed_at = null;
                            $perpajakanRoleData->save();
                        }
                    }

                    $processed++;

                    Log::info("Bulk forward: Document {$dokumen->nomor_agenda} forwarded to {$targetRole} by {$user->name}");

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing document {$docId}: " . $e->getMessage();
                    Log::error("Bulk forward error for document {$docId}: " . $e->getMessage());
                }
            }

            DB::commit();

            $responseData = [
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'message' => "Successfully forwarded {$processed} document(s) to {$targetRole}" . ($failed > 0 ? ", {$failed} failed" : '')
            ];

            // Always include errors for debugging
            if (!empty($errors)) {
                $responseData['errors'] = $errors;
                $responseData['debug_info'] = [
                    'total_requested' => count($documentIds),
                    'processed_count' => $processed,
                    'failed_count' => $failed,
                    'target_role' => $targetRole,
                ];
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk forward transaction failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
