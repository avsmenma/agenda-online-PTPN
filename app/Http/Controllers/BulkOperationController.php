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
                    $dokumen = Dokumen::with('latestRoleData')->find($docId);

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
                    $dokumen = Dokumen::with('latestRoleData')->find($docId);

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
     */
    public function bulkForward(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1|max:50',
            'document_ids.*' => 'required|integer|exists:dokumens,id',
            'target_role' => 'required|in:perpajakan,akuntansi',
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
        $targetRole = $validated['target_role'];
        $processed = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($documentIds as $docId) {
                try {
                    $dokumen = Dokumen::with('latestRoleData')->find($docId);

                    if (!$dokumen) {
                        $failed++;
                        $errors[] = "Document ID {$docId} not found";
                        continue;
                    }

                    // Check if document is accessible by team_verifikasi
                    // A document is accessible if it has role_data for team_verifikasi
                    $verifikasiRoleData = $dokumen->getDataForRole('team_verifikasi');

                    if (!$verifikasiRoleData) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} not accessible to Team Verifikasi";
                        continue;
                    }

                    // Check if already sent to target role (prevent duplicate sends)
                    $targetRoleData = $dokumen->getDataForRole($targetRole);
                    if ($targetRoleData && $targetRoleData->received_at) {
                        $failed++;
                        $errors[] = "Document {$dokumen->nomor_agenda} already sent to {$targetRole}";
                        continue;
                    }

                    // Mark current role data as processed
                    if ($dokumen->latestRoleData) {
                        $dokumen->latestRoleData->update([
                            'processed_at' => now(),
                        ]);
                    }

                    // Determine status based on target role
                    $newStatus = $targetRole === 'perpajakan' ? 'sent_to_perpajakan' : 'sent_to_akutansi';

                    // Update document
                    $dokumen->update([
                        'status' => $newStatus,
                        'current_handler' => $targetRole,
                    ]);

                    // Create role data for target role
                    DokumenRoleData::create([
                        'dokumen_id' => $dokumen->id,
                        'role_code' => $targetRole,
                        'received_at' => now(),
                        'processed_at' => null,
                        'deadline_at' => null,
                    ]);

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
