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
            // Pembayaran tracking fields
            $table->timestamp('sent_to_pembayaran_at')->nullable()->after('returned_from_perpajakan_at');
            $table->timestamp('processed_pembayaran_at')->nullable()->after('sent_to_pembayaran_at');
            $table->timestamp('returned_from_pembayaran_at')->nullable()->after('processed_pembayaran_at');

            // Pembayaran deadline fields
            $table->timestamp('deadline_pembayaran_at')->nullable()->after('returned_from_pembayaran_at');
            $table->integer('deadline_pembayaran_days')->nullable()->after('deadline_pembayaran_at');
            $table->text('deadline_pembayaran_note')->nullable()->after('deadline_pembayaran_days');

            // Pembayaran specific fields
            $table->enum('status_pembayaran', ['belum_dibayar', 'siap_dibayar', 'sudah_dibayar'])->nullable()->after('deadline_pembayaran_note');
            $table->timestamp('tanggal_dibayar')->nullable()->after('status_pembayaran');
            $table->string('bukti_pembayaran')->nullable()->after('tanggal_dibayar');
            $table->text('catatan_pembayaran')->nullable()->after('bukti_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'sent_to_pembayaran_at',
                'processed_pembayaran_at',
                'returned_from_pembayaran_at',
                'deadline_pembayaran_at',
                'deadline_pembayaran_days',
                'deadline_pembayaran_note',
                'status_pembayaran',
                'tanggal_dibayar',
                'bukti_pembayaran',
                'catatan_pembayaran',
            ]);
        });
    }
};
