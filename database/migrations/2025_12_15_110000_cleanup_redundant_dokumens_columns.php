<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration removes redundant columns from dokumens table
     * that are now handled by the new dokumen_statuses and dokumen_role_data tables.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Drop inbox_approval columns (now in dokumen_statuses)
            $columnsToDrop = [
                'inbox_approval_for',
                'inbox_approval_status',
                'inbox_approval_sent_at',
                'inbox_approval_responded_at',
                'inbox_approval_reason',
                'inbox_original_status',
                // Drop old approval system columns (never fully used)
                'pending_approval_for',
                'pending_approval_at',
                'approval_responded_at',
                'approval_responded_by',
                'approval_rejection_reason',
                // Drop universal approval columns (never fully used)
                'universal_approval_for',
                'universal_approval_sent_at',
                'universal_approval_responded_at',
                'universal_approval_responded_by',
                'universal_approval_rejection_reason',
                // Drop redundant timestamp columns (now in dokumen_role_data)
                'sent_to_ibub_at',
                'sent_to_perpajakan_at',
                'sent_to_pembayaran_at',
                'processed_at',
                'processed_perpajakan_at',
                'returned_to_ibua_at',
                'returned_from_perpajakan_at',
                'returned_from_akutansi_at',
                // Drop duplicate deadline columns (now in dokumen_role_data)
                'deadline_at',
                'deadline_days',
                'deadline_note',
                'deadline_completed_at',
                'deadline_perpajakan_at',
                'deadline_perpajakan_days',
                'deadline_perpajakan_note',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('dokumens', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Re-add inbox columns
            $table->string('inbox_approval_for')->nullable();
            $table->string('inbox_approval_status')->nullable();
            $table->timestamp('inbox_approval_sent_at')->nullable();
            $table->timestamp('inbox_approval_responded_at')->nullable();
            $table->text('inbox_approval_reason')->nullable();
            $table->string('inbox_original_status')->nullable();

            // Re-add old approval columns
            $table->string('pending_approval_for')->nullable();
            $table->timestamp('pending_approval_at')->nullable();
            $table->timestamp('approval_responded_at')->nullable();
            $table->string('approval_responded_by')->nullable();
            $table->text('approval_rejection_reason')->nullable();

            // Re-add universal approval columns
            $table->string('universal_approval_for')->nullable();
            $table->timestamp('universal_approval_sent_at')->nullable();
            $table->timestamp('universal_approval_responded_at')->nullable();
            $table->string('universal_approval_responded_by')->nullable();
            $table->text('universal_approval_rejection_reason')->nullable();

            // Re-add timestamp columns
            $table->timestamp('sent_to_ibub_at')->nullable();
            $table->timestamp('sent_to_perpajakan_at')->nullable();
            $table->timestamp('sent_to_pembayaran_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('processed_perpajakan_at')->nullable();
            $table->timestamp('returned_to_ibua_at')->nullable();
            $table->timestamp('returned_from_perpajakan_at')->nullable();
            $table->timestamp('returned_from_akutansi_at')->nullable();

            // Re-add deadline columns
            $table->timestamp('deadline_at')->nullable();
            $table->integer('deadline_days')->nullable();
            $table->string('deadline_note')->nullable();
            $table->timestamp('deadline_completed_at')->nullable();
            $table->timestamp('deadline_perpajakan_at')->nullable();
            $table->integer('deadline_perpajakan_days')->nullable();
            $table->string('deadline_perpajakan_note')->nullable();
        });
    }
};
