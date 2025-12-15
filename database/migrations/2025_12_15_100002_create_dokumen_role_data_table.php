<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dokumen_role_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumens')->onDelete('cascade');
            $table->string('role_code', 50);
            $table->dateTime('received_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->integer('deadline_days')->nullable();
            $table->string('deadline_note', 500)->nullable();
            $table->json('role_specific_data')->nullable(); // For role-specific fields
            $table->timestamps();

            // Foreign key to roles table
            $table->foreign('role_code')->references('code')->on('roles');

            // Unique constraint: one data record per dokumen per role
            $table->unique(['dokumen_id', 'role_code'], 'uk_dokumen_role_data');

            // Index for queries
            $table->index('role_code', 'idx_role_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_role_data');
    }
};
