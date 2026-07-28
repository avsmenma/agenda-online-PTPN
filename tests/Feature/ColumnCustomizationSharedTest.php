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

    /**
     * Review Task 3 (Important): applyToolbarParams() memproses kontrol toolbar satu
     * per satu — untuk grup radio/checkbox yang berbagi name, elemen checked menulis
     * url.searchParams.set(name, value), lalu elemen unchecked BERIKUTNYA dalam grup
     * yang sama memanggil url.searchParams.delete(name) dan menghapus nilai yang baru
     * saja ditulis. Perbaikannya: kelompokkan dulu per name (loop pertama), baru tulis
     * SATU nilai efektif per name (loop kedua) — bukan menulis langsung di dalam loop
     * pengelompokan. Tak ada JS test runner di project ini, jadi test ini memeriksa
     * bentuk sumber: penanda loop kedua (`groups.forEach(`) wajib ada, dan penulisan
     * ke URL (`.set(`/`.delete(`) wajib terjadi SETELAH loop itu dimulai — bukan di
     * dalam loop pertama yang cuma mengumpulkan elemen per name.
     */
    public function test_apply_toolbar_params_kelompokkan_per_name_sebelum_menulis(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        $awal = strpos($js, 'function applyToolbarParams(');
        $this->assertNotFalse($awal, 'fungsi applyToolbarParams tidak ditemukan');
        $akhir = strpos($js, 'function initializeModalState(', $awal);
        $this->assertNotFalse($akhir, 'penutup badan applyToolbarParams tidak ditemukan');
        $badan = substr($js, $awal, $akhir - $awal);

        $posLoopTulis = strpos($badan, 'groups.forEach(');
        $this->assertNotFalse($posLoopTulis, 'loop kedua (menulis per grup name) tidak ditemukan — pengelompokan per-name hilang, risiko regresi radio/checkbox');

        $posSet = strpos($badan, 'url.searchParams.set(');
        $posDelete = strpos($badan, 'url.searchParams.delete(');
        $this->assertNotFalse($posSet, 'url.searchParams.set( tidak ditemukan di applyToolbarParams');
        $this->assertNotFalse($posDelete, 'url.searchParams.delete( tidak ditemukan di applyToolbarParams');

        // Penulisan ke URL wajib terjadi di DALAM/SETELAH loop kedua, bukan di dalam
        // loop pertama yang mengelompokkan controls per name.
        $this->assertGreaterThan($posLoopTulis, $posSet, 'set() ke URL terjadi sebelum pengelompokan per-name selesai — risiko nilai checked tertimpa oleh elemen unchecked bernama sama');
        $this->assertGreaterThan($posLoopTulis, $posDelete, 'delete() ke URL terjadi sebelum pengelompokan per-name selesai — risiko nilai checked tertimpa oleh elemen unchecked bernama sama');
    }

    public function test_js_bersama_punya_logika_tab_beku(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        foreach ([
            'function switchColumnTab',
            'function getFrozenState',
            'function setFrozenState',
            'function renderFrozenTab',
            'function renderFrozenWarning',
        ] as $fungsi) {
            $this->assertStringContainsString($fungsi, $js);
        }

        // Penanda wajib pada simpan (lihat spec §5.5) — dikunci ke badan
        // saveColumnCustomization() saja (bukan seluruh berkas), supaya assertion
        // menggigit kalau baris pengiriman parameter dihapus. Memeriksa seluruh
        // berkas vakum: ketiga literal ini sudah dipenuhi oleh RESERVED_PARAM_NAMES
        // (Task 3) walau baris pengiriman di bawah ini dihapus total.
        $awalSimpan = strpos($js, 'function saveColumnCustomization(');
        $this->assertNotFalse($awalSimpan, 'fungsi saveColumnCustomization tidak ditemukan');
        $akhirSimpan = strpos($js, 'function applyToolbarParams(', $awalSimpan);
        $this->assertNotFalse($akhirSimpan, 'penutup badan saveColumnCustomization tidak ditemukan');
        $badanSimpan = substr($js, $awalSimpan, $akhirSimpan - $awalSimpan);

        $this->assertStringContainsString("searchParams.set('frozen_config'", $badanSimpan);
        $this->assertStringContainsString("searchParams.append('frozen_left[]'", $badanSimpan);
        $this->assertStringContainsString("searchParams.append('frozen_right[]'", $badanSimpan);

        // Kolom pinned dirender non-aktif, bukan bisa dilepas — dikunci ke badan
        // renderFrozenTab() saja. Memeriksa seluruh berkas vakum: 'disabled' sudah
        // dipenuhi oleh saveButton.disabled di updateSelectedCount() (fungsi lain,
        // tak ada hubungannya dengan kolom pinned).
        $awalRender = strpos($js, 'function renderFrozenTab(');
        $this->assertNotFalse($awalRender, 'fungsi renderFrozenTab tidak ditemukan');
        $akhirRender = strpos($js, 'function renderFrozenWarning(', $awalRender);
        $badanRender = substr($js, $awalRender, $akhirRender - $awalRender);

        $this->assertStringContainsString('disabled', $badanRender);
    }

    /**
     * Invarian: renderFrozenTab() TIDAK boleh menugaskan ulang state beku.
     * Dulu fungsi ini memangkas frozenLeftOrder/frozenRightOrder padahal hanya
     * jalan saat tab Beku dibuka — akibatnya hasil simpan bergantung pada apakah
     * tab itu pernah dibuka. Penyaringan hanya boleh di saveColumnCustomization().
     */
    public function test_render_tab_beku_tidak_menugaskan_ulang_state(): void
    {
        $js = file_get_contents(public_path('js/column-customization.js'));

        $awal = strpos($js, 'function renderFrozenTab(');
        $this->assertNotFalse($awal, 'fungsi renderFrozenTab tidak ditemukan');

        $akhir = strpos($js, 'function renderFrozenWarning(', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        $this->assertStringNotContainsString('frozenLeftOrder =', $badan);
        $this->assertStringNotContainsString('frozenRightOrder =', $badan);
    }

    /**
     * Dua urutan kolom TIDAK boleh tertukar: DOCUMENT_TABULATOR_CONFIG.columns
     * memakai urutan render (beku kiri -> bebas -> beku kanan), sedangkan modal
     * menampilkan urutan pilihan asli user.
     */
    public function test_urutan_render_berbeda_dari_urutan_modal_saat_ada_beku_kanan(): void
    {
        $user = User::factory()->create(['role' => 'akutansi']);

        $res = $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp', 'nilai_rupiah'],
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nomor_spp'],
        ]));
        $res->assertOk();

        // Modal: urutan pilihan asli.
        $res->assertSee('"selected":["nomor_agenda","nomor_spp","nilai_rupiah"]', false);
        // Tabel: nomor_spp pindah ke akhir karena dibekukan di kanan.
        $res->assertSee('"frozen":{"left":["nomor_agenda"],"right":["nomor_spp"]}', false);
        // Review: "frozen" saja tidak cukup — ia diisi dari $frozenColumns, bukan
        // dari urutan "columns". Tanpa cek ini, regresi 'columns' => collect($selectedColumns)
        // (mengembalikan urutan pilihan, bukan urutan render) tetap lolos hijau.
        // nomor_spp (beku kanan) WAJIB pindah ke akhir daftar kolom tabel.
        $res->assertSee('"columns":[{"key":"nomor_agenda","label":"Nomor Agenda"},{"key":"nilai_rupiah","label":"Nilai Rupiah"},{"key":"nomor_spp","label":"Nomor SPP"}]', false);
    }

    public function test_kolom_beku_tersimpan_dan_bisa_dikosongkan(): void
    {
        $user = User::factory()->create(['role' => 'akutansi']);

        $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp'],
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['nomor_spp'],
        ]))->assertOk();

        $user->refresh();
        $this->assertSame(['nomor_spp'], $user->table_columns_preferences['akutansi_frozen']['right']);

        // Lepas semua: hanya penanda yang dikirim, tanpa frozen_left/right.
        $this->actingAs($user)->get(route('documents.akutansi.index', [
            'columns'       => ['nomor_agenda', 'nomor_spp'],
            'frozen_config' => '1',
        ]))->assertOk();

        $user->refresh();
        $this->assertSame([], $user->table_columns_preferences['akutansi_frozen']['right']);
        // Kolom pinned tetap beku kiri.
        $this->assertSame(['nomor_agenda'], $user->table_columns_preferences['akutansi_frozen']['left']);
    }

    /**
     * Regresi produksi (ditemukan lewat query data sungguhan 2026-07-28): minimal
     * 1 akun team_verifikasi menyimpan 'tanggal_paraf'/'pemaraf' di
     * table_columns_preferences dari era sebelum Paraf jadi kolom tetap
     * (extraColumns). TeamVerifikasiController menyaring keduanya SEBELUM
     * memanggil ColumnCustomization::resolveFrozen() — tanpa penyaringan itu,
     * kolom ini lolos ke $renderColumns dan dobel dengan entri extraColumns
     * 'tanggal_paraf' yang sudah fixed di view.
     */
    public function test_kolom_paraf_lama_tidak_dobel_dengan_extra_columns_verifikasi(): void
    {
        $user = User::factory()->create([
            'role' => 'team_verifikasi',
            'table_columns_preferences' => [
                'team_verifikasi' => ['nomor_agenda', 'tanggal_paraf'],
            ],
        ]);

        // Tanpa query 'columns': controller memuat dari table_columns_preferences.
        $res = $this->actingAs($user)->get(route('documents.verifikasi.index'));
        $res->assertOk();

        // Kolom kustomisasi TIDAK boleh memuat tanggal_paraf sebagai entri "key"
        // (itu akan dobel dengan extraColumns di bawah).
        $res->assertDontSee('{"key":"tanggal_paraf"', false);
        $res->assertDontSee('{"key":"pemaraf"', false);
        // Kolom Paraf tetap tampil, tapi lewat extraColumns (field, bukan key).
        $res->assertSee('"field":"tanggal_paraf"', false);
        $res->assertSee('"field":"pemaraf"', false);
    }

    /**
     * Bug CRITICAL (review Task 5, 2026-07-28): buildColumns() di
     * document-tabulator.js membangun urutan kolom sebagai
     * cfg.columns + cfg.extraColumns + [Pengurus Dokumen]. cfg.columns SENDIRI
     * memuat kelompok beku-kanan (di ujung, sesuai renderOrder PHP) — tapi karena
     * extraColumns/kolom handler menyusul SETELAHNYA di keempat role selain
     * pembayaran, kelompok beku-kanan tidak lagi jadi kelompok TERAKHIR dalam
     * definisi tabel. Modul FrozenColumns Tabulator menempelkan ke tepi kanan
     * viewport berdasarkan posisi (bukan properti sisi eksplisit), sehingga kolom
     * beku-kanan mengambang menutupi Deadline/Status/Paraf/Pengurus Dokumen saat
     * digulir. Perbaikan: kelompok beku-kanan ditunda (kelompokBekuKanan) dan
     * ditambahkan PALING TERAKHIR — setelah extraColumns maupun kolom handler.
     * Tak ada test runner JS di project ini (lihat catatan test lain di berkas
     * ini), jadi test ini memeriksa bentuk sumber: penambahan kelompokBekuKanan
     * WAJIB terjadi setelah penambahan extraColumns dan kolom "Pengurus Dokumen".
     */
    public function test_build_columns_menunda_kelompok_beku_kanan_ke_akhir(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awal = strpos($js, 'function buildColumns(');
        $this->assertNotFalse($awal, 'fungsi buildColumns tidak ditemukan');
        $akhir = strpos($js, 'function activeRange(', $awal);
        $this->assertNotFalse($akhir, 'penutup badan buildColumns tidak ditemukan');
        $badan = substr($js, $awal, $akhir - $awal);

        $posDeklarasiKelompok = strpos($badan, 'kelompokBekuKanan');
        $this->assertNotFalse($posDeklarasiKelompok, 'kelompokBekuKanan tidak ditemukan di buildColumns');

        $posExtraColumns = strpos($badan, "(cfg.extraColumns || []).forEach(");
        $this->assertNotFalse($posExtraColumns, 'loop extraColumns tidak ditemukan di buildColumns');

        $posHandler = strpos($badan, 'cfg.showHandler !== false');
        $this->assertNotFalse($posHandler, 'blok kolom handler (Pengurus Dokumen) tidak ditemukan di buildColumns');

        // Penambahan (push) kelompok beku-kanan wajib jadi baris TERAKHIR di badan
        // fungsi — dicari dari posisi TERAKHIR literal '.push(buildColumnDef(c))'
        // di dalam badan, yang wajib berada di dalam forEach kelompokBekuKanan.
        $posPushTerakhir = strrpos($badan, '.push(buildColumnDef(c))');
        $this->assertNotFalse($posPushTerakhir, 'push(buildColumnDef(c)) tidak ditemukan di buildColumns');

        $this->assertGreaterThan($posExtraColumns, $posPushTerakhir, 'push terakhir kolom terjadi SEBELUM extraColumns ditambahkan — kelompok beku-kanan belum jadi kelompok terakhir');
        $this->assertGreaterThan($posHandler, $posPushTerakhir, 'push terakhir kolom terjadi SEBELUM kolom Pengurus Dokumen ditambahkan — kelompok beku-kanan belum jadi kelompok terakhir');

        // forEach kelompokBekuKanan wajib berada SETELAH extraColumns & handler
        // (bukan cuma push-nya) — deklarasi variabel boleh di awal, tapi
        // forEach-nya sendiri (pemakaian) wajib menyusul di akhir.
        $posForEachKelompok = strrpos($badan, 'kelompokBekuKanan.forEach(');
        $this->assertNotFalse($posForEachKelompok, 'kelompokBekuKanan.forEach( tidak ditemukan di buildColumns');
        $this->assertGreaterThan($posExtraColumns, $posForEachKelompok);
        $this->assertGreaterThan($posHandler, $posForEachKelompok);
    }
}
