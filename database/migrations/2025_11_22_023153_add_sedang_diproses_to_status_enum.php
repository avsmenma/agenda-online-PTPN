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
        // Add 'sedang diproses' to status enum
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'menunggu_approved_pengiriman',
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'sent_to_ibub',
            'sedang diproses',
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
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi'
        ) NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'sedang diproses' from status enum (revert to previous state)
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
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
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi'
        ) NOT NULL DEFAULT 'draft'");
    }
};
