<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji filter Vendor & Item Sub Kriteria di halaman role Bagian.
 *
 * Vendor  = kolom `dibayar_kepada` (nama penerima pembayaran).
 * Item Sub Kriteria = kolom `jenis_sub_pekerjaan` (lihat DokumenController:
 * ItemSubKriteria dipetakan ke kolom itu).
 *
 * Berlaku untuk desktop DAN ponsel: keduanya memakai <form> filter yang sama —
 * di ponsel form itu hanya dipindah ke dalam popup lewat CSS.
 */
class FilterVendorSubKriteriaBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian mengurutkan pakai SUBSTRING_INDEX (fungsi
        // MySQL) yang tak ada di SQLite. Polyfill sama dengan test bagian lain.
        $pdo = DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);

                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function userBagian(string $kode = 'AKN'): User
    {
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    private function dokumen(string $nomor, array $atribut = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomor . '_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ], $atribut));
    }

    public function test_filter_vendor_menyaring_dokumen(): void
    {
        $this->dokumen('V001', ['dibayar_kepada' => 'PT Sumber Makmur']);
        $this->dokumen('V002', ['dibayar_kepada' => 'CV Karya Abadi']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents?vendor=' . urlencode('PT Sumber Makmur'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('V001_2026', $html);
        $this->assertStringNotContainsString('V002_2026', $html);
    }

    public function test_filter_vendor_cocok_persis_bukan_sebagian(): void
    {
        // Dicocokkan PERSIS, bukan LIKE: nilai dropdown berasal dari kolom yang
        // sama sehingga selalu cocok utuh. Dengan LIKE, memilih "PT Sumber"
        // akan ikut menjaring "PT Sumber Makmur Jaya" — dokumen milik vendor
        // LAIN bocor ke hasil filter.
        $this->dokumen('V003', ['dibayar_kepada' => 'PT Sumber']);
        $this->dokumen('V004', ['dibayar_kepada' => 'PT Sumber Makmur Jaya']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents?vendor=' . urlencode('PT Sumber'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('V003_2026', $html);
        $this->assertStringNotContainsString('V004_2026', $html);
    }

    public function test_filter_item_sub_kriteria_menyaring_dokumen(): void
    {
        $this->dokumen('S001', ['jenis_sub_pekerjaan' => 'Biaya Sumbangan dan Iuran']);
        $this->dokumen('S002', ['jenis_sub_pekerjaan' => 'Biaya Perjalanan Dinas']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents?sub_kriteria=' . urlencode('Biaya Sumbangan dan Iuran'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('S001_2026', $html);
        $this->assertStringNotContainsString('S002_2026', $html);
    }

    public function test_dropdown_hanya_memuat_nilai_milik_bagian_sendiri(): void
    {
        // Daftar vendor global memuat ratusan nama dari bagian lain yang tak
        // pernah muncul di halaman ini — memilihnya hanya menghasilkan tabel
        // kosong. Dropdown wajib dibatasi ke dokumen milik bagian sendiri.
        $this->dokumen('B001', ['dibayar_kepada' => 'Vendor Milik AKN']);
        $this->dokumen('B002', [
            'bagian'          => 'SKH',
            'dibayar_kepada'  => 'Vendor Milik SKH',
        ]);

        $html = $this->actingAs($this->userBagian('AKN'))
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Vendor Milik AKN', $html);
        $this->assertStringNotContainsString('Vendor Milik SKH', $html);
    }

    public function test_dropdown_vendor_tidak_menyempit_oleh_filter_lain(): void
    {
        // Kalau daftar vendor ikut menyempit mengikuti filter yang sedang aktif,
        // user tak bisa BERPINDAH vendor setelah memilih satu: opsi lain lenyap
        // dari dropdown-nya sendiri dan satu-satunya jalan keluar adalah
        // mengosongkan filter lewat URL.
        $this->dokumen('P001', ['dibayar_kepada' => 'Vendor Alpha']);
        $this->dokumen('P002', ['dibayar_kepada' => 'Vendor Beta']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents?vendor=' . urlencode('Vendor Alpha'))
            ->assertOk()
            ->getContent();

        // Vendor Beta harus TETAP ada sebagai opsi meski sedang tersaring keluar.
        $this->assertStringContainsString('Vendor Beta', $html);
    }

    public function test_dropdown_sub_kriteria_disembunyikan_saat_datanya_kosong(): void
    {
        // Per 2026-08-13 baru 3 dokumen se-database yang mengisi kolom ini.
        // Dropdown berisi hanya "Semua" tampak seperti kontrol rusak, jadi ia
        // disembunyikan sampai datanya terisi.
        $this->dokumen('K001', ['dibayar_kepada' => 'PT Apa Saja']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Dicari <select name="sub_kriteria">, BUKAN nama kelasnya: kelas
        // .btn-subkriteria-select juga muncul di blok CSS dropdown (yang selalu
        // dirender), sehingga assertion atas nama kelas tak pernah bisa
        // membuktikan dropdown-nya absen.
        $this->assertStringNotContainsString('name="sub_kriteria"', $html);

        // ...dan MUNCUL sendiri begitu ada datanya, tanpa perubahan kode.
        $this->dokumen('K002', ['jenis_sub_pekerjaan' => 'Biaya Sumbangan dan Iuran']);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="sub_kriteria"', $html);
    }

    public function test_semua_dropdown_filter_bergaya_sama(): void
    {
        // .btn-month-select dulu TIDAK terdaftar di blok CSS dropdown sehingga
        // jatuh ke gaya bawaan browser dan tampak berbeda sendiri di antara
        // tetangganya (dilaporkan user 2026-08-13). Kelima kelas wajib terdaftar
        // di blok dasar DAN blok :hover — menambah dropdown baru tanpa
        // mendaftarkannya akan mengulang cacat yang sama.
        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        foreach (['btn-year-select', 'btn-month-select', 'btn-status-select', 'btn-vendor-select'] as $kelas) {
            $this->assertMatchesRegularExpression(
                '/\.' . $kelas . ',\s*\n/',
                $html,
                "Kelas .{$kelas} tidak terdaftar di blok gaya dropdown — tampilannya akan berbeda sendiri."
            );

            $this->assertStringContainsString(
                '.' . $kelas . ':hover',
                $html,
                "Kelas .{$kelas} tidak punya gaya :hover — tampilannya akan berbeda sendiri saat disentuh."
            );
        }
    }
}
