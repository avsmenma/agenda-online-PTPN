<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Phase 1 of Opsi B: Add unified return fields to consolidate
     * the 8 redundant return-related fields into 3 unified fields.
     */
    public function up(): void
    {
        // Step 1: Add new unified columns
        Schema::table('dokumens', function (Blueprint $table) {
            $table->string('return_source')->nullable()->after('was_returned_by_verifikasi')
                ->comment('Source/target of return: perpajakan, akutansi, team_verifikasi, bagian_xxx, operator');
            $table->text('return_reason')->nullable()->after('return_source')
                ->comment('Unified return reason (replaces alasan_pengembalian, department_return_reason, bidang_return_reason)');
            $table->timestamp('returned_at')->nullable()->after('return_reason')
                ->comment('Unified return timestamp (replaces department_returned_at, bidang_returned_at, returned_to_Operator_at)');
        });

        // Step 2: Backfill data from old columns into new columns
        // Priority: most recent return data wins

        // 2a. Documents returned from departments to verifikasi (department_returned_at)
        DB::table('dokumens')
            ->whereNotNull('department_returned_at')
            ->whereNull('return_source')
            ->update([
                'return_source' => DB::raw('target_department'),
                'return_reason' => DB::raw("COALESCE(department_return_reason, alasan_pengembalian)"),
                'returned_at' => DB::raw('department_returned_at'),
            ]);

        // 2b. Documents returned to bidang (bidang_returned_at)
        DB::table('dokumens')
            ->whereNotNull('bidang_returned_at')
            ->whereNull('return_source')
            ->update([
                'return_source' => DB::raw("COALESCE(target_bidang, 'team_verifikasi')"),
                'return_reason' => DB::raw("COALESCE(bidang_return_reason, 'Dikembalikan ke bidang asal')"),
                'returned_at' => DB::raw('bidang_returned_at'),
            ]);

        // 2c. Documents returned to operator (returned_to_Operator_at)
        if (Schema::hasColumn('dokumens', 'returned_to_Operator_at')) {
            DB::table('dokumens')
                ->whereNotNull('returned_to_Operator_at')
                ->whereNull('return_source')
                ->update([
                    'return_source' => 'team_verifikasi',
                    'return_reason' => DB::raw("COALESCE(alasan_pengembalian, 'Dikembalikan ke operator')"),
                    'returned_at' => DB::raw('returned_to_Operator_at'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn(['return_source', 'return_reason', 'returned_at']);
        });
    }
};
