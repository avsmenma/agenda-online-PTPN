<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds auto_forwarded_at column to dokumens table and creates
     * the dokumen_auto_forward_queue table used by the DB trigger
     * to notify Laravel when an external project marks a document
     * as 'sudah_dibayar' via raw DB query.
     */
    public function up(): void
    {
        // 1. Add auto_forwarded_at to dokumens
        Schema::table('dokumens', function (Blueprint $table) {
            $table->timestamp('auto_forwarded_at')
                  ->nullable()
                  ->comment('Timestamp kapan dokumen di-auto-forward ke pembayaran oleh sistem')
                  ->after('status_pembayaran');

            $table->index('auto_forwarded_at', 'idx_auto_forwarded_at');
        });

        // 2. Create queue table populated by MySQL trigger
        Schema::create('dokumen_auto_forward_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dokumen_id')->unique(); // UNIQUE prevents duplicate queue entries
            $table->timestamp('triggered_at')->comment('Kapan trigger dipicu');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable()->comment('Kapan diproses oleh scheduler');
            $table->text('error_message')->nullable()->comment('Pesan error jika status = failed');
            $table->timestamps();

            $table->index('status', 'idx_auto_forward_status');
            $table->foreign('dokumen_id')->references('id')->on('dokumens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_auto_forward_queue');

        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex('idx_auto_forwarded_at');
            $table->dropColumn('auto_forwarded_at');
        });
    }
};
