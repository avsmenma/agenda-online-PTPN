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
            // Composite index for IbuB queries
            $table->index(['current_handler', 'sent_to_ibub_at'], 'idx_current_handler_sent_at');

            // Index for deadline queries
            $table->index(['deadline_at'], 'idx_deadline_at');

            // Index for searching
            $table->index(['nomor_agenda'], 'idx_nomor_agenda');
            $table->index(['nomor_spp'], 'idx_nomor_spp');

            // Composite index for status filtering
            $table->index(['current_handler', 'status'], 'idx_current_handler_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex('idx_current_handler_sent_at');
            $table->dropIndex('idx_deadline_at');
            $table->dropIndex('idx_nomor_agenda');
            $table->dropIndex('idx_nomor_spp');
            $table->dropIndex('idx_current_handler_status');
        });
    }
};
