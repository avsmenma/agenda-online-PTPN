<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds display_status column to dokumen_role_data table.
     * This column stores the FINAL display status for each role that should NOT change
     * when other downstream roles perform actions.
     * 
     * Example values:
     * - menunggu_approval_verifikasi (Ibu Tarapul waiting for Verifikasi to approve)
     * - terkirim (Final status for Ibu Tarapul after Verifikasi approves)
     * - sedang_diproses (Role is currently processing the document)
     * - menunggu_approval_perpajakan (Verifikasi waiting for Perpajakan to approve)
     * - terkirim_perpajakan (Final status for Verifikasi after Perpajakan approves)
     * - menunggu_approval_akutansi (Perpajakan waiting for Akutansi to approve)
     * - terkirim_akutansi (Final status for Perpajakan after Akutansi approves)
     * - menunggu_approval_pembayaran (Akutansi waiting for Pembayaran to approve)
     * - terkirim_pembayaran (Final status for Akutansi after Pembayaran approves)
     */
    public function up(): void
    {
        Schema::table('dokumen_role_data', function (Blueprint $table) {
            $table->string('display_status', 100)->nullable()
                ->after('deadline_note')
                ->comment('Status tampilan FINAL per role, tidak berubah saat role lain melakukan aksi');

            // Add index for querying by display_status
            $table->index('display_status', 'idx_display_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_role_data', function (Blueprint $table) {
            $table->dropIndex('idx_display_status');
            $table->dropColumn('display_status');
        });
    }
};
