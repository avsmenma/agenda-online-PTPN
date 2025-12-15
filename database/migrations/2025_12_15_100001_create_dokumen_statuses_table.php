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
        Schema::create('dokumen_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumens')->onDelete('cascade');
            $table->string('role_code', 50);
            $table->enum('status', [
                'pending',      // Menunggu di inbox
                'received',     // Diterima/di-approve dari inbox
                'processing',   // Sedang diproses
                'approved',     // Disetujui
                'rejected',     // Ditolak
                'completed',    // Selesai
                'returned'      // Dikembalikan
            ])->default('pending');
            $table->dateTime('status_changed_at');
            $table->string('changed_by', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key to roles table
            $table->foreign('role_code')->references('code')->on('roles');

            // Unique constraint: one status record per dokumen per role
            $table->unique(['dokumen_id', 'role_code'], 'uk_dokumen_role_status');

            // Indexes for common queries
            $table->index(['role_code', 'status'], 'idx_role_status');
            $table->index(['dokumen_id', 'status'], 'idx_dokumen_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_statuses');
    }
};
