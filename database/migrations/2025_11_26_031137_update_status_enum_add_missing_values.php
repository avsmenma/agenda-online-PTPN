<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update status enum to include all possible status values
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'menunggu_di_approve',
            'menunggu_approved_pengiriman',
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'sent_to_ibub',
            'processed_by_ibub',
            'sent_to_perpajakan',
            'processed_by_perpajakan',
            'sent_to_akutansi',
            'processed_by_akutansi',
            'sent_to_pembayaran',
            'processed_by_pembayaran',
            'completed',
            'returned_to_ibua',
            'returned_to_ibub',
            'returned_to_department',
            'returned_to_bidang',
            'returned_from_akutansi',
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi',
            'sedang diproses',
            'rejected_dikembalikan',
            'selesai'
        ) NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum (keep all values for safety)
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'menunggu_di_approve',
            'menunggu_approved_pengiriman',
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'sent_to_ibub',
            'processed_by_ibub',
            'sent_to_perpajakan',
            'processed_by_perpajakan',
            'sent_to_akutansi',
            'processed_by_akutansi',
            'sent_to_pembayaran',
            'processed_by_pembayaran',
            'completed',
            'returned_to_ibua',
            'returned_to_ibub',
            'returned_to_department',
            'returned_to_bidang',
            'returned_from_akutansi',
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi',
            'sedang diproses',
            'rejected_dikembalikan',
            'selesai'
        ) NOT NULL DEFAULT 'draft'");
    }
};
