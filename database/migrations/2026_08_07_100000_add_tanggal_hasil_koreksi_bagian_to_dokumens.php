<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom `tanggal_hasil_koreksi_bagian` — stempel waktu saat dokumen hasil revisi
 * DITERIMA KEMBALI dari Bagian oleh Team Verifikasi.
 *
 * Pasangan dari `tanggal_kembali_ke_bagian` (migrasi 2026_08_06_100000): yang itu
 * mencatat kapan dokumen DIKIRIM ke Bagian, yang ini kapan dokumen KEMBALI. Tanpa
 * keduanya, lama Bagian mengoreksi dokumen tidak bisa diukur sama sekali.
 *
 * Sama seperti kembarannya, kolom ini SUDAH lama terdaftar di katalog
 * (config/document_columns.php:51) sehingga muncul sebagai pilihan kolom di tabel,
 * TETAPI kolom databasenya tidak pernah dibuat — nol migrasi. Selalu tampil '-'.
 *
 * Diisi OTOMATIS oleh DocumentHandlerController::receiveBackFromBagian(), satu-
 * satunya pintu terima-balik yang ada. Karena diisi otomatis, kolomnya juga
 * hanya-baca di tabel (NON_EDITABLE_FIELDS di public/js/document-tabulator.js).
 *
 * Idempoten (aturan 6 CLAUDE.md): dijaga hasColumn agar aman dijalankan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->timestamp('tanggal_hasil_koreksi_bagian')
                ->nullable()
                ->after('tanggal_kembali_ke_bagian');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('tanggal_hasil_koreksi_bagian');
        });
    }
};
