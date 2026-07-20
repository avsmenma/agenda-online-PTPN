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
        $result = FrozenColumnLayout::normalize(
            ['nomor_agenda'],
            ['nilai_rupiah'],
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame(['nilai_rupiah'], $result['right']);
    }

    public function test_membuang_kolom_yang_tidak_ditampilkan(): void
    {
        // 'nilai_rupiah' dikenal tapi tidak dicentang user -> tidak boleh beku.
        $result = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nilai_rupiah'],
            [],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    public function test_membuang_key_yang_tidak_dikenal(): void
    {
        $result = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'kolom_karangan'],
            [],
            ['nomor_agenda', 'kolom_karangan'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    /** Sisi kanan wajib disaring sekeras sisi kiri. */
    public function test_membuang_key_tidak_sah_di_sisi_kanan(): void
    {
        $result = FrozenColumnLayout::normalize(
            [],
            ['kolom_karangan', 'nilai_rupiah', 'no_spp'],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        // 'kolom_karangan' tidak dikenal, 'nilai_rupiah' dikenal tapi tidak
        // ditampilkan -> hanya 'no_spp' yang boleh beku di kanan.
        $this->assertSame([], $result['left']);
        $this->assertSame(['no_spp'], $result['right']);
    }

    public function test_membuang_duplikat(): void
    {
        $result = FrozenColumnLayout::normalize(
            ['nomor_agenda', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    public function test_kiri_menang_bila_key_ada_di_kedua_sisi(): void
    {
        $result = FrozenColumnLayout::normalize(
            ['no_spp'],
            ['no_spp'],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['no_spp'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    /**
     * Setelah key yang bentrok dibuang dari sisi kanan, kunci array WAJIB
     * diurutkan ulang dari 0. Tanpa itu hasilnya ter-encode JSON menjadi
     * objek {"1":"no_spp"}, bukan array.
     */
    public function test_kunci_sisi_kanan_diurutkan_ulang_setelah_membuang(): void
    {
        $result = FrozenColumnLayout::normalize(
            ['nomor_agenda'],
            ['nomor_agenda', 'no_spp'],
            ['nomor_agenda', 'no_spp'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame(['no_spp'], $result['right']);
    }

    public function test_membuang_nilai_kosong(): void
    {
        $result = FrozenColumnLayout::normalize(
            ['', '   ', 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    public function test_membuang_masukan_yang_bukan_teks(): void
    {
        $result = FrozenColumnLayout::normalize(
            [['array'], null, 123, 'nomor_agenda'],
            [],
            ['nomor_agenda'],
            $this->available
        );

        $this->assertSame(['nomor_agenda'], $result['left']);
        $this->assertSame([], $result['right']);
    }

    /** Contoh persis dari spec §4. */
    public function test_urutan_render_memindahkan_kolom_beku_ke_tepi(): void
    {
        $order = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah', 'bulan'],
            ['nilai_rupiah'],
            ['no_spp']
        );

        $this->assertSame(
            ['nilai_rupiah', 'nomor_agenda', 'bulan', 'no_spp'],
            $order
        );
    }

    public function test_urutan_dalam_kelompok_mengikuti_urutan_pilihan(): void
    {
        $order = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'nilai_rupiah'],
            ['nilai_rupiah', 'nomor_agenda'],
            []
        );

        // Meski 'nilai_rupiah' disebut lebih dulu di daftar beku,
        // urutannya tetap mengikuti urutan pilihan user.
        $this->assertSame(
            ['nomor_agenda', 'nilai_rupiah', 'no_spp'],
            $order
        );
    }

    public function test_tanpa_beku_urutan_tidak_berubah(): void
    {
        $order = FrozenColumnLayout::renderOrder(
            ['nomor_agenda', 'no_spp', 'bulan'],
            [],
            []
        );

        $this->assertSame(['nomor_agenda', 'no_spp', 'bulan'], $order);
    }
}
