<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menguji kontrak partial bersama partials._infoCards (dipakai kartu keuangan
 * dashboard.workflow DAN kartu informasi bagian). Partial murni presentasional,
 * tanpa akses DB — cukup render langsung dengan array $cards contoh.
 */
class InfoCardsPartialTest extends TestCase
{
    private function render(array $cards): string
    {
        $html = view('partials._infoCards', ['cards' => $cards])->render();

        // Buang blok <style> agar assertion penghitungan (substr_count) hanya
        // mengukur MARKUP kartu, bukan selector CSS yang bernama sama
        // (mis. .wd-card-value / .wd-card--active di dalam <style>).
        return preg_replace('/<style\b[^>]*>.*?<\/style>/s', '', $html);
    }

    public function test_href_jadi_anchor_tanpa_href_jadi_div(): void
    {
        $html = $this->render([
            ['label' => 'A', 'value' => 1000, 'sub' => 'sub a', 'icon' => '<svg id="ia"></svg>', 'iconBg' => '#f0f4ff', 'href' => '/go'],
            ['label' => 'B', 'value' => 5,    'sub' => 'sub b', 'icon' => '<svg id="ib"></svg>', 'iconBg' => '#ffffff'],
        ]);

        // Kartu 1 (ber-href) = <a href="/go">, kartu 2 tanpa href = <div> (nol anchor).
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringContainsString('href="/go"', $html);
        // Dua kartu terender.
        $this->assertSame(2, substr_count($html, 'wd-card-value'));
        // Jumlah kolom mengikuti jumlah kartu.
        $this->assertStringContainsString('wd-cards--cols-2', $html);
        // Angka diformat gaya Indonesia (titik ribuan).
        $this->assertStringContainsString('1.000', $html);
        // Ikon dirender mentah (tak ter-escape).
        $this->assertStringContainsString('<svg id="ia"></svg>', $html);
    }

    public function test_active_dan_value_color_default_vs_override(): void
    {
        $html = $this->render([
            ['label' => 'X', 'value' => 42, 'sub' => 's', 'icon' => '<svg></svg>', 'iconBg' => '#fff', 'href' => '/x', 'active' => true],
            ['label' => 'Y', 'value' => 7,  'sub' => 's', 'icon' => '<svg></svg>', 'iconBg' => '#fff', 'href' => '/y', 'valueColor' => '#10b981'],
        ]);

        // Kartu aktif dapat kelas sorotan; hanya satu kartu aktif.
        $this->assertSame(1, substr_count($html, 'wd-card--active'));
        // Kartu X pakai warna default; kartu Y override hijau.
        $this->assertStringContainsString('color:#1a2340', $html);
        $this->assertStringContainsString('color:#10b981', $html);
    }

    public function test_partial_membawa_blok_style_kartu(): void
    {
        // Guard regresi: CSS .wd-card* WAJIB ikut di partial (Task 2 memindahkannya
        // keluar dari workflow.blade.php). Tanpa guard ini, penghapusan blok <style>
        // lolos diam-diam — pernah terjadi sekali di riwayat task ini.
        $raw = view('partials._infoCards', ['cards' => [
            ['label' => 'A', 'value' => 1, 'sub' => 's', 'icon' => '<svg></svg>', 'iconBg' => '#fff'],
        ]])->render();

        $this->assertStringContainsString('<style', $raw);
        $this->assertStringContainsString('.wd-card {', $raw);
        $this->assertStringContainsString('.wd-card--active', $raw);
        $this->assertStringContainsString('@keyframes wdFadeUp', $raw);
    }
}
