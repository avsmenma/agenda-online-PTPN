<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel sync_logs — mencatat semua aktivitas sinkronisasi
     * antara Agenda Online dan Cash Bank.
     */
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dokumen_id')->nullable()->index();
            $table->enum('direction', ['ao_to_cb', 'cb_to_ao'])->default('ao_to_cb');
            $table->enum('status', [
                'success',
                'failed',
                'skipped_no_change',
                'conflict_resolved',
            ])->default('success');
            $table->json('fields_synced')->nullable()
                  ->comment('Field apa saja yang ikut di-sync');
            $table->json('conflict_fields')->nullable()
                  ->comment('Field yang bertabrakan saat conflict');
            $table->string('source_wins', 50)->nullable()
                  ->comment('agenda_online atau cash_bank — siapa yang menang jika conflict');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
