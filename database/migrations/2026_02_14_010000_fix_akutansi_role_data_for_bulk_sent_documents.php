<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fix: Ensure akutansi dokumen_role_data records have received_at set
 * for documents that went through bulk direct-to-payment workflow.
 * 
 * This handles two cases:
 * 1. Records that exist but have NULL received_at
 * 2. Records that are completely missing
 */
return new class extends Migration {
    public function up(): void
    {
        // Find all documents that have bulk_workflow_complete activity log entries
        $bulkDocumentIds = DB::table('dokumen_activity_logs')
            ->where('action', 'bulk_workflow_complete')
            ->pluck('dokumen_id')
            ->unique()
            ->toArray();

        if (empty($bulkDocumentIds)) {
            Log::info('Backfill akutansi: No bulk-sent documents found, skipping.');
            return;
        }

        Log::info('Backfill akutansi: Found ' . count($bulkDocumentIds) . ' bulk-sent documents to check.');

        $rolesToFix = ['perpajakan', 'akutansi'];
        $created = 0;
        $updated = 0;

        foreach ($bulkDocumentIds as $docId) {
            // Get any existing role data as reference for timestamps
            $referenceData = DB::table('dokumen_role_data')
                ->where('dokumen_id', $docId)
                ->whereNotNull('received_at')
                ->orderBy('received_at', 'desc')
                ->first();

            if (!$referenceData) {
                // Try to get from dokumen created_at as last resort
                $dokumen = DB::table('dokumens')->where('id', $docId)->first();
                if (!$dokumen) {
                    continue;
                }
                $referenceTimestamp = $dokumen->created_at;
            } else {
                $referenceTimestamp = $referenceData->processed_at ?? $referenceData->received_at;
            }

            foreach ($rolesToFix as $roleCode) {
                // Check if role data exists
                $existingRecord = DB::table('dokumen_role_data')
                    ->where('dokumen_id', $docId)
                    ->where('role_code', $roleCode)
                    ->first();

                if (!$existingRecord) {
                    // Create missing record
                    DB::table('dokumen_role_data')->insert([
                        'dokumen_id' => $docId,
                        'role_code' => $roleCode,
                        'received_at' => $referenceTimestamp,
                        'processed_at' => $referenceTimestamp,
                        'deadline_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created++;
                    Log::info("Backfill: Created {$roleCode} role_data for dokumen_id={$docId}");
                } elseif (!$existingRecord->received_at) {
                    // Record exists but received_at is NULL - update it
                    DB::table('dokumen_role_data')
                        ->where('dokumen_id', $docId)
                        ->where('role_code', $roleCode)
                        ->update([
                            'received_at' => $referenceTimestamp,
                            'processed_at' => $existingRecord->processed_at ?? $referenceTimestamp,
                            'updated_at' => now(),
                        ]);
                    $updated++;
                    Log::info("Backfill: Updated {$roleCode} received_at for dokumen_id={$docId}");
                }
            }
        }

        Log::info("Backfill akutansi: Created {$created}, updated {$updated} role_data records.");
    }

    public function down(): void
    {
        // No destructive rollback - the data should stay
    }
};
