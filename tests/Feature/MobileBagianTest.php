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

    /**
     * Ambil badan (isi antara "{" dan "}" pertama setelahnya) aturan CSS yang
     * selector-nya diawali persis oleh $selectorAwal. Dipakai untuk
     * mempersempit assertion ke SATU aturan spesifik — CLAUDE.md aturan 8:
     * assertion yang mencari string di seluruh berkas biasanya hampa karena
     * string yang sama kebetulan sudah ada di aturan lain yang tak berkaitan
     * (terbukti nyata: "font-size: 16px" & "min-height: 44px" masing-masing
     * muncul di lebih dari satu aturan tak berkaitan di berkas ini).
     */
    private function cssRuleBody(string $css, string $selectorAwal): string
    {
        $posisi = strpos($css, $selectorAwal);
        $this->assertNotFalse($posisi, "Aturan CSS berawalan \"{$selectorAwal}\" tidak ditemukan.");

        $akhir = strpos($css, '}', $posisi);
        $this->assertNotFalse($akhir, "Aturan CSS berawalan \"{$selectorAwal}\" tidak tertutup.");

        return substr($css, $posisi, $akhir - $posisi);
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

        // Posisi WAJIB setelah @stack('styles') — bukan sekadar kehadiran
        // string di atas. <link> ini harus datang SETELAH blok <style> inline
        // raksasa (yang berisi @media (max-width: 768px) miliknya sendiri
        // untuk sidebar collapse desktop): keduanya menyetel margin-left
        // dengan !important pada selector identik (body.owner-layout
        // .content), dan saat importance seri begitu, urutan DOKUMEN yang
        // menentukan cascade. Memindahkan <link> ini ke atas <head> ("tampak
        // lebih rapi") membuat aturan lama menang lagi dan mematikan
        // drawer/margin konten mobile secara diam-diam — sudah pernah
        // terjadi & diperbaiki di tengah program ini (lihat komentar di
        // layout, baris ~3123-3133).
        //
        // assertNotFalse pada KEDUA strpos WAJIB mendahului assertGreaterThan:
        // strpos mengembalikan false saat string yang dicari tak ditemukan,
        // dan PHP membandingkan bool dengan int lewat konversi ke bool —
        // integer posisi mana pun (bukan nol) dikonversi ke true, jadi
        // "<posisi ditemukan> > false" selalu TRUE. Tanpa penjaga ini, kalau
        // "@stack('styles')" kelak dihapus/di-rename, strpos-nya
        // mengembalikan false dan assertGreaterThan(false, <posisi apa
        // pun>) tetap LULUS diam-diam — assertion yang dibuat untuk menutup
        // lubang hampa (Finding 5, revisi sebelumnya) menanam lubang dorman
        // baru. Dibuktikan: `php -r 'var_dump(3134 > false);'` → bool(true).
        $posisiStack = strpos($layout, "@stack('styles')");
        $this->assertNotFalse($posisiStack, "Penanda \"@stack('styles')\" tidak ditemukan di layout.");

        $posisiMobileCss = strpos($layout, "Asset::versioned('css/mobile.css')");
        $this->assertNotFalse($posisiMobileCss, 'Link mobile.css tidak ditemukan di layout.');

        $this->assertGreaterThan(
            $posisiStack,
            $posisiMobileCss,
            'mobile.css WAJIB di-link SETELAH @stack(styles) — lihat komentar di layout.'
        );
    }

    public function test_layout_punya_scrim_dan_cabang_lebar_layar(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('mobile-drawer-scrim', $layout);

        // Cabang lebar layar: di ponsel hamburger menggerakkan drawer
        // (toggle), BUKAN menulis localStorage sidebar_collapsed milik
        // desktop. Assertion longgar sebelumnya HAMPA di dua sisi:
        // 'mobile-drawer-open' polos juga muncul di tutupDrawerPonsel()
        // (classList.remove, bukan toggle — menghapus cabang toggle tetap
        // lulus), dan 'max-width: 768px' polos sudah ada sejak sebelum
        // branch ini di blok <style> sidebar-collapse desktop (dibuktikan:
        // `git show c2db3358:resources/views/layouts/app.blade.php | grep -c
        // "max-width: 768px"` → 1) — menghapus SELURUH cabang kueriPonsel
        // tetap lulus kedua assertion lama.
        //
        // Dipersempit: matchMedia('(max-width: 768px)') hanya muncul SEKALI
        // di berkas ini — di deklarasi kueriPonsel — jadi ini memastikan
        // breakpoint JS-nya sendiri, bukan breakpoint CSS mana pun yang
        // kebetulan cocok.
        $this->assertStringContainsString("matchMedia('(max-width: 768px)')", $layout);

        // Dan posisi "if (kueriPonsel.matches)" WAJIB diikuti toggle
        // 'mobile-drawer-open' dalam jarak dekat (badan if yang sama) —
        // memastikan toggle itu benar-benar terjadi DI DALAM cabang ponsel,
        // bukan sekadar hadir di suatu tempat lain di berkas.
        $posisiCabang = strpos($layout, 'if (kueriPonsel.matches)');
        $this->assertNotFalse($posisiCabang, 'Cabang "if (kueriPonsel.matches)" tidak ditemukan.');

        $badanCabang = substr($layout, $posisiCabang, 200);
        $this->assertStringContainsString(
            "classList.toggle('mobile-drawer-open')",
            $badanCabang,
            'Toggle drawer ponsel tidak ditemukan di dalam badan cabang "if (kueriPonsel.matches)".'
        );
    }

    public function test_drawer_dan_konten_diatur_di_css(): void
    {
        $css = $this->mobileCss();

        // Sidebar disembunyikan dengan transform (bukan display:none) supaya
        // bisa dianimasikan menggeser masuk. Dipersempit ke BADAN aturan
        // lewat cssRuleBody(): assertStringContainsString('.sidebar-owner', ...)
        // mentah HAMPA karena selector itu juga disebut di KOMENTAR mobile.css
        // (mis. baris ~102), jadi menghapus seluruh aturan drawer tetap lulus.
        $badanSidebar = $this->cssRuleBody($css, 'body.owner-layout .sidebar-owner {');
        $this->assertStringContainsString('translateX(-100%)', $badanSidebar);

        // Konten mengambil lebar penuh — inilah yang mengembalikan 72px yang
        // dicuri sidebar. Sama-sama dipersempit ke badan aturan.
        $badanKonten = $this->cssRuleBody($css, 'body.owner-layout .content {');
        $this->assertStringContainsString('margin-left: 0', $badanKonten);
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

    public function test_uraian_spp_dibawa_ke_modal_perjalanan(): void
    {
        // Uraian SPP sengaja TIDAK dirender di badan kartu (terlalu panjang,
        // akan mendominasi) — dibawa ke modal lewat atribut data-uraian.
        $uraian = 'Pembayaran jasa pemeliharaan instalasi listrik pabrik periode Juli 2026';

        Dokumen::create([
            'nomor_agenda'    => 'MOB005_2026',
            'uraian_spp'      => $uraian,
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

        // Kartu ponsel membawa uraian sebagai atribut.
        $posisiKartu = strpos($html, 'class="mob-card"');
        $this->assertNotFalse($posisiKartu, 'Kartu mobile tidak ditemukan.');
        $this->assertStringContainsString(
            'data-uraian="' . $uraian . '"',
            substr($html, $posisiKartu, 2000),
            'Kartu ponsel tidak membawa data-uraian.'
        );

        // Sel tabel desktop WAJIB membawanya juga: modalnya SATU dan dipakai
        // bersama. Tanpa ini, pengguna desktop membuka modal yang sama dan
        // bagian Uraian SPP-nya kosong.
        $posisiSel = strpos($html, 'class="dj-cell');
        $this->assertNotFalse($posisiSel, 'Sel perjalanan desktop tidak ditemukan.');
        $this->assertStringContainsString(
            'data-uraian=',
            substr($html, $posisiSel, 1200),
            'Sel tabel desktop tidak membawa data-uraian — modal bersama akan kosong di desktop.'
        );

        // Markup penampung di modal + pengisian lewat textContent (bukan
        // innerHTML): uraian adalah teks bebas dari database.
        $this->assertStringContainsString('id="perjalananUraianTeks"', $html);
        $this->assertStringContainsString('teksUraian.textContent = uraian', $html);

        // Uraian kosong menyembunyikan seluruh bagian, bukan menampilkan strip.
        $this->assertStringContainsString("uraian === '' ? 'none' : 'block'", $html);
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
        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->assertSeeInOrder(['.mob-cards { display: none; }', 'class="mob-card"'], false)
            ->getContent();

        // Blok <style> milik kartu WAJIB ditutup sebelum markup kartu dimulai.
        // Kalau tidak, browser menelan seluruh markup kartu ke dalam <style> dan
        // halaman ponsel tampil KOSONG.
        $posisiStyle  = strpos($html, '.mob-cards { display: none; }');
        $posisiMarkup = strpos($html, 'class="mob-card"');
        $this->assertNotFalse($posisiStyle, 'Blok <style> kartu tidak ditemukan.');
        $this->assertNotFalse($posisiMarkup, 'Markup kartu tidak ditemukan.');

        $antara = substr($html, $posisiStyle, $posisiMarkup - $posisiStyle);
        $this->assertStringContainsString(
            '</style>',
            $antara,
            'Blok <style> kartu tidak ditutup sebelum markup — markup akan tertelan ke dalam <style> dan halaman ponsel tampil kosong.'
        );
    }

    public function test_aturan_kartu_benar_benar_aktif_bukan_sekadar_ada(): void
    {
        // Blok <style> kartu boleh memuat TEPAT SATU tag penutup style, yaitu
        // penutup blok itu sendiri. Menulisnya lagi di dalam komentar membuat
        // parser HTML mengakhiri blok LEBIH AWAL, sehingga aturan di bawah
        // komentar (".mob-cards { display: none; }") tak pernah aktif — kartu
        // ikut tampil di DESKTOP, dobel dengan tabel. Lolos ke produksi
        // 2026-08-13; assertion urutan & keberadaan string tetap hijau karena
        // teksnya memang ada, cuma tak pernah berlaku sebagai CSS.
        $partial = file_get_contents(
            resource_path('views/bagian/partials/_kartuDokumenMobile.blade.php')
        );

        $penutupStyle = substr_count($partial, '</' . 'style>');
        $this->assertSame(
            1,
            $penutupStyle,
            "Ditemukan {$penutupStyle} tag penutup style di partial kartu — harus tepat 1. "
            . 'Menulisnya di dalam komentar memotong blok lebih awal sehingga aturan '
            . 'display:none tak pernah aktif dan kartu tampil di desktop.'
        );
    }

    public function test_direktif_push_partial_kartu_seimbang(): void
    {
        // Penjaga akar masalah insiden 2026-08-13: komentar CSS di partial memuat
        // nama direktif push secara LITERAL, dan Blade memproses direktif di dalam
        // komentar CSS juga. Akibatnya stack ketiga terbuka di tengah blok tanpa
        // penutup — </style> jatuh ke push yang keliru, seluruh markup kartu
        // tertelan ke dalam <style>, dan halaman ponsel tampil KOSONG di produksi.
        //
        // Diuji di SUMBER, bukan HTML hasil render: begitu jumlahnya timpang,
        // render sudah rusak sedemikian rupa sehingga assertion atas HTML jadi
        // sulit dibaca (assertSeeInOrder gagal lebih dulu dengan pesan yang
        // menyesatkan). Menghitung direktif menunjuk langsung ke penyebabnya.
        $partial = file_get_contents(
            resource_path('views/bagian/partials/_kartuDokumenMobile.blade.php')
        );

        $jumlahPush    = preg_match_all('/@push\(/', $partial);
        $jumlahEndpush = preg_match_all('/@endpush/', $partial);

        $this->assertSame(
            $jumlahPush,
            $jumlahEndpush,
            "Direktif push/endpush timpang ({$jumlahPush} push vs {$jumlahEndpush} endpush) — "
            . 'biasanya karena nama direktif ditulis literal di dalam komentar; '
            . 'Blade memprosesnya sebagai direktif sungguhan.'
        );
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

        $this->assertStringContainsString("document.addEventListener('keydown'", $html);
        $this->assertStringContainsString(".closest('.mob-card[data-perjalanan]')", $html);
        $this->assertStringContainsString('tampilkanPerjalanan(kartu)', $html);

        // Handler WAJIB jalan langsung (IIFE), BUKAN dibungkus DOMContentLoaded.
        // Partial ini di-push ke stack scripts yang dirender di AKHIR <body>,
        // jadi saat skripnya dieksekusi DOMContentLoaded SUDAH menyala dan
        // callback-nya tak akan pernah dipanggil — listener senyap tak terpasang
        // dan Enter/Space mati tanpa satu pun error di konsol. Lolos ke produksi
        // 2026-08-13; assertion string di atas tetap hijau karena kodenya memang
        // ada, cuma tak pernah dijalankan.
        $partial = file_get_contents(
            resource_path('views/bagian/partials/_kartuDokumenMobile.blade.php')
        );

        $this->assertStringNotContainsString(
            "document.addEventListener('DOMContentLoaded'",
            $partial,
            'Handler keyboard kartu tidak boleh dibungkus DOMContentLoaded — event itu sudah lewat saat stack scripts dirender, sehingga listener tak pernah terpasang.'
        );

        // Listener WAJIB di document, BUKAN di .mob-cards. Menyasar pembungkus
        // membuat handler bergantung pada markup sudah ada saat skrip jalan —
        // dan di halaman ini skrip menang duluan, sehingga
        // "if (!pembungkus) return" menendang keluar diam-diam dan Enter/Space
        // mati tanpa error. Terbukti di produksi 2026-08-13.
        $this->assertStringNotContainsString(
            "pembungkus.addEventListener('keydown'",
            $partial,
            'Listener keyboard tidak boleh dipasang di .mob-cards — pasang di document agar tidak bergantung urutan render stack scripts vs markup.'
        );
    }

    public function test_popup_filter_ponsel_memakai_form_yang_sama(): void
    {
        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        // Tombol mengambang, latar gelap, kepala popup, dan tombol Terapkan ada.
        $this->assertStringContainsString('data-filter-toggle', $html);
        $this->assertStringContainsString('data-filter-scrim', $html);
        $this->assertStringContainsString('filter-pop__terapkan', $html);

        // TEPAT SATU form filter. Menyalin blok filter jadi popup terpisah akan
        // melahirkan dua <form> dengan nama field identik di satu halaman —
        // keduanya mengirim "search"/"tahun"/"bulan"/"status" dan saling
        // menimpa saat submit. Popup adalah elemen .search-box yang SAMA,
        // hanya diubah jadi fixed lewat CSS.
        $this->assertSame(
            1,
            substr_count($html, 'class="search-filter-form"'),
            'Ditemukan lebih dari satu form filter — popup harus memakai form yang sama, bukan salinan.'
        );

        // Tombol Terapkan WAJIB type="submit": form GET yang sama mengirim
        // sendiri, nol JS. Kalau jadi type="button", tombolnya tak berbuat apa pun.
        $posisiTerapkan = strpos($html, 'filter-pop__terapkan');
        $this->assertNotFalse($posisiTerapkan);
        $this->assertStringContainsString(
            'type="submit"',
            substr($html, $posisiTerapkan - 120, 140),
            'Tombol Terapkan harus type="submit".'
        );
    }

    public function test_refresh_dan_uji_whatsapp_disembunyikan_dari_popup(): void
    {
        // Keputusan user 2026-08-13: popup filter untuk mencari & menyaring;
        // Refresh dan Uji Kirim Pesan bukan filter dan hanya menambah tingginya.
        //
        // DISEMBUNYIKAN lewat CSS, BUKAN dihapus dari markup. Keduanya wajib
        // tetap ada di HTML: partial bagian.partials._ujiWhatsApp mengikat
        // tombolnya lewat id="btnUjiWhatsApp", dan UjiWhatsAppBagianTest
        // menuntut tag <button> itu hadir lengkap dengan kelas .btn-refresh.
        // Menghapusnya dari Blade akan mematikan fitur uji WhatsApp sekaligus
        // memerahkan test tersebut.
        $css = $this->mobileCss();

        $this->assertStringContainsString(
            'display: none !important',
            $this->cssRuleBody($css, 'body.bagian-layout .search-box .btn-refresh'),
            'Refresh & Uji Kirim Pesan harus disembunyikan dari popup filter di ponsel.'
        );

        // Markupnya WAJIB tetap ada — inilah yang membedakan "disembunyikan"
        // dari "dihapus".
        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="btnRefreshTable"', $html);
        $this->assertStringContainsString('id="btnUjiWhatsApp"', $html);
    }

    public function test_popup_filter_menyusun_kepala_di_atas_form(): void
    {
        // Kepala popup (judul + tombol tutup) adalah SAUDARA dari <form>, bukan
        // anaknya. Tanpa flex-direction: column pada .search-box, keduanya
        // berjajar menyamping: judul terdorong jadi kolom kiri, bukan baris di
        // atas — terlihat saat QA produksi 2026-08-13.
        $css = $this->mobileCss();

        $posisi = strpos($css, 'body.bagian-layout .search-box {');
        $this->assertNotFalse($posisi, 'Aturan popup .search-box tidak ditemukan.');

        $akhir = strpos($css, '}', $posisi);
        $badan = substr($css, $posisi, $akhir - $posisi);

        $this->assertStringContainsString('flex-direction: column', $badan);
        $this->assertStringContainsString('position: fixed', $badan);
    }

    public function test_auto_submit_dropdown_dipulihkan_saat_desktop(): void
    {
        // Auto-submit dimatikan HANYA di ponsel (di popup ia menutup popup tiap
        // satu filter dipilih). Atribut aslinya dipindah ke data-onchange, BUKAN
        // dibuang, supaya bisa dipulihkan persis saat layar melebar ke desktop —
        // tanpa itu pengguna yang memutar tablet kehilangan auto-submit permanen
        // sampai memuat ulang halaman.
        $html = $this->actingAs($this->userBagian())
            ->get('/bagian/documents')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString("s.setAttribute('data-onchange', s.getAttribute('onchange'))", $html);
        $this->assertStringContainsString("s.setAttribute('onchange', s.getAttribute('data-onchange'))", $html);

        // Popup tak boleh tertinggal terbuka saat layar melebar ke desktop.
        $this->assertStringContainsString('if (!ponsel) setBuka(false)', $html);
    }

    public function test_input_filter_mencegah_zoom_ios(): void
    {
        $css = $this->mobileCss();

        $this->assertStringContainsString('.search-filter-form', $css);

        // font-size minimal 16px pada input mencegah iOS auto-zoom saat field
        // difokus — penyebab keluhan "layarnya meloncat sendiri". Dipersempit
        // ke badan aturan ".search-filter-form input/select, .search-box
        // .form-control, .search-box .input-group-text": Blok B kartu (Task 3)
        // sudah punya deklarasi "font-size: 16px" sendiri (.mob-card__nilai)
        // sehingga assertStringContainsString mentah di seluruh berkas lolos
        // terus, tak peduli nilai font-size input sebenarnya — terbukti hampa
        // saat dicoba mutasi jadi 14px. Selektor ".search-box .form-control" +
        // "!important" WAJIB ada di badan aturan ini: partial GLOBAL
        // compact-document-ui.blade.php baris ~899-919, di dalam
        // @media (max-width: 1400px) miliknya sendiri, mengunci
        // ".search-box .form-control" ke "font-size: 0.74rem !important"
        // — breakpoint 1400px itu tetap aktif di viewport ponsel (375px < 1400px)
        // sehingga tanpa selektor+!important yang menyamai di sini, input
        // terukur ~11.84px di produksi, bukan 16px.
        $badanAturan = $this->cssRuleBody($css, 'body.bagian-layout .search-filter-form input');
        $this->assertStringContainsString('.search-box .form-control', $badanAturan);
        $this->assertStringContainsString('font-size: 16px !important', $badanAturan);

        // .search-box .input-group-text (kotak ikon kaca pembesar) WAJIB ikut
        // aturan yang sama: dikunci partial global yang SAMA (baris ~899-919)
        // ke height: 34px !important — tinggi EKSPLISIT yang tak tertolong
        // align-items: stretch bawaan Bootstrap (stretch hanya berlaku untuk
        // anak ber-tinggi auto). Tanpa selektor ini, input jadi 44px tapi
        // kotak ikon tetap 34px — pincang 10px, terukur nyata di produksi.
        $this->assertStringContainsString('.search-box .input-group-text', $badanAturan);
    }

    public function test_target_sentuh_minimal_44px(): void
    {
        $css = $this->mobileCss();

        // 44px = ambang target sentuh Apple HIG. "min-height: 44px" muncul di
        // BEBERAPA aturan berbeda (input/select filter, tombol Terapkan,
        // .page-link paginasi) — assertion mentah di seluruh berkas hanya
        // butuh SATU di antaranya benar untuk lolos, jadi memutasi salah satu
        // aturan saja tetap hijau selama yang lain utuh. Diperiksa per-aturan
        // supaya regresi di aturan mana pun tertangkap sendiri-sendiri.
        //
        // Aturan input/select WAJIB "!important": compact-document-ui.blade.php
        // ~899-919 (@media max-width: 1400px, selalu aktif di ponsel) mengunci
        // ".search-box .form-control" ke "min-height: 34px !important".
        $this->assertStringContainsString(
            'min-height: 44px !important',
            $this->cssRuleBody($css, 'body.bagian-layout .search-filter-form input')
        );

        // Tombol Terapkan menggantikan Refresh sebagai satu-satunya tombol yang
        // TAMPIL di popup (Refresh & Uji Kirim Pesan disembunyikan 2026-08-13),
        // jadi dialah yang kini wajib memenuhi ambang sentuh. !important-nya
        // beralasan sama: partial global mengunci tinggi tombol lewat !important.
        $this->assertStringContainsString(
            'min-height: 44px !important',
            $this->cssRuleBody($css, 'body.bagian-layout .filter-pop__terapkan')
        );

        $this->assertStringContainsString(
            'min-height: 44px',
            $this->cssRuleBody($css, 'body.bagian-layout .pagination-container .page-link')
        );
    }
}
