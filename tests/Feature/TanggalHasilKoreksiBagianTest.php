<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Menguji kolom `tanggal_hasil_koreksi_bagian` — stempel waktu saat dokumen hasil
 * revisi DITERIMA KEMBALI dari Bagian oleh Team Verifikasi.
 *
 * Pasangan dari `tanggal_kembali_ke_bagian`: yang itu mencatat kapan dokumen
 * DIKIRIM ke Bagian, yang ini kapan dokumen KEMBALI. Tanpa keduanya, lama Bagian
 * mengoreksi dokumen tak bisa diukur.
 *
 * Kolom ini sudah lama terdaftar di katalog (config/document_columns.php:51)
 * sehingga muncul di tabel, tetapi kolom databasenya TIDAK PERNAH ADA sampai
 * migrasi 2026_08_07_100000 — selalu tampil '-' dan mustahil terisi.
 */
class TanggalHasilKoreksiBagianTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_database_benar_benar_ada(): void
    {
        // Migrasi dijaga hasColumn, jadi test ini sekaligus membuktikan migrasinya
        // jalan dan bukan no-op diam-diam.
        $this->assertTrue(
            Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian'),
            'Kolom tanggal_hasil_koreksi_bagian tidak ada — katalog kolom akan menjanjikan sesuatu yang tak bisa terisi.'
        );
    }
}
