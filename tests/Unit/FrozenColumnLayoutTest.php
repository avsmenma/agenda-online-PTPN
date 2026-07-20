<?php

namespace Tests\Unit;

use App\Support\FrozenColumnLayout;
use PHPUnit\Framework\TestCase;

class FrozenColumnLayoutTest extends TestCase
{
    /** @var array<string,string> peta key => label, meniru $availableColumns */
    private array $available = [
        'nomor_agenda' => 'Nomor Agenda',
        'no_spp'       => 'No SPP',
        'nilai_rupiah' => 'Nilai Rupiah',
        'bulan'        => 'Bulan',
        'tahun'        => 'Tahun',
    ];

    public function test_menerima_pilihan_beku_yang_sah(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda'],
            ['nilai_rupiah'],
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame(['nilai_rupiah'], $hasil['right']);
    }

    public function test_membuang_kolom_yang_tidak_ditampilkan(): void
    {
        // 'nilai_rupiah' dikenal tapi tidak dicentang user -> tidak boleh beku.
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nilai_rupiah'],
            [],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_membuang_key_yang_tidak_dikenal(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'kolom_karangan'],
            [],
            ['nomor_agenda', 'kolom_karangan'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_membuang_duplikat(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_kiri_menang_bila_key_ada_di_kedua_sisi(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['no_spp'],
            ['no_spp'],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['no_spp'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_membuang_nilai_kosong_dan_bukan_teks(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            ['', '   ', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    public function test_membuang_masukan_yang_bukan_teks(): void
    {
        $hasil = FrozenColumnLayout::normalize(
            [['array'], null, 123, 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $hasil['left']);
    }

    /** Contoh persis dari spec §4. */
    public function test_urutan_render_memindahkan_kolom_beku_ke_tepi(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah', 'bulan'],
            ['nilai_rupiah'],
            ['no_spp']
        );

        $this->assertSame(
            ['nilai_rupiah', 'nomor_agenda', 'bulan', 'no_spp'],
            $urutan
        );
    }

    public function test_urutan_dalam_kelompok_mengikuti_urutan_pilihan(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            ['nilai_rupiah', 'nomor_agenda'],
            []
        );

        // Meski 'nilai_rupiah' disebut lebih dulu di daftar beku,
        // urutannya tetap mengikuti urutan pilihan user.
        $this->assertSame(
            ['nomor_agenda', 'nilai_rupiah', 'no_spp'],
            $urutan
        );
    }

    public function test_tanpa_beku_urutan_tidak_berubah(): void
    {
        $urutan = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'bulan'],
            [],
            []
        );

        $this->assertSame(['nomor_agenda', 'no_spp', 'bulan'], $urutan);
    }
}
