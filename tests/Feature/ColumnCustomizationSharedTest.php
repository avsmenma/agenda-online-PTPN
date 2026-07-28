<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Kontrak partial bersama partials._columnCustomizationModal + file JS
 * public/js/column-customization.js (dipakai 4 view Tabulator role).
 */
class ColumnCustomizationSharedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // buildXQuery() akutansi/perpajakan/verifikasi memakai fungsi MySQL
        // (REGEXP, SUBSTRING_INDEX, LPAD) di ORDER BY nomor_agenda — polyfill utk SQLite
        // (disalin dari AkutansiTabulatorSwitchTest/OperatorTabulatorViewTest::setUp).
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', fn ($s, $d, $c) => implode($d, array_slice(explode($d, (string) $s), 0, (int) $c)), 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    /**
     * Bukti adopsi Task 2: 3 view keuangan (akutansi/perpajakan/verifikasi) memakai
     * partial + JS bersama, bukan lagi modal/JS inline duplikat masing-masing.
     */
    public function test_view_keuangan_memakai_modal_bersama(): void
    {
        $cases = [
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
        ];
        foreach ($cases as $c) {
            $user = User::factory()->create(['role' => $c['role']]);
            $res = $this->actingAs($user)->get(route($c['route']));
            $res->assertOk();
            // Modal via partial + JS bersama hadir.
            $res->assertSee('id="columnCustomizationModal"', false);
            $res->assertSee('js/column-customization.js', false);
            $res->assertSee('window.COLUMN_CUSTOMIZATION_CONFIG', false);
            // JS modal inline lama sudah tidak ada (bukti ekstraksi).
            $res->assertDontSee('let availableColumnsData =', false);
            // Bukti CSS modal lewat @push('styles') sampai ke <head> layout (bukan
            // nyangkut di body setelah markup modal — regresi flash-of-unstyled-modal).
            // assertSeeInOrder menegakkan URUTAN: aturan display:none WAJIB lebih dulu
            // daripada markup modal. Tanpa cek urutan, assertion tetap lolos meski CSS
            // kembali nyangkut di body (persis bug yang diperbaiki 2026-07-28).
            $res->assertSeeInOrder([
                '.customization-modal { display: none;',
                'id="columnCustomizationModal"',
            ], false);
        }
    }

    public function test_partial_merender_modal_dan_jembatan_config(): void
    {
        // Catatan: partial ini render standalone (tanpa layout), jadi CSS di
        // @push('styles') TIDAK ikut ke $html — @push cuma dikumpulkan ke stack,
        // baru dikeluarkan saat layout memanggil @stack('styles'). Bukti CSS
        // benar-benar sampai ke halaman ada di test_view_keuangan_memakai_modal_bersama
        // (request penuh lewat layouts/app.blade.php).
        $html = view('partials._columnCustomizationModal', [
            'availableColumns' => ['nomor_agenda' => 'Nomor Agenda', 'no_spp' => 'No SPP'],
            'selectedColumns'  => ['nomor_agenda'],
        ])->render();

        // Markup modal ada.
        $this->assertStringContainsString('id="columnCustomizationModal"', $html);
        // Jembatan data (menggantikan @json inline di tiap view).
        $this->assertStringContainsString('window.COLUMN_CUSTOMIZATION_CONFIG', $html);
        // Kolom dari $availableColumns dirender sebagai item.
        $this->assertStringContainsString('Nomor Agenda', $html);
        $this->assertStringContainsString('data-column="no_spp"', $html);
    }

    public function test_file_js_bersama_ada_dan_berisi_fungsi_inti(): void
    {
        $path = public_path('js/column-customization.js');
        $this->assertFileExists($path);
        $js = file_get_contents($path);

        $this->assertStringContainsString('function openColumnCustomizationModal', $js);
        // Task 3: appendActiveFilterInputs (submit #filterForm) diganti applyToolbarParams
        // (pembangunan URL) — lihat test_js_bersama_simpan_lewat_url_bukan_submit_form.
        $this->assertStringContainsString('function applyToolbarParams', $js);
        // Baca data dari jembatan window, BUKAN @json Blade (file statis).
        $this->assertStringContainsString('COLUMN_CUSTOMIZATION_CONFIG', $js);
        $this->assertStringNotContainsString('@json', $js);
    }

    /**
     * Bukti adopsi Task 3: view operator memakai partial + JS bersama (modal JS inline
     * lama sudah tidak ada), TANPA menghilangkan JS operator-only (hapus baris aktif).
     */
    public function test_view_operator_pakai_modal_bersama_tanpa_hilangkan_fitur_operator(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $res = $this->actingAs($user)->get(route('documents.index'));
        $res->assertOk();
        $res->assertSee('id="columnCustomizationModal"', false);
        $res->assertSee('js/column-customization.js', false);
        $res->assertDontSee('let availableColumnsData =', false);
        // Operator ikut dijaga: CSS modal wajib sampai <head> SEBELUM markup modal.
        $res->assertSeeInOrder([
            '.customization-modal { display: none;',
            'id="columnCustomizationModal"',
        ], false);
        // Fitur operator-only WAJIB tetap.
        $res->assertSee('id="btnHapusBarisAktif"', false);
    }

    /**
     * Tab kedua (Kolom Beku) hadir di keempat role, dan CSS-nya tetap lewat
     * @push('styles') — urutan CSS sebelum markup adalah jaring
     * flash-of-unstyled-modal yang sudah dipasang 2026-07-28.
     */
    public function test_tab_kolom_beku_hadir_di_semua_view(): void
    {
        $cases = [
            ['role' => 'operator',        'route' => 'documents.index'],
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
        ];

        foreach ($cases as $c) {
            $user = User::factory()->create(['role' => $c['role']]);
            $res = $this->actingAs($user)->get(route($c['route']));
            $res->assertOk();

            $res->assertSee('data-tab="kolom"', false);
            $res->assertSee('data-tab="beku"', false);
            $res->assertSee('id="tabPanelBeku"', false);
            $res->assertSee('id="frozenList"', false);
            $res->assertSee('id="frozenWarning"', false);
            // Jembatan data untuk tab Beku.
            $res->assertSee('frozen:', false);
            $res->assertSee('pinned:', false);

            // CSS tab wajib sampai <head> SEBELUM markup modal.
            $res->assertSeeInOrder([
                '.column-tabs {',
                'id="columnCustomizationModal"',
            ], false);
        }
    }

    /**
     * Regresi review: #tabPanelKolom (wrapper baru pembungkus .customization-grid)
     * WAJIB meneruskan rantai flex .modal-body-custom → .customization-grid, kalau
     * tidak flex:1/min-height:0 pada .customization-grid jadi tak berefek (parent
     * langsungnya bukan flex container lagi) dan panel Pilih Kolom + Preview kolaps
     * jadi satu blok scroll panjang — regresi layout senyap pada tab yang sudah
     * dipakai 4 role di produksi. Ini bukan test layout terhitung (feature test tak
     * bisa mengukur box CSS), hanya jaring supaya aturan ini tak terhapus tanpa sadar.
     */
    public function test_tabpanelkolom_meneruskan_rantai_flex(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $res = $this->actingAs($user)->get(route('documents.index'));
        $res->assertOk();

        $res->assertSee('#tabPanelKolom { display: flex; flex-direction: column; flex: 1; min-height: 0; }', false);

        // Aturan #tabPanelKolom wajib sampai <head> sebelum markup modal (pola
        // sama seperti jaring flash-of-unstyled-modal untuk aturan lain).
        $res->assertSeeInOrder([
            '#tabPanelKolom {',
            'id="columnCustomizationModal"',
        ], false);
    }

    /**
     * Jalur simpan pindah dari filterForm.submit() ke pembangunan URL.
     * Parameter mati enable_customization tidak boleh dikirim lagi.
     */
    public function test_js_bersama_simpan_lewat_url_bukan_submit_form(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        // Jalur baru: bangun URL lalu arahkan browser.
        $this->assertStringContainsString('new URL(', $js);
        $this->assertStringContainsString('function applyToolbarParams', $js);

        // Jalur lama benar-benar hilang.
        $this->assertStringNotContainsString('filterForm.submit()', $js);
        $this->assertStringNotContainsString('function appendActiveFilterInputs', $js);

        // Parameter mati tidak dikirim lagi (nol pembaca di sisi server).
        $this->assertStringNotContainsString('enable_customization', $js);
    }

    /**
     * Bug 2026-07-28: toggleColumn memasang atribut draggable tapi tidak
     * memasang listener drag, sehingga kolom yang baru dicentang tak bisa
     * ditarik sampai modal ditutup-buka.
     */
    public function test_toggle_kolom_memasang_ulang_listener_drag(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        $awal = strpos($js, 'function toggleColumn(');
        $this->assertNotFalse($awal, 'fungsi toggleColumn tidak ditemukan');

        $akhir = strpos($js, 'function selectAllColumns(', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        $this->assertStringContainsString('initializeDragAndDrop()', $badan);
    }
}
