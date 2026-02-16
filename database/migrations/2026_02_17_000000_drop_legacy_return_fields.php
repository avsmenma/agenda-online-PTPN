<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Phase 3: Drop all legacy return-related columns now that unified fields are in place
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Phase 3A: Drop reason/timestamp duplicates
            $table->dropColumn([
                'alasan_pengembalian',
                'department_return_reason',
                'department_returned_at',
                'bidang_return_reason',
                'bidang_returned_at',
            ]);

            // Phase 3B: Drop routing columns (now using return_source)
            $table->dropColumn([
                'target_department',
                'target_bidang',
                'was_returned_by_verifikasi',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Recreate legacy columns for rollback
            // Phase 3A columns
            $table->text('alasan_pengembalian')->nullable();
            $table->text('department_return_reason')->nullable();
            $table->timestamp('department_returned_at')->nullable();
            $table->text('bidang_return_reason')->nullable();
            $table->timestamp('bidang_returned_at')->nullable();

            // Phase 3B columns
            $table->string('target_department')->nullable();
            $table->string('target_bidang')->nullable();
            $table->boolean('was_returned_by_verifikasi')->default(false);
        });
    }
};
