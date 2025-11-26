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
            // Inbox approval system fields
            $table->enum('inbox_approval_for', ['IbuB', 'Perpajakan', 'Akutansi'])->nullable()->after('universal_approval_rejection_reason');
            $table->enum('inbox_approval_status', ['pending', 'approved', 'rejected'])->nullable()->default('pending')->after('inbox_approval_for');
            $table->timestamp('inbox_approval_sent_at')->nullable()->after('inbox_approval_status');
            $table->timestamp('inbox_approval_responded_at')->nullable()->after('inbox_approval_sent_at');
            $table->text('inbox_approval_reason')->nullable()->after('inbox_approval_responded_at');
            $table->string('inbox_original_status', 50)->nullable()->after('inbox_approval_reason'); // backup status sebelum masuk inbox
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'inbox_approval_for',
                'inbox_approval_status',
                'inbox_approval_sent_at',
                'inbox_approval_responded_at',
                'inbox_approval_reason',
                'inbox_original_status'
            ]);
        });
    }
};
