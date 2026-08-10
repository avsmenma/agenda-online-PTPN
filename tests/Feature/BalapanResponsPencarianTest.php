<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga penjagaan balapan respons AJAX di mesin Tabulator bersama.
 *
 * Bug yang dijaga (direproduksi di produksi 2026-08-10, role operator, kata
 * kunci "5661"): tiap perubahan filter memanggil table.replaceData(), dan
 * Tabulator 6.3.1 tidak membatalkan permintaan sebelumnya. Kueri yang lebih
 * luas dijawab server lebih lambat daripada yang sempit (search=56 -> 2,4 dtk
 * / 671 baris; search=5661 -> 0,5 dtk / 4 baris), sehingga respons basi
 * mendarat belakangan dan menimpa hasil yang benar — kotak cari berisi "5661"
 * tetapi tabel menampilkan seluruh dokumen.
 *
 * Berdampak ke kelima role keuangan sekaligus karena berkas ini adalah mesin
 * bersama mereka.
 *
 * Tak ada test runner JS di project ini (lihat catatan sama di
 * ColumnCustomizationSharedTest), jadi test ini memeriksa bentuk sumber.
 */
class BalapanResponsPencarianTest extends TestCase
{
    private function sumberMesin(): string
    {
        return file_get_contents(public_path('js/document-tabulator.js'));
    }

    /**
     * Badan fungsi ajaxDenganPembatalan saja — assertion yang menyapu seluruh
     * berkas gampang hampa karena kata yang dicari bisa muncul di komentar atau
     * di fungsi lain.
     */
    private function badanFungsiPembatalan(string $js): string
    {
        $this->assertSame(
            1,
            substr_count($js, 'function ajaxDenganPembatalan('),
            'ajaxDenganPembatalan harus persis satu — batas badan fungsi jadi ambigu bila ganda'
        );

        $awal = strpos($js, 'function ajaxDenganPembatalan(');
        $this->assertNotFalse($awal, 'fungsi ajaxDenganPembatalan tidak ditemukan');

        $akhir = strpos($js, 'const table = new Tabulator(', $awal);
        $this->assertNotFalse($akhir, 'penutup badan ajaxDenganPembatalan tidak ditemukan');

        return substr($js, $awal, $akhir - $awal);
    }

    /**
     * Assertion paling menggigit: fungsinya boleh ada, tapi kalau tidak
     * DIPASANG ke konstruktor Tabulator, bug-nya hidup lagi utuh.
     */
    public function test_pembatalan_terpasang_ke_konstruktor_tabulator(): void
    {
        $js = $this->sumberMesin();

        $awal = strpos($js, 'const table = new Tabulator(');
        $this->assertNotFalse($awal, 'konstruktor Tabulator tidak ditemukan');
        $akhir = strpos($js, 'placeholder:', $awal);
        $this->assertNotFalse($akhir, 'batas akhir opsi konstruktor tidak ditemukan');

        $opsiKonstruktor = substr($js, $awal, $akhir - $awal);

        $this->assertStringContainsString(
            'ajaxRequestFunc: ajaxDenganPembatalan',
            $opsiKonstruktor,
            'ajaxRequestFunc tidak dipasang di konstruktor — Tabulator kembali memakai ajax bawaannya '
            . 'yang tak membatalkan permintaan tersalip, dan respons basi bisa menimpa hasil pencarian'
        );
    }

    public function test_permintaan_yang_tersalip_dibatalkan(): void
    {
        $badan = $this->badanFungsiPembatalan($this->sumberMesin());

        $this->assertStringContainsString(
            'permintaanBerjalan.abort()',
            $badan,
            'permintaan sebelumnya tidak dibatalkan — respons basi tetap mendarat dan menimpa hasil baru'
        );
    }

    /**
     * Permintaan yang kita batalkan sendiri TIDAK boleh ditolak ke Tabulator:
     * penolakan menyalakan dataLoadError -> showLoadError() memunculkan kotak
     * "Coba lagi" untuk pembatalan yang memang disengaja. Diverifikasi di
     * produksi bahwa promise menggantung benar-benar membungkam peristiwa itu.
     */
    public function test_pembatalan_disengaja_tidak_dijadikan_error(): void
    {
        $badan = $this->badanFungsiPembatalan($this->sumberMesin());

        $this->assertStringContainsString(
            "err.name === 'AbortError'",
            $badan,
            'pembatalan sengaja tidak dibedakan dari kegagalan jaringan sungguhan'
        );

        $this->assertStringContainsString(
            'return new Promise(function () {})',
            $badan,
            'pembatalan sengaja diteruskan ke Tabulator alih-alih dibungkam — kotak "Coba lagi" palsu muncul'
        );
    }
}
