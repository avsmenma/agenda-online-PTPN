<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds urgency alert fields to the dokumens table.
     * These fields track when an admin has sent an urgency reminder to the responsible role.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Flag: is urgency currently active for this document?
            $table->boolean('urgency_active')->default(false);

            // When was the urgency alert sent?
            $table->timestamp('urgency_sent_at')->nullable();

            // Which admin sent the urgency alert?
            $table->unsignedBigInteger('urgency_sent_by')->nullable();

            // Index for fast filtering of urgency-active documents
            $table->index('urgency_active', 'idx_urgency_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex('idx_urgency_active');
            $table->dropColumn(['urgency_active', 'urgency_sent_at', 'urgency_sent_by']);
        });
    }
};
