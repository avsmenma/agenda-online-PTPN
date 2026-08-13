<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menjaga kontrak tampilan ponsel role Bagian.
 *
 * Dua test paling penting di berkas ini bekerja BERSAMA untuk menegakkan janji
 * "nol perubahan desktop" secara mekanis, bukan sekadar niat baik:
 *   - test_seluruh_aturan_mobile_terkurung_media_query — memindai kurung kurawal
 *     per-karakter (BUKAN regex bersarang) sehingga tetap benar walau isi
 *     @media punya nesting lebih dari 1 tingkat (mis. @keyframes di dalam
 *     @media, yang punya blok persentase bersarang lagi di dalamnya).
 *   - test_setiap_media_query_berkondisi_max_width_768px — memastikan breakpoint-nya
 *     sendiri tidak salah ketik (mis. "768px" jadi "786px"); test pertama di
 *     atas HANYA memeriksa bahwa isi berkas terkurung SATU kondisi @media yang
 *     konsisten, ia tidak memeriksa kondisi itu bernilai benar.
 */
class MobileBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar dokumen Bagian memakai SUBSTRING_INDEX (fungsi MySQL) yang
        // tak ada di SQLite. Polyfill sama dengan PerjalananDokumenBagianTest.
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
        // CheckBagianRole menuntut role BERAWALAN 'bagian_' DAN bagian_code terisi.
        return User::factory()->create([
            'role'        => 'bagian_' . strtolower($kode),
            'bagian_code' => $kode,
        ]);
    }

    private function mobileCss(): string
    {
        $path = public_path('css/mobile.css');
        $this->assertFileExists($path, 'public/css/mobile.css wajib ada.');

        return file_get_contents($path);
    }

    public function test_seluruh_aturan_mobile_terkurung_media_query(): void
    {
        $css = $this->mobileCss();

        // Buang komentar /* ... */ agar contoh kode di dalamnya tak ikut terhitung.
        $tanpaKomentar = preg_replace('#/\*.*?\*/#s', '', $css);

        // Pindai kurung kurawal per-karakter, lacak kedalaman. Setiap kali kurung
        // buka terjadi PERSIS di kedalaman 0 (yakni ia membuka blok top-level),
        // teks yang mendahuluinya sejak kurung tutup terakhir WAJIB persis
        // "@media (max-width: 768px)" — apa pun selain itu berarti ada deklarasi
        // CSS (atau @media lain) yang hidup di luar blok media ponsel, dan akan
        // ikut berlaku di desktop. Kurung di kedalaman >=1 (termasuk nesting
        // berlapis milik @keyframes) tak diperiksa headernya — sudah berada
        // aman di dalam blok top-level yang lolos verifikasi.
        $depth = 0;
        $headerBuffer = '';
        $panjang = strlen($tanpaKomentar);

        for ($i = 0; $i < $panjang; $i++) {
            $char = $tanpaKomentar[$i];

            if ($char === '{') {
                if ($depth === 0) {
                    $header = trim(preg_replace('/\s+/', ' ', $headerBuffer));
                    $this->assertSame(
                        '@media (max-width: 768px)',
                        $header,
                        "Aturan top-level ditemukan di luar @media (max-width: 768px): \"{$header}\" — ini akan mengubah tampilan desktop."
                    );
                }
                $depth++;
                $headerBuffer = '';
            } elseif ($char === '}') {
                $depth--;
                $this->assertGreaterThanOrEqual(
                    0,
                    $depth,
                    'Kurung kurawal tidak seimbang: kurung tutup berlebih di public/css/mobile.css.'
                );
                $headerBuffer = '';
            } elseif ($depth === 0) {
                $headerBuffer .= $char;
            }
        }

        $this->assertSame(
            0,
            $depth,
            'Kurung kurawal tidak seimbang: ada kurung buka yang tak tertutup di public/css/mobile.css.'
        );
        $this->assertSame(
            '',
            trim(preg_replace('/\s+/', ' ', $headerBuffer)),
            'Ada teks/deklarasi tersisa di luar blok @media setelah kurung terakhir.'
        );
    }

    public function test_setiap_media_query_berkondisi_max_width_768px(): void
    {
        $css = $this->mobileCss();
        $tanpaKomentar = preg_replace('#/\*.*?\*/#s', '', $css);

        // Tangkap kondisi SETIAP kemunculan @media di berkas (berapa pun
        // jumlahnya, di kedalaman berapa pun) — test di atas hanya menjaga
        // konsistensi struktur, test ini menjaga breakpoint-nya sendiri tidak
        // salah ketik (mis. "768px" jadi "786px" tetap akan lolos test
        // sebelumnya selama konsisten, tapi salah secara bisnis).
        preg_match_all('/@media\s*([^{]*)\{/', $tanpaKomentar, $cocok);

        $this->assertNotEmpty(
            $cocok[1],
            'Tidak ada @media ditemukan di public/css/mobile.css — berkas ini wajib punya minimal satu blok @media.'
        );

        foreach ($cocok[1] as $kondisiMentah) {
            $kondisiRapi = trim(preg_replace('/\s+/', ' ', $kondisiMentah));

            $this->assertSame(
                '(max-width: 768px)',
                $kondisiRapi,
                "Ditemukan @media dengan kondisi salah: \"{$kondisiRapi}\" — breakpoint WAJIB persis (max-width: 768px)."
            );
        }
    }

    public function test_mobile_css_ter_link_di_layout(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        // Wajib lewat Asset::versioned() — bukan asset() polos — agar cache
        // browser ter-bust saat berkas berubah (pola berkas CSS lain di layout).
        $this->assertStringContainsString("Asset::versioned('css/mobile.css')", $layout);
    }

    public function test_layout_punya_scrim_dan_cabang_lebar_layar(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('mobile-drawer-scrim', $layout);
        // Cabang lebar layar: di ponsel hamburger menggerakkan drawer, BUKAN
        // menulis localStorage sidebar_collapsed milik desktop.
        $this->assertStringContainsString('mobile-drawer-open', $layout);
        $this->assertStringContainsString('max-width: 768px', $layout);
    }

    public function test_drawer_dan_konten_diatur_di_css(): void
    {
        $css = $this->mobileCss();

        // Sidebar disembunyikan dengan transform (bukan display:none) supaya
        // bisa dianimasikan menggeser masuk.
        $this->assertStringContainsString('.sidebar-owner', $css);
        $this->assertStringContainsString('translateX(-100%)', $css);
        // Konten mengambil lebar penuh — inilah yang mengembalikan 72px yang dicuri sidebar.
        $this->assertStringContainsString('margin-left: 0', $css);
    }

    public function test_kartu_mobile_terender_sebanyak_baris_tabel(): void
    {
        Dokumen::create([
            'nomor_agenda'    => 'MOB001_2026',
            'nomor_spp'       => 'SPP-MOB-1',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
            'nilai_rupiah'    => 42500000,
            'dibayar_kepada'  => 'PT Sumber Makmur',
        ]);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Satu dokumen → satu kartu.
        $this->assertSame(1, substr_count($html, 'class="mob-card"'));
        // Kartu memuat data yang sama dengan tabel.
        $this->assertStringContainsString('MOB001_2026', $html);
        $this->assertStringContainsString('PT Sumber Makmur', $html);
        // Nilai diformat gaya Indonesia.
        $this->assertStringContainsString('42.500.000', $html);
        // Badge status ikut dirender di kartu (dari helper Task 1).
        $this->assertStringContainsString('Belum Siap Dibayar', $html);
    }

    public function test_kartu_memakai_fungsi_modal_yang_sudah_ada(): void
    {
        Dokumen::create([
            'nomor_agenda'    => 'MOB002_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ]);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Kartu memanggil fungsi perjalanan yang SUDAH ADA, dengan membawa
        // atribut data-perjalanan (kontrak fungsi tersebut).
        $posisiKartu = strpos($html, 'class="mob-card"');
        $this->assertNotFalse($posisiKartu, 'Kartu mobile tidak ditemukan.');

        $potonganKartu = substr($html, $posisiKartu, 2000);
        $this->assertStringContainsString('tampilkanPerjalanan(this)', $potonganKartu);
        $this->assertStringContainsString('data-perjalanan', $potonganKartu);
    }

    public function test_pembungkus_kartu_hanya_tampil_di_ponsel(): void
    {
        $css = $this->mobileCss();
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        // Pembungkus disembunyikan di desktop. Aturan display:none itu TIDAK boleh
        // ada di mobile.css (berkas itu hanya berisi @media) — ia dibawa partial
        // sendiri lewat @push('styles').
        $partial = file_get_contents(
            resource_path('views/bagian/partials/_kartuDokumenMobile.blade.php')
        );

        $this->assertStringContainsString("@push('styles')", $partial);
        $this->assertStringContainsString('.mob-cards', $partial);
        $this->assertStringContainsString('display: none', $partial);

        // Di mobile.css pembungkus DITAMPILKAN kembali (di dalam @media).
        $this->assertStringContainsString('.mob-cards', $css);
    }

    public function test_css_kartu_dipush_sebelum_markup(): void
    {
        // Regresi flash-of-unstyled: kalau CSS display:none ter-parse SETELAH
        // markup, kartu berkedip muncul di desktop sebelum disembunyikan.
        // Pelajaran dari program modal kustomisasi kolom (CLAUDE.md §7).
        Dokumen::create([
            'nomor_agenda'    => 'MOB003_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ]);

        // Urutan yang diuji: blok <style> dari @push('styles') (berisi
        // ".mob-cards { display: none; }") WAJIB muncul sebelum markup kartu.
        // Dicari string persis milik blok itu — bukan sekadar ".mob-cards",
        // yang juga muncul di <link> mobile.css sehingga assertion jadi hampa.
        $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->assertSeeInOrder(['.mob-cards { display: none; }', 'class="mob-card"'], false);
    }

    public function test_kartu_punya_handler_keyboard_enter_dan_spasi(): void
    {
        // Kartu ber-role="button" tabindex="0" TIDAK dapat aktivasi Enter/Space
        // gratis seperti <button> asli — tanpa handler ini, kartu bisa difokus
        // Tab (janji role="button") tapi menekan Enter/Space tak berbuat apa-apa.
        // Dicari string SPESIFIK ('pembungkus.addEventListener(\'keydown\'' dan
        // 'tampilkanPerjalanan(kartu)'), bukan sekadar "keydown" atau
        // "tampilkanPerjalanan" telanjang — keduanya sudah muncul di tempat lain
        // pada halaman ini (partial global _activeCellNav/_inlineEditEngine
        // punya listener keydown sendiri; tampilkanPerjalanan juga dipanggil
        // via onclick="tampilkanPerjalanan(this)" dan didefinisikan sebagai
        // window.tampilkanPerjalanan) sehingga assertion longgar akan lolos
        // tanpa benar-benar menguji handler baru ini.
        Dokumen::create([
            'nomor_agenda'    => 'MOB004_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => 'AKN',
        ]);

        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString("pembungkus.addEventListener('keydown'", $html);
        $this->assertStringContainsString('tampilkanPerjalanan(kartu)', $html);
    }
}
