<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menguji larangan jalur mundur ke Operator & Bagian untuk perpajakan, akutansi,
 * dan pembayaran.
 *
 * Pemangkasan dropdown saja tidak cukup: opsi ditanam di data baris yang dikirim
 * ke klien, jadi bisa diakali lewat devtools. Penegakan sesungguhnya ada di
 * DocumentHandlerController::update().
 */
class LaranganJalurMundurTest extends TestCase
{
    public function test_formatter_handler_merender_opsi_nonaktif(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        // Assertion dipersempit ke BADAN fungsi: kata 'disabled' sudah muncul di
        // banyak tempat lain di berkas ini (atribut select, judul tooltip), jadi
        // pencarian ke seluruh berkas akan hampa.
        $this->assertSame(
            1,
            substr_count($js, 'function optionHtml(o) {'),
            'optionHtml tidak lagi tunggal — persempit ulang assertion ini'
        );

        $mulai = strpos($js, 'function optionHtml(o) {');
        $badan = substr($js, $mulai, 500);

        $this->assertStringContainsString(
            "(o.disabled ? ' disabled' : '')",
            $badan,
            'optionHtml tidak merender atribut disabled'
        );
    }
}
