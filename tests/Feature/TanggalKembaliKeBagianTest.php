<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Menguji kolom `tanggal_kembali_ke_bagian` — stempel waktu saat Team Verifikasi
 * mengembalikan dokumen ke Bagian asalnya.
 *
 * Kolom ini sudah lama terdaftar di katalog (config/document_columns.php:50)
 * sehingga muncul di tabel, tetapi kolom databasenya TIDAK PERNAH ADA sampai
 * migrasi 2026_08_06_100000 — selalu tampil '-' dan mustahil terisi.
 *
 * Diisi OTOMATIS, bukan diketik: karena itu ada dua pintu yang harus sama-sama
 * mengisinya, dan kolomnya wajib hanya-baca di klien.
 */
class TanggalKembaliKeBagianTest extends TestCase
{
    use RefreshDatabase;

    private function dokumenDiVerifikasi(string $bagian = 'KEU'): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => $bagian,
        ]);
    }

    private function verifikasi(): User
    {
        return User::factory()->create(['role' => 'team_verifikasi']);
    }

    public function test_kolom_database_benar_benar_ada(): void
    {
        // Migrasi dijaga hasColumn, jadi test ini juga membuktikan migrasinya jalan
        // dan bukan sekadar no-op diam-diam.
        $this->assertTrue(
            Schema::hasColumn('dokumens', 'tanggal_kembali_ke_bagian'),
            'Kolom tanggal_kembali_ke_bagian tidak ada — katalog kolom akan menjanjikan sesuatu yang tak bisa terisi.'
        );
    }

    public function test_pengembalian_lewat_dropdown_mengisi_stempel(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi();

        $this->assertNull($dokumen->tanggal_kembali_ke_bagian);

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Lampiran faktur belum lengkap.',
            ])
            ->assertOk();

        $this->assertNotNull(
            $dokumen->fresh()->tanggal_kembali_ke_bagian,
            'Jalur dropdown Pengurus Dokumen tidak mengisi stempel.'
        );
    }

    public function test_pengembalian_lewat_halaman_khusus_mengisi_stempel(): void
    {
        // Pintu KEDUA. Kalau hanya satu pintu yang mengisi, kolomnya kosong
        // separuh waktu tergantung jalur yang dipakai user.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi();

        $this->actingAs($this->verifikasi())
            ->postJson(route('returns.verifikasi.to-bidang', $dokumen), [
                'bidang_return_reason' => 'Nilai rupiah tidak cocok dengan lampiran.',
            ])
            ->assertOk();

        $this->assertNotNull(
            $dokumen->fresh()->tanggal_kembali_ke_bagian,
            'Jalur halaman Pengembalian Ke Bagian tidak mengisi stempel.'
        );
    }

    public function test_pengembalian_ke_operator_tidak_mengisi_stempel(): void
    {
        // Stempel ini KHUSUS kembali-ke-Bagian. `returned_at` yang generik boleh
        // terisi, tapi kolom ini tidak — itulah alasan kolom terpisah dibuat.
        $dokumen = $this->dokumenDiVerifikasi();

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertOk();

        $segar = $dokumen->fresh();
        $this->assertNotNull($segar->returned_at, 'returned_at generik seharusnya tetap terisi.');
        $this->assertNull(
            $segar->tanggal_kembali_ke_bagian,
            'Pengembalian ke Operator tidak boleh mengisi stempel kembali-ke-Bagian.'
        );
    }

    public function test_kolom_ditandai_hanya_baca_di_klien(): void
    {
        // Diisi otomatis; bila selnya tampak bisa diedit, user akan mengetik lalu
        // ditolak server — persis bug yang baru diperbaiki di kolom lain.
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $this->assertMatchesRegularExpression(
            '/const NON_EDITABLE_FIELDS = \[[^\]]*\'tanggal_kembali_ke_bagian\'/',
            $js,
            'tanggal_kembali_ke_bagian tidak ada di NON_EDITABLE_FIELDS — selnya akan tampak bisa diedit.'
        );
    }
}
