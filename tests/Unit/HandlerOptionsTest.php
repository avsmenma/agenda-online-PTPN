<?php

namespace Tests\Unit;

use App\Models\Bagian;
use App\Support\HandlerOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji App\Support\HandlerOptions — sumber tunggal opsi dropdown "Pengurus
 * Dokumen" untuk 4 role Tabulator, menggantikan 4 salinan build*HandlerOptions().
 *
 * Aturan yang dijaga: optgroup Bagian hanya boleh berisi bagian MILIK dokumen itu
 * sendiri, supaya salah pilih bagian saat pengembalian tidak mungkin terjadi.
 */
class HandlerOptionsTest extends TestCase
{
    use RefreshDatabase;

    private const OPSI_PERAN = [
        ['value' => 'operator',        'label' => 'Operator'],
        ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
        ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
        ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
        ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
    ];

    public function test_hanya_bagian_milik_dokumen_yang_ditawarkan(): void
    {
        Bagian::create(['kode' => 'DPM', 'nama' => 'DPM']);
        Bagian::create(['kode' => 'SDM', 'nama' => 'SDM']);
        Bagian::create(['kode' => 'TEP', 'nama' => 'TEP']);

        $opsi = HandlerOptions::forDokumen('SDM', HandlerOptions::bagianMap());

        $this->assertSame(array_merge(self::OPSI_PERAN, [[
            'optgroup' => 'Bagian',
            'options'  => [['value' => 'bagian_sdm', 'label' => 'SDM']],
        ]]), $opsi);
    }

    public function test_bagian_kosong_tidak_menghasilkan_optgroup(): void
    {
        Bagian::create(['kode' => 'DPM', 'nama' => 'DPM']);

        $peta = HandlerOptions::bagianMap();

        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen(null, $peta));
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('', $peta));
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('   ', $peta));
    }

    public function test_bagian_tak_dikenal_tidak_menghasilkan_optgroup(): void
    {
        Bagian::create(['kode' => 'DPM', 'nama' => 'DPM']);

        $opsi = HandlerOptions::forDokumen('BAGIAN_ANTAH_BERANTAH', HandlerOptions::bagianMap());

        $this->assertSame(self::OPSI_PERAN, $opsi);
    }

    public function test_pencocokan_abai_besar_kecil_huruf_dan_spasi(): void
    {
        Bagian::create(['kode' => 'PTI', 'nama' => 'PTI']);

        $peta = HandlerOptions::bagianMap();
        $harapan = ['value' => 'bagian_pti', 'label' => 'PTI'];

        foreach (['pti', 'Pti', '  PTI  '] as $tulisan) {
            $opsi = HandlerOptions::forDokumen($tulisan, $peta);
            $this->assertSame($harapan, $opsi[5]['options'][0], "Gagal untuk '{$tulisan}'");
        }
    }

    public function test_peta_mengenali_nama_bagian_bukan_hanya_kode(): void
    {
        // Baris lama bisa menyimpan nama bagian, bukan kodenya.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('Akuntansi', HandlerOptions::bagianMap());

        $this->assertSame(
            ['value' => 'bagian_akn', 'label' => 'Akuntansi'],
            $opsi[5]['options'][0]
        );
    }

    public function test_bagian_nonaktif_tidak_masuk_peta(): void
    {
        Bagian::create(['kode' => 'LAMA', 'nama' => 'Bagian Lama', 'is_active' => false]);

        $peta = HandlerOptions::bagianMap();

        $this->assertArrayNotHasKey('LAMA', $peta);
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('LAMA', $peta));
    }
}
