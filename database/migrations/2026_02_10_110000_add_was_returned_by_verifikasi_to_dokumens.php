<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds tracking fields for conditional document routing:
     * - was_returned_by_verifikasi: Flag to know if document was returned from Team Verifikasi
     * - resent_to_verifikasi_at: Timestamp when Bagian resent the corrected document
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->boolean('was_returned_by_verifikasi')->default(false)->after('bidang_return_reason');
            $table->timestamp('resent_to_verifikasi_at')->nullable()->after('was_returned_by_verifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn(['was_returned_by_verifikasi', 'resent_to_verifikasi_at']);
        });
    }
};
