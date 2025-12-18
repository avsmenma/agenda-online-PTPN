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
        Schema::create('document_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumens')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type'); // 'viewing', 'editing'
            $table->timestamp('last_activity_at');
            $table->timestamps();
            
            // Unique constraint: one activity type per user per document
            $table->unique(['dokumen_id', 'user_id', 'activity_type']);
            
            // Indexes for performance
            $table->index(['dokumen_id', 'activity_type']);
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_activities');
    }
};

