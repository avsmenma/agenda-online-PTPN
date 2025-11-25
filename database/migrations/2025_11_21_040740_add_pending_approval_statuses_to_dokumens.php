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
        // Update status enum to include pending approval statuses
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'pending_approval_ibub',
            'sent_to_ibub',
            'sedang diproses',
            'pending_approval_perpajakan',
            'sent_to_perpajakan',
            'pending_approval_akutansi',
            'sent_to_akutansi',
            'approved_ibub',
            'rejected_ibub',
            'returned_to_ibua',
            'returned_to_department',
            'returned_to_bidang',
            'selesai'
        ) DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original status enum without pending approval
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'sedang diproses',
            'sent_to_ibub',
            'approved_ibub',
            'rejected_ibub',
            'returned_to_ibua',
            'sent_to_perpajakan',
            'sent_to_akutansi',
            'returned_to_department',
            'returned_to_bidang',
            'selesai'
        ) DEFAULT 'draft'");
    }
};
