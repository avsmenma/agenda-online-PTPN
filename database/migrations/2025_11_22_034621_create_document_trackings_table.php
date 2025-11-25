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
        Schema::create('document_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('dokumens')->onDelete('cascade');
            $table->string('action', 100); // created, sent_to_ibub, deadline_set, sent_to_perpajakan, etc.
            $table->string('actor', 100); // ibua, ibub, perpajakan, akutansi, pembayaran
            $table->text('metadata')->nullable(); // JSON data for additional context
            $table->timestamp('action_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['document_id', 'action_at']);
            $table->index(['action', 'action_at']);
            $table->index('actor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_trackings');
    }
};
