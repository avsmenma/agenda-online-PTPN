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
        return view('partials._infoCards', ['cards' => $cards])->render();
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
}
