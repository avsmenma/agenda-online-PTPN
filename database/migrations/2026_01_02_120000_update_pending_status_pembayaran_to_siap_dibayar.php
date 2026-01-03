<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update dokumen yang memiliki status_pembayaran = 'pending' menjadi 'siap_dibayar'
        // Hanya update jika dokumen sudah dikirim ke pembayaran (current_handler = 'pembayaran' atau status = 'sent_to_pembayaran')
        // dan belum dibayar (tidak ada tanggal_dibayar atau link_bukti_pembayaran)
        DB::table('dokumens')
            ->where(function ($query) {
                $query->where('status_pembayaran', 'pending')
                      ->orWhere('status_pembayaran', 'Pending')
                      ->orWhere('status_pembayaran', 'PENDING');
            })
            ->where(function ($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
            })
            ->whereNull('tanggal_dibayar')
            ->whereNull('link_bukti_pembayaran')
            ->update([
                'status_pembayaran' => 'siap_dibayar',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini adalah data fix
        // Jika perlu rollback, bisa set kembali ke 'pending', tapi tidak disarankan
    }
};

