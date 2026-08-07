<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
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

    protected function setUp(): void
    {
        parent::setUp();

        // buildVerifikasiQuery() memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX) pada
        // ORDER BY nomor_agenda — tak tersedia di SQLite. Polyfill sama dengan
        // TanggalKembaliKeBagianTest.
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('regexp', function ($pattern, $value) {
                return preg_match('/' . $pattern . '/u', (string) $value) ? 1 : 0;
            });
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);

                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    public function test_kolom_database_benar_benar_ada(): void
    {
        // Migrasi dijaga hasColumn, jadi test ini sekaligus membuktikan migrasinya
        // jalan dan bukan no-op diam-diam.
        $this->assertTrue(
            Schema::hasColumn('dokumens', 'tanggal_hasil_koreksi_bagian'),
            'Kolom tanggal_hasil_koreksi_bagian tidak ada — katalog kolom akan menjanjikan sesuatu yang tak bisa terisi.'
        );
    }

    /** Dokumen yang SEDANG dikembalikan ke Bagian — kondisi awal terima-balik. */
    private function dokumenDikembalikanKeBagian(string $bagian = 'KEU'): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'returned_to_bidang',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'return_source'   => $bagian,
            'bagian'          => $bagian,
        ]);
    }

    private function verifikasi(): User
    {
        return User::factory()->create(['role' => 'team_verifikasi']);
    }

    public function test_terima_balik_dari_bagian_mengisi_stempel(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDikembalikanKeBagian();

        $this->assertNull($dokumen->tanggal_hasil_koreksi_bagian);

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $this->assertNotNull(
            $dokumen->fresh()->tanggal_hasil_koreksi_bagian,
            'Terima-balik dari Bagian tidak mengisi stempel hasil koreksi.'
        );
    }

    public function test_forward_biasa_ke_verifikasi_tidak_mengisi_stempel(): void
    {
        // INI yang membuat kolomnya bermakna. Kalau setiap perpindahan ke Verifikasi
        // ikut mengisi, angkanya tak menandakan koreksi apa pun.
        $dokumen = Dokumen::create([
            'nomor_agenda'    => '2_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
            'bagian'          => 'KEU',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'operator']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $segar = $dokumen->fresh();
        $this->assertSame('team_verifikasi', $segar->current_handler, 'Forward-nya sendiri harus tetap berhasil.');
        $this->assertNull(
            $segar->tanggal_hasil_koreksi_bagian,
            'Forward biasa operator→verifikasi tidak boleh mengisi stempel hasil koreksi.'
        );
    }

    public function test_koreksi_kedua_menimpa_stempel_pertama(): void
    {
        // Keputusan user: kolom selalu menunjuk siklus koreksi TERAKHIR, sepasang
        // dengan tanggal_kembali_ke_bagian yang juga ditimpa tiap pengembalian.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDikembalikanKeBagian();

        // Siklus 1 sudah terjadi jauh sebelumnya.
        $stempelLama = '2026-01-02 03:04:05';
        $dokumen->forceFill(['tanggal_hasil_koreksi_bagian' => $stempelLama])->save();

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'team_verifikasi',
            ])
            ->assertOk();

        $baru = $dokumen->fresh()->tanggal_hasil_koreksi_bagian;

        $this->assertNotNull($baru);
        $this->assertNotSame(
            $stempelLama,
            $baru->format('Y-m-d H:i:s'),
            'Stempel lama tidak ditimpa — kolom akan menunjuk siklus koreksi yang salah.'
        );
    }

    public function test_nilai_ikut_terkirim_di_endpoint_tabel_verifikasi(): void
    {
        // buildVerifikasiQuery() memakai daftar select EKSPLISIT. Kolom yang tak
        // disebut di sana sampai ke DTO sebagai null meski datanya ada di database —
        // terbukti saat tanggal_kembali_ke_bagian baru dibuat: DB terisi, sel '-'.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);

        $dokumen = $this->dokumenDikembalikanKeBagian();
        $dokumen->forceFill(['tanggal_hasil_koreksi_bagian' => '2026-08-07 09:30:00'])->save();

        $response = $this->actingAs($this->verifikasi())
            ->getJson(route('documents.verifikasi.data'));

        $response->assertOk();

        $baris = collect($response->json('data'))->firstWhere('id', $dokumen->id);

        $this->assertNotNull($baris, 'Dokumen tidak muncul di endpoint tabel.');
        $this->assertNotNull(
            $baris['tanggal_hasil_koreksi_bagian'],
            'Kolom tidak ikut di-select buildVerifikasiQuery() — sel akan selalu tampil "-".'
        );
        $this->assertSame('07/08/2026 09:30', $baris['dates']['tanggal_hasil_koreksi_bagian']);
    }

    public function test_kolom_ditandai_hanya_baca_di_klien(): void
    {
        // Diisi otomatis; bila selnya tampak bisa diedit, user akan mengetik lalu
        // ditolak server — persis keluhan yang sudah diperbaiki di kolom lain.
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $this->assertMatchesRegularExpression(
            '/const NON_EDITABLE_FIELDS = \[[^\]]*\'tanggal_hasil_koreksi_bagian\'/',
            $js,
            'tanggal_hasil_koreksi_bagian tidak ada di NON_EDITABLE_FIELDS — selnya akan tampak bisa diedit.'
        );
    }
}
