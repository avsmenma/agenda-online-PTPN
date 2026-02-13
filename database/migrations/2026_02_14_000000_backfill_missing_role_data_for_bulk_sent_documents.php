<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Backfill missing dokumen_role_data records for documents that went through
 * the bulk direct-to-payment workflow. Previously, the workflow skipped
 * perpajakan and akutansi, so those roles have no role_data records.
 * This migration creates the missing records so documents appear in
 * those teams' rekapan keterlambatan.
 */
return new class extends Migration {
    public function up(): void
    {
        // Find all documents that have bulk_workflow_complete activity log entries
        // These documents went through the programmer's bulk direct-to-payment
        $bulkDocumentIds = DB::table('dokumen_activity_logs')
            ->where('action', 'bulk_workflow_complete')
            ->pluck('dokumen_id')
            ->unique()
            ->toArray();

        if (empty($bulkDocumentIds)) {
            Log::info('Backfill: No bulk-sent documents found, skipping.');
            return;
        }

        Log::info('Backfill: Found ' . count($bulkDocumentIds) . ' bulk-sent documents to check.');

        $rolesToCheck = ['perpajakan', 'akutansi'];
        $created = 0;

        foreach ($bulkDocumentIds as $docId) {
            // Get the team_verifikasi role data as reference for timestamps
            $verifikasiData = DB::table('dokumen_role_data')
                ->where('dokumen_id', $docId)
                ->where('role_code', 'team_verifikasi')
                ->first();

            // Get pembayaran role data as reference
            $pembayaranData = DB::table('dokumen_role_data')
                ->where('dokumen_id', $docId)
                ->where('role_code', 'pembayaran')
                ->first();

            if (!$verifikasiData || !$pembayaranData) {
                continue; // Skip if base records don't exist
            }

            foreach ($rolesToCheck as $roleCode) {
                // Check if role data already exists
                $exists = DB::table('dokumen_role_data')
                    ->where('dokumen_id', $docId)
                    ->where('role_code', $roleCode)
                    ->exists();

                if (!$exists) {
                    // Create the missing role data record
                    // Use team_verifikasi's processed_at as the received_at for perpajakan
                    // and perpajakan's processed_at (which we'll set) as received_at for akutansi
                    // Since these are bulk-sent, all timestamps are very close together
                    $receivedAt = $verifikasiData->processed_at ?? $verifikasiData->received_at ?? $pembayaranData->received_at;
                    $processedAt = $receivedAt; // Instantly processed since it was bulk-sent

                    DB::table('dokumen_role_data')->insert([
                        'dokumen_id' => $docId,
                        'role_code' => $roleCode,
                        'received_at' => $receivedAt,
                        'processed_at' => $processedAt,
                        'deadline_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $created++;
                    Log::info("Backfill: Created {$roleCode} role_data for dokumen_id={$docId}");
                }
            }
        }

        Log::info("Backfill: Created {$created} missing role_data records for bulk-sent documents.");
    }

    public function down(): void
    {
        // Find all documents that have bulk_workflow_complete activity log entries
        $bulkDocumentIds = DB::table('dokumen_activity_logs')
            ->where('action', 'bulk_workflow_complete')
            ->pluck('dokumen_id')
            ->unique()
            ->toArray();

        if (empty($bulkDocumentIds)) {
            return;
        }

        // Remove the backfilled records
        DB::table('dokumen_role_data')
            ->whereIn('dokumen_id', $bulkDocumentIds)
            ->whereIn('role_code', ['perpajakan', 'akutansi'])
            ->delete();
    }
};
