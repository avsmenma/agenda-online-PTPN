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
        Schema::create('deadline_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumens')->onDelete('cascade');
            $table->string('handler'); // akuntansi, perpajakan, ibu_a
            $table->string('deadline_type'); // deadline_at, deadline_perpajakan_at, etc
            $table->timestamp('deadline_at');
            $table->enum('status', ['warning', 'danger'])->default('warning'); // warning = >1 hari, danger = >1 hari
            $table->integer('days_overdue')->default(0); // jumlah hari terlambat
            $table->timestamp('last_notified_at')->nullable(); // waktu notifikasi terakhir
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['handler', 'status', 'is_read']);
            $table->index('last_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadline_notifications');
    }
};
