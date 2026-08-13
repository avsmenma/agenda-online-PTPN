<?php

namespace Tests\Feature;

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
}
