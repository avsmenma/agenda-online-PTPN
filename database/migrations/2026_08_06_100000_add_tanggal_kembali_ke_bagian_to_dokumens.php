<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom `tanggal_kembali_ke_bagian` — stempel waktu saat Team Verifikasi
 * mengembalikan dokumen ke Bagian asalnya.
 *
 * Kolom ini SUDAH lama terdaftar di katalog (config/document_columns.php:50)
 * sehingga muncul sebagai pilihan kolom di tabel, TETAPI kolom databasenya tidak
 * pernah dibuat — nol migrasi, dikonfirmasi ke information_schema produksi
 * 2026-08-06. Akibatnya selalu tampil '-' dan tak mungkin terisi.
 *
 * BEDA dengan `returned_at` yang sudah ada: `returned_at` ditimpa setiap kali
 * dokumen dikembalikan KE SIAPA PUN (termasuk ke Operator), sedangkan kolom ini
 * khusus mencatat kembali-ke-Bagian. Untuk dokumen yang beberapa kali
 * dikembalikan, keduanya akan berbeda.
 *
 * Diisi OTOMATIS oleh kedua pintu pengembalian ke Bagian
 * (DocumentHandlerController::returnDirectlyToBagian dan
 * TeamVerifikasiController::returnToBidang) — bukan diketik manual. Karena itu
 * kolomnya juga dijadikan hanya-baca di tabel (NON_EDITABLE_FIELDS di
 * public/js/document-tabulator.js).
 *
 * Idempoten (aturan 6 CLAUDE.md): dijaga hasColumn agar aman dijalankan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dokumens', 'tanggal_kembali_ke_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->timestamp('tanggal_kembali_ke_bagian')
                ->nullable()
                ->after('tanggal_selesai_diproses');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dokumens', 'tanggal_kembali_ke_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('tanggal_kembali_ke_bagian');
        });
    }
};
