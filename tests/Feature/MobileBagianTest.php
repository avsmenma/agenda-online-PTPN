<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga kontrak tampilan ponsel role Bagian.
 *
 * Test paling penting di berkas ini adalah
 * test_seluruh_aturan_mobile_terkurung_media_query — ia menegakkan janji
 * "nol perubahan desktop" secara mekanis, bukan sekadar niat baik.
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

        // Buang setiap blok @media beserta isinya (kurung bersarang 1 tingkat).
        $diLuarMedia = preg_replace('#@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}#s', '', $tanpaKomentar);

        // Yang tersisa harus tak punya deklarasi CSS sama sekali.
        $this->assertStringNotContainsString(
            '{',
            trim($diLuarMedia),
            'Ada aturan CSS di LUAR @media — ini akan mengubah tampilan desktop.'
        );
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
