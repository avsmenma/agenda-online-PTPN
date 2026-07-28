<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\ColumnCustomization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Kontrak resolusi preferensi kolom beku bersama (dipakai 5 role keuangan).
 */
class ColumnCustomizationTest extends TestCase
{
    use RefreshDatabase;
    /** Peta kolom tersedia yang dipakai semua kasus uji. */
    private function available(): array
    {
        return [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp'    => 'Nomor SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'keterangan'   => 'Keterangan',
        ];
    }

    private function baseOptions(array $overrides = []): array
    {
        return array_merge([
            'available'  => $this->available(),
            'selected'   => ['nomor_agenda', 'nomor_spp', 'nilai_rupiah'],
            'default'    => ['left' => ['nomor_agenda'], 'right' => []],
            'pinnedLeft' => ['nomor_agenda'],
            'prefKey'    => null,
            'sessionKey' => 'uji_frozen',
        ], $overrides);
    }

    public function test_tanpa_request_memakai_default(): void
    {
        $hasil = ColumnCustomization::resolveFrozen(Request::create('/'), null, $this->baseOptions());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_request_membekukan_kolom_kanan(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nilai_rupiah'],
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->baseOptions());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame(['nilai_rupiah'], $hasil['right']);
        // Urutan render: beku kiri -> bebas -> beku kanan.
        $this->assertSame(['nomor_agenda', 'nomor_spp', 'nilai_rupiah'], $hasil['render']);
    }

    /**
     * Inti keberadaan penanda frozen_config: "user melepas SEMUA kolom beku"
     * harus bisa dibedakan dari "request tak membawa konfigurasi beku".
     */
    public function test_melepas_semua_beku_tidak_dipulihkan_dari_preferensi(): void
    {
        session(['uji_frozen' => ['left' => ['nomor_spp'], 'right' => ['nilai_rupiah']]]);

        $request = Request::create('/', 'GET', ['frozen_config' => '1']);
        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->baseOptions());

        // Hanya kolom pinned yang tersisa; sisanya benar-benar lepas.
        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertSame([], $hasil['right']);
    }

    public function test_kolom_pinned_dipaksa_masuk_kiri_meski_diminta_kanan(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => [],
            'frozen_right'  => ['nomor_agenda'],
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->baseOptions());

        $this->assertSame(['nomor_agenda'], $hasil['left']);
        $this->assertNotContains('nomor_agenda', $hasil['right']);
    }

    public function test_kolom_beku_yang_disembunyikan_ikut_lepas(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['keterangan'], // tidak ada di 'selected'
        ]);

        $hasil = ColumnCustomization::resolveFrozen($request, null, $this->baseOptions());

        $this->assertSame([], $hasil['right']);
    }

    public function test_hasil_disimpan_ke_sesi_saat_request_membawa_konfigurasi(): void
    {
        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nilai_rupiah'],
        ]);

        ColumnCustomization::resolveFrozen($request, null, $this->baseOptions());

        $this->assertSame(
            ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']],
            session('uji_frozen')
        );
    }

    public function test_preferensi_sesi_dipakai_saat_request_kosong(): void
    {
        session(['uji_frozen' => ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']]]);

        $hasil = ColumnCustomization::resolveFrozen(Request::create('/'), null, $this->baseOptions());

        $this->assertSame(['nilai_rupiah'], $hasil['right']);
    }

    /**
     * Jalur penyimpanan DB: saat request membawa frozen_config, preferensi
     * disimpan ke table_columns_preferences user jika user authenticated
     * dan prefKey diisi.
     */
    public function test_preferensi_disimpan_ke_db_saat_request_membawa_konfigurasi(): void
    {
        $user = User::factory()->create(['role' => 'akutansi']);

        $request = Request::create('/', 'GET', [
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nilai_rupiah'],
        ]);

        ColumnCustomization::resolveFrozen($request, $user, $this->baseOptions([
            'prefKey' => 'akutansi_frozen',
        ]));

        // Refresh user dari DB dan periksa preferensi tersimpan.
        $user->refresh();
        $this->assertSame(
            ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']],
            $user->table_columns_preferences['akutansi_frozen']
        );
    }

    /**
     * Urutan baca: DB menang atas sesi. Jika user authenticated dengan prefKey
     * diisi dan preference di DB ada, gunakan itu (bukan fallback sesi).
     */
    public function test_preferensi_db_menang_atas_sesi_saat_request_kosong(): void
    {
        $user = User::factory()->create(['role' => 'perpajakan']);
        // Set preferensi DB ke nilai A.
        $user->table_columns_preferences = [
            'perpajakan_frozen' => ['left' => ['nomor_agenda'], 'right' => ['nomor_spp']],
        ];
        $user->save();

        // Set sesi ke nilai B (berbeda).
        session(['uji_frozen' => ['left' => ['nomor_agenda'], 'right' => ['nilai_rupiah']]]);

        // Request kosong (tanpa frozen_config).
        $hasil = ColumnCustomization::resolveFrozen(Request::create('/'), $user, $this->baseOptions([
            'prefKey' => 'perpajakan_frozen',
        ]));

        // Haruslah memakai nilai A dari DB, bukan B dari sesi.
        $this->assertSame(['nomor_spp'], $hasil['right']);
        // Confirm bukan dari sesi yang berisi nilai_rupiah.
        $this->assertNotContains('nilai_rupiah', $hasil['right']);
    }
}
