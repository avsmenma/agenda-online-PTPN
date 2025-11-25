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
        Schema::table('dokumens', function (Blueprint $table) {
            // Universal approval system fields - check if columns exist first
            if (!Schema::hasColumn('dokumens', 'universal_approval_for')) {
                $table->string('universal_approval_for')->nullable()->after('approval_rejection_reason');
            }
            if (!Schema::hasColumn('dokumens', 'universal_approval_sent_at')) {
                $table->timestamp('universal_approval_sent_at')->nullable()->after('universal_approval_for');
            }
            if (!Schema::hasColumn('dokumens', 'universal_approval_responded_at')) {
                $table->timestamp('universal_approval_responded_at')->nullable()->after('universal_approval_sent_at');
            }
            if (!Schema::hasColumn('dokumens', 'universal_approval_responded_by')) {
                $table->string('universal_approval_responded_by')->nullable()->after('universal_approval_responded_at');
            }
            if (!Schema::hasColumn('dokumens', 'universal_approval_rejection_reason')) {
                $table->text('universal_approval_rejection_reason')->nullable()->after('universal_approval_responded_by');
            }
        });

        // Update status enum to include universal approval statuses (preserving existing values)
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM(
            'draft',
            'pending_approval_ibub',
            'sent_to_ibub',
            'sedang diproses',
            'pending_approval_perpajakan',
            'sent_to_perpajakan',
            'pending_approval_akutansi',
            'sent_to_akutansi',
            'sent_to_pembayaran',
            'approved_ibub',
            'rejected_ibub',
            'returned_to_ibua',
            'returned_to_department',
            'returned_to_bidang',
            'selesai',
            'menunggu_approved_pengiriman',
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'processed_by_ibub',
            'processed_by_perpajakan',
            'processed_by_akutansi',
            'processed_by_pembayaran',
            'completed',
            'returned_to_ibub'
        ) NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'universal_approval_for',
                'universal_approval_sent_at',
                'universal_approval_responded_at',
                'universal_approval_responded_by',
                'universal_approval_rejection_reason'
            ]);
        });

        // Revert status enum
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
        ) NOT NULL DEFAULT 'draft'");
    }
};
