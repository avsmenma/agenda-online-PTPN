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
        Schema::create('dokumen_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumens')->onDelete('cascade');
            $table->string('stage')->nullable(); // 'sender', 'reviewer', 'tax', 'accounting', 'payment'
            $table->string('action'); // 'created', 'sent', 'edited', 'deadline_set', 'form_filled', 'returned', etc.
            $table->string('action_description'); // Deskripsi aksi dalam bahasa Indonesia
            $table->string('performed_by')->nullable(); // 'ibuA', 'ibuB', 'perpajakan', 'akutansi', 'pembayaran'
            $table->text('details')->nullable(); // JSON atau text untuk detail tambahan
            $table->timestamp('action_at');
            $table->timestamps();
            
            $table->index(['dokumen_id', 'stage']);
            $table->index('action_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_activity_logs');
    }
};
