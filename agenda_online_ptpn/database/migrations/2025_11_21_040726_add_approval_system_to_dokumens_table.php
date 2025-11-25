<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Tracking siapa yang akan menerima dokumen (pending approval)
            $table->string('pending_approval_for')->nullable()->after('current_handler');

            // Timestamp ketika dokumen dikirim untuk approval
            $table->timestamp('pending_approval_at')->nullable()->after('pending_approval_for');

            // Timestamp ketika approval diterima/ditolak
            $table->timestamp('approval_responded_at')->nullable()->after('pending_approval_at');

            // User yang merespon approval (accept/reject)
            $table->string('approval_responded_by')->nullable()->after('approval_responded_at');

            // Alasan jika ditolak
            $table->text('approval_rejection_reason')->nullable()->after('approval_responded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'pending_approval_for',
                'pending_approval_at',
                'approval_responded_at',
                'approval_responded_by',
                'approval_rejection_reason'
            ]);
        });
    }
};
