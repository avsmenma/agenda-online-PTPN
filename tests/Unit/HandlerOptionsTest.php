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

    private const OPSI_TANPA_MUNDUR = [
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

        $opsi = HandlerOptions::forDokumen('SDM', HandlerOptions::bagianMap(), 'operator', null);

        $this->assertSame(array_merge(self::OPSI_PERAN, [[
            'optgroup' => 'Bagian',
            'options'  => [['value' => 'bagian_sdm', 'label' => 'SDM']],
        ]]), $opsi);
    }

    public function test_bagian_kosong_tidak_menghasilkan_optgroup(): void
    {
        Bagian::create(['kode' => 'DPM', 'nama' => 'DPM']);

        $peta = HandlerOptions::bagianMap();

        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen(null, $peta, 'operator', null));
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('', $peta, 'operator', null));
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('   ', $peta, 'operator', null));
    }

    public function test_bagian_tak_dikenal_tidak_menghasilkan_optgroup(): void
    {
        Bagian::create(['kode' => 'DPM', 'nama' => 'DPM']);

        $opsi = HandlerOptions::forDokumen('BAGIAN_ANTAH_BERANTAH', HandlerOptions::bagianMap(), 'operator', null);

        $this->assertSame(self::OPSI_PERAN, $opsi);
    }

    public function test_pencocokan_abai_besar_kecil_huruf_dan_spasi(): void
    {
        Bagian::create(['kode' => 'PTI', 'nama' => 'PTI']);

        $peta = HandlerOptions::bagianMap();
        $harapan = ['value' => 'bagian_pti', 'label' => 'PTI'];

        foreach (['pti', 'Pti', '  PTI  '] as $tulisan) {
            $opsi = HandlerOptions::forDokumen($tulisan, $peta, 'operator', null);
            $this->assertSame($harapan, $opsi[5]['options'][0], "Gagal untuk '{$tulisan}'");
        }
    }

    public function test_peta_mengenali_nama_bagian_bukan_hanya_kode(): void
    {
        // Baris lama bisa menyimpan nama bagian, bukan kodenya.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('Akuntansi', HandlerOptions::bagianMap(), 'operator', null);

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
        $this->assertSame(self::OPSI_PERAN, HandlerOptions::forDokumen('LAMA', $peta, 'operator', null));
    }

    public function test_tiga_role_tak_boleh_menunjuk_operator_maupun_bagian(): void
    {
        foreach (['perpajakan', 'akutansi', 'pembayaran'] as $role) {
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($role, 'operator'),
                "Role {$role} masih boleh menunjuk Operator"
            );
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($role, 'bagian_akn'),
                "Role {$role} masih boleh menunjuk Bagian"
            );
        }
    }

    public function test_role_terlarang_tetap_boleh_menunjuk_peran_lain(): void
    {
        foreach (['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'] as $target) {
            $this->assertTrue(
                HandlerOptions::bolehMenunjuk('perpajakan', $target),
                "Perpajakan seharusnya masih boleh menunjuk {$target}"
            );
        }
    }

    public function test_operator_dan_verifikasi_tidak_ikut_dilarang(): void
    {
        foreach (['operator', 'team_verifikasi'] as $role) {
            $this->assertTrue(HandlerOptions::bolehMenunjuk($role, 'operator'), $role);
            $this->assertTrue(HandlerOptions::bolehMenunjuk($role, 'bagian_akn'), $role);
        }
    }

    public function test_alias_peran_dan_target_ikut_dinormalisasi(): void
    {
        // Role::ALIASES memuat 'akuntansi' => 'akutansi'. Tanpa normalisasi, akun
        // yang kolom role-nya berisi alias LOLOS dari larangan tanpa suara.
        foreach (['akuntansi', 'Akutansi', ' PEMBAYARAN ', 'Tim Perpajakan'] as $alias) {
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($alias, 'operator'),
                "Alias peran '{$alias}' lolos dari larangan"
            );
        }

        // Sisi target juga: 'Operator' berhuruf besar harus tetap tertangkap.
        $this->assertFalse(HandlerOptions::bolehMenunjuk('perpajakan', 'Operator'));
    }

    public function test_peran_kosong_tidak_dilarang(): void
    {
        // null/'' = permintaan tanpa sesi. Bukan anggota daftar, jadi tak dilarang.
        $this->assertTrue(HandlerOptions::bolehMenunjuk(null, 'operator'));
        $this->assertTrue(HandlerOptions::bolehMenunjuk('', 'operator'));
    }

    public function test_tiga_role_kehilangan_operator_dan_optgroup_bagian(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);
        $peta = HandlerOptions::bagianMap();

        foreach (['perpajakan', 'akutansi', 'pembayaran'] as $role) {
            $this->assertSame(
                self::OPSI_TANPA_MUNDUR,
                HandlerOptions::forDokumen('AKN', $peta, $role, null),
                "Role {$role} masih menawarkan jalur mundur"
            );
        }
    }

    public function test_operator_dan_verifikasi_tetap_menerima_daftar_penuh(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);
        $peta = HandlerOptions::bagianMap();
        $penuh = array_merge(self::OPSI_PERAN, [[
            'optgroup' => 'Bagian',
            'options'  => [['value' => 'bagian_akn', 'label' => 'Akuntansi']],
        ]]);

        foreach (['operator', 'team_verifikasi'] as $role) {
            $this->assertSame($penuh, HandlerOptions::forDokumen('AKN', $peta, $role, null), $role);
        }
    }

    public function test_opsi_operator_bertahan_nonaktif_bila_ia_pengurus_baris_itu(): void
    {
        // Perpajakan & akutansi melihat SELURUH dokumen, termasuk yang masih di
        // Operator. Kalau opsinya dibuang, <select> kehilangan nilai terpilih dan
        // browser jatuh ke opsi pertama — tabel akan menampilkan pengurus KELIRU.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'perpajakan', 'operator');

        $this->assertSame(
            ['value' => 'operator', 'label' => 'Operator', 'disabled' => true],
            $opsi[0]
        );
    }

    public function test_optgroup_bagian_bertahan_nonaktif_untuk_pembayaran(): void
    {
        // Pembayaran satu-satunya role yang melihat dokumen returned_to_bidang,
        // dan nilai tampilnya bagian_<kode> (DocumentRow::handlerTampilanMentah).
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'pembayaran', 'bagian_akn');
        $terakhir = end($opsi);

        $this->assertSame('Bagian', $terakhir['optgroup']);
        $this->assertSame(
            ['value' => 'bagian_akn', 'label' => 'Akuntansi', 'disabled' => true],
            $terakhir['options'][0]
        );
    }

    public function test_nilai_dipertahankan_tidak_menghidupkan_opsi_terlarang_lain(): void
    {
        // Yang dipertahankan HANYA nilai itu sendiri — bukan seluruh kelompok terlarang.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'perpajakan', 'operator');

        foreach ($opsi as $o) {
            $this->assertArrayNotHasKey('optgroup', $o, 'optgroup Bagian ikut terbawa');
        }
    }
}
