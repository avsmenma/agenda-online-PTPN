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
            // index() pembayaran menghitung dropdown filter Tahun via
            // selectRaw('YEAR(created_at) as year') — YEAR() bukan fungsi SQLite
            // bawaan (polyfill sama seperti PembayaranTabulatorSwitchTest::setUp()).
            $pdo->sqliteCreateFunction('YEAR', fn ($v) => $v ? (int) substr((string) $v, 0, 4) : null, 1);
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
     * Tab kedua (Kolom Beku) hadir di kelima role, dan CSS-nya tetap lewat
     * @push('styles') — urutan CSS sebelum markup adalah jaring
     * flash-of-unstyled-modal yang sudah dipasang 2026-07-28. Spec §10 menuntut
     * jaring ini mencakup 5 halaman (termasuk pembayaran, Task 6 — sebelumnya
     * hanya operator/akutansi/perpajakan/verifikasi yang tercakup).
     */
    public function test_tab_kolom_beku_hadir_di_semua_view(): void
    {
        $cases = [
            ['role' => 'operator',        'route' => 'documents.index'],
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
            ['role' => 'pembayaran',      'route' => 'documents.pembayaran.index'],
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
     *
     * Review akhir branch (2026-07-28): dulu hanya diuji di akutansi — jaring
     * "benar-benar tersambung" (kunci 'columns' benar-benar memakai $renderColumns,
     * bukan $selectedColumns via fallback `?? `) tidak menutupi 3 role lain.
     * Dibuktikan lewat mutasi: hapus frozenColumns/pinnedColumns/renderColumns dari
     * data controller operator membuat fitur beku hilang total + bug beku-kanan
     * mengambang hidup lagi, tapi suite tetap hijau. Sekarang loop 4 role
     * (operator/akutansi/perpajakan/verifikasi) — pembayaran sudah punya jaring
     * setara sendiri (test_kolom_beku_pembayaran_tersimpan_dan_bisa_dikosongkan +
     * paritas kolom di tempat lain). Ketiga kolom (nomor_agenda/nomor_spp/
     * nilai_rupiah) & labelnya diverifikasi identik di config/document_columns.php
     * utk keempat role (operator: base apa adanya; akutansi/perpajakan/verifikasi:
     * base tanpa 'status', verifikasi juga tanpa paraf — tak satu pun ketiga kolom
     * ini kena exclude).
     */
    public function test_urutan_render_berbeda_dari_urutan_modal_saat_ada_beku_kanan(): void
    {
        $cases = [
            ['role' => 'operator',        'route' => 'documents.index'],
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
        ];

        foreach ($cases as $c) {
            $user = User::factory()->create(['role' => $c['role']]);

            $res = $this->actingAs($user)->get(route($c['route'], [
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
            // nomor_spp (beku kanan) WAJIB pindah ke akhir daftar kolom tabel, di
            // role {$c['role']}.
            $res->assertSee('"columns":[{"key":"nomor_agenda","label":"Nomor Agenda"},{"key":"nilai_rupiah","label":"Nilai Rupiah"},{"key":"nomor_spp","label":"Nomor SPP"}]', false);
        }
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
     * Review Task 6 (IMPORTANT): mirip test_kolom_beku_tersimpan_dan_bisa_dikosongkan di
     * atas, tapi menyasar PEMBAYARAN — satu-satunya role yang prefKey-nya
     * ('pembayaran_dashboard_frozen') sudah berisi data user produksi sungguhan
     * (contoh nyata: {"left":["nomor_agenda"],"right":["status_pembayaran"]}). Tanpa
     * jaring ini, mengganti nama/menghapus 'prefKey' => 'pembayaran_dashboard_frozen'
     * di DashboardPembayaranController::index() membuat resolveFrozen() diam-diam
     * jatuh ke sesi/default — suite tetap hijau, tapi user Team Pembayaran kehilangan
     * kolom beku-kanannya tanpa jejak setelah deploy.
     */
    public function test_kolom_beku_pembayaran_tersimpan_dan_bisa_dikosongkan(): void
    {
        $user = User::factory()->create(['role' => 'pembayaran']);

        $this->actingAs($user)->get(route('documents.pembayaran.index', [
            'columns'       => ['nomor_agenda', 'status_pembayaran'],
            'frozen_config' => '1',
            'frozen_left'   => ['nomor_agenda'],
            'frozen_right'  => ['status_pembayaran'],
        ]))->assertOk();

        $user->refresh();
        $this->assertSame(['status_pembayaran'], $user->table_columns_preferences['pembayaran_dashboard_frozen']['right']);

        // Lepas semua: hanya penanda yang dikirim, tanpa frozen_left/right.
        $this->actingAs($user)->get(route('documents.pembayaran.index', [
            'columns'       => ['nomor_agenda', 'status_pembayaran'],
            'frozen_config' => '1',
        ]))->assertOk();

        $user->refresh();
        $this->assertSame([], $user->table_columns_preferences['pembayaran_dashboard_frozen']['right']);
        // Kolom pinned (nomor_agenda) tetap beku kiri.
        $this->assertSame(['nomor_agenda'], $user->table_columns_preferences['pembayaran_dashboard_frozen']['left']);
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

    /**
     * Review akhir branch (item 2, 2026-07-28): tepiKananKolomBeku() hanya
     * mengenal .tabulator-frozen-left — kompensator gulir tak pernah tahu ada
     * kolom beku-KANAN. Dibuktikan di produksi (pembayaran, frozen.right =
     * ['status_pembayaran']): setelah 40x panah kanan, sel aktif (880-971)
     * berada di belakang tepi kiri blok beku-kanan (847) — tertimbun. Tak ada
     * test runner JS di project ini, jadi test ini memeriksa bentuk sumber:
     * (a) sisi kiri byte-identik (5 baris asli tak tersentuh), (b) fungsi sisi
     * kanan ada & memakai selector .tabulator-frozen-right, (c) terdaftar
     * lewat listener 'rangeChanged' KEDUA yang terpisah (bukan disisipkan ke
     * blok sisi kiri), (d) koreksinya += (menggulir kanan) — kebalikan -=
     * sisi kiri.
     */
    public function test_kompensasi_gulir_beku_kanan_ada_dan_terpisah_dari_sisi_kiri(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awalFix = strpos($js, 'function wireFrozenScrollFix');
        $this->assertNotFalse($awalFix, 'wireFrozenScrollFix tidak ditemukan');
        $akhirFix = strpos($js, "table.on('dataLoadError'", $awalFix);
        $this->assertNotFalse($akhirFix, 'penutup wireFrozenScrollFix tidak ditemukan');
        $badan = substr($js, $awalFix, $akhirFix - $awalFix);

        // Sisi kiri byte-identik: 5 baris asli (fungsi + listener pertama)
        // masih persis apa adanya, tak tersentuh oleh penambahan sisi kanan.
        $this->assertStringContainsString(
            "        const tepi = tepiKananKolomBeku();\n"
            . "        if (!tepi) return;\n"
            . "        const kotak = el.getBoundingClientRect();\n"
            . "        const tertimbun = tepi - kotak.left;\n"
            . "        if (tertimbun > 0) holder.scrollLeft -= (tertimbun + JARAK_AMAN);",
            $badan
        );

        // Fungsi sisi kanan ada & memakai selector kelas kolom beku-kanan.
        $posFungsiKanan = strpos($badan, 'function tepiKiriKolomBekuKanan(');
        $this->assertNotFalse($posFungsiKanan, 'fungsi tepiKiriKolomBekuKanan tidak ditemukan');
        $this->assertStringContainsString('tabulator-frozen-right', $badan);

        // Terdaftar lewat listener rangeChanged KEDUA (bukan disisipkan ke
        // listener sisi kiri) -- wajib ada 2 pendaftaran di badan ini.
        $this->assertSame(2, substr_count($badan, "table.on('rangeChanged'"));

        // Koreksi sisi kanan memakai += (menggulir kanan), bukan -= seperti
        // sisi kiri. Sejak klem tepi beku-kiri (2026-07-28) basis koreksi
        // (tertimbun + JARAK_AMAN) disimpan ke variabel 'koreksi' dulu
        // (boleh diklem lebih kecil) sebelum diterapkan ke scrollLeft --
        // bukan lagi ekspresi literal langsung seperti semula.
        $this->assertStringContainsString('let koreksi = tertimbun + JARAK_AMAN;', $badan);
        $this->assertStringContainsString('holder.scrollLeft += koreksi;', $badan);

        // Fungsi sisi kanan wajib dipakai SETELAH listener kedua terdaftar
        // (sanity urutan sumber, bukan sekadar hadir di mana pun).
        $posListenerKedua = strrpos($badan, "table.on('rangeChanged'");
        $this->assertGreaterThan($posFungsiKanan, $posListenerKedua);
    }

    /**
     * Bug review 2026-07-28 (ditemukan sesudah kompensasi kanan di atas
     * dipasang): kompensasi kanan sebelumnya SELALU menang -- kalau sel
     * aktif lebih lebar daripada celah bebas antara blok beku-kiri & blok
     * beku-kanan, tepi kanan sel didorong pas ke tepi blok kanan sampai
     * tepi KIRI sel (awal isi, paling berguna utk teks kiri-ke-kanan) malah
     * tertimbun blok beku-kiri -- kebalikan dari bug yang sedang diperbaiki
     * listener ini. Perbaikan: klem koreksi supaya tepi kiri sel tidak
     * pernah melewati tepi kanan blok beku-kiri (+ JARAK_AMAN yang sama
     * dipakai listener kiri, bukan angka ajaib baru), dan lewati koreksi
     * kalau hasil klem <= 0.
     */
    public function test_kompensasi_gulir_kanan_diklem_ke_tepi_beku_kiri(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awalFix = strpos($js, 'function wireFrozenScrollFix');
        $this->assertNotFalse($awalFix, 'wireFrozenScrollFix tidak ditemukan');
        $akhirFix = strpos($js, "table.on('dataLoadError'", $awalFix);
        $this->assertNotFalse($akhirFix, 'penutup wireFrozenScrollFix tidak ditemukan');
        $badan = substr($js, $awalFix, $akhirFix - $awalFix);

        // Klem wajib memakai tepiKananKolomBeku() -- fungsi sisi kiri yang
        // sudah ada -- bukan query DOM baru, dan JARAK_AMAN yang sama dipakai
        // listener kiri (bukan angka ajaib baru).
        $posKlem = strpos(
            $badan,
            'if (tepiKiri) koreksi = Math.min(koreksi, kotak.left - (tepiKiri + JARAK_AMAN));'
        );
        $this->assertNotFalse($posKlem, 'klem tepi beku-kiri tidak ditemukan di listener kanan');

        // Klem wajib berada SETELAH pendaftaran listener kanan (bukan bocor
        // ke listener kiri) -- sanity urutan sumber.
        $posListenerKedua = strrpos($badan, "table.on('rangeChanged'");
        $this->assertGreaterThan($posListenerKedua, $posKlem);

        // Koreksi dilewati kalau hasil klem <= 0 -- satu tembakan per event,
        // tak ada penerapan scrollLeft dengan nilai negatif/nol yang bisa
        // memicu gulir bolak-balik.
        $posSkip = strpos($badan, 'if (koreksi <= 0) return;', $posKlem);
        $this->assertNotFalse($posSkip, 'guard koreksi <= 0 tidak ditemukan setelah klem');

        // Penerapan akhir memakai variabel 'koreksi' (hasil klem), bukan lagi
        // literal tertimbun + JARAK_AMAN langsung ke scrollLeft.
        $posTerap = strpos($badan, 'holder.scrollLeft += koreksi;', $posSkip);
        $this->assertNotFalse($posTerap, 'penerapan scrollLeft += koreksi tidak ditemukan setelah guard');
    }

    /**
     * Bug 2026-07-28 (dibuktikan di produksi, bukan dugaan): persistenceID
     * Tabulator dulu konstanta harfiah ('agenda-operator-documents') dipakai
     * SEMUA role sekaligus, dan opsi `persistence: { columns: ['width'] }`
     * TERNYATA tetap mengunci URUTAN kolom — mergeDefinition() internal
     * Tabulator membangun hasil dengan mengiterasi ARRAY TERSIMPAN, sehingga
     * urutan hasil = urutan localStorage apa pun properti yang diminta. Akibatnya
     * localStorage basi (dari sebelum fitur Kolom Beku ada) menimpa urutan baru
     * dari server, dan kelima role saling mencemari lebar/urutan lewat kunci yang
     * sama. Perbaikan: persistenceID dibangun dari CFG.mountId (penanda role) +
     * sidik jari daftar kunci kolom yang sedang dikirim server — bukan lagi
     * string tetap.
     */
    public function test_persist_id_dibangun_dari_mount_id_dan_sidik_jari(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        // Konstanta harfiah lama (sama untuk semua role) sudah tidak ada.
        $this->assertStringNotContainsString("PERSIST_ID = 'agenda-operator-documents'", $js);

        $awal = strpos($js, 'function hashSusunanKolom(');
        $this->assertNotFalse($awal, 'fungsi hashSusunanKolom tidak ditemukan');
        $akhir = strpos($js, 'const table = new Tabulator(mountEl()', $awal);
        $this->assertNotFalse($akhir, 'penutup blok persistenceID (sebelum konstruktor Tabulator) tidak ditemukan');
        $badan = substr($js, $awal, $akhir - $awal);

        // Penanda role dari CFG.mountId, dengan nilai cadangan bila kosong.
        $this->assertStringContainsString("const roleTag = CFG.mountId || 'documents';", $badan);
        // PERSIST_ID = awalan tetap + roleTag + sidik jari — dibangun, bukan literal.
        $this->assertStringContainsString("const PERSIST_ID_PREFIX = 'agenda-documents-' + roleTag + '-';", $badan);
        $this->assertStringContainsString('const PERSIST_ID = PERSIST_ID_PREFIX + sidikJariKolom;', $badan);
    }

    /**
     * Sidik jari WAJIB dihitung dari daftar KUNCI KOLOM yang sedang dikirim
     * server (CFG.columns[].key) — bukan dari localStorage, bukan dari DOM —
     * supaya begitu susunan kolom berubah (pilihan/urutan), kunci persistence
     * ikut berganti dan simpanan basi tak bisa menimpa susunan baru. Cakupan
     * freeze (CFG.frozen) diuji terpisah di
     * test_sidik_jari_ikut_membaca_susunan_beku di bawah.
     */
    public function test_sidik_jari_dihitung_dari_daftar_kunci_kolom(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awal = strpos($js, 'function hashSusunanKolom(');
        $this->assertNotFalse($awal, 'fungsi hashSusunanKolom tidak ditemukan');
        $akhir = strpos($js, 'const table = new Tabulator(mountEl()', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        // Hash djb2 sederhana (bukan kriptografis) atas string gabungan.
        $this->assertStringContainsString('charCodeAt(', $badan);
        $this->assertStringContainsString('.toString(36)', $badan);
        // Input hash mencakup CFG.columns[].key — daftar kolom yang SEDANG
        // dikirim server, urut sesuai urutan server (bukan urutan localStorage).
        $this->assertStringContainsString(
            "(CFG.columns || []).map(function (c) { return c.key; }).join(',')",
            $badan
        );
    }

    /**
     * Bug lanjutan 2026-07-28 (ditemukan review, sebelum sempat ke produksi):
     * FrozenColumnLayout::renderOrder() (PHP) mempertahankan urutan RELATIF di
     * dalam tiap kelompok kiri/bebas/kanan. Kalau kolom yang dibekukan ke
     * KANAN kebetulan sudah berada di EKOR daftar 'selected' (kasus lazim:
     * kolom terakhir dibekukan ke kanan), urutan CFG.columns[].key TIDAK
     * berubah sama sekali walau freeze berubah — padahal buildColumns() di
     * klien menunda kelompok beku-kanan ke paling akhir, jadi urutan definisi
     * TABEL tetap berubah. Kalau sidik jari hanya dari CFG.columns[].key, ia
     * gagal mendeteksi perubahan ini: localStorage lama menang lewat
     * mergeDefinition, dan kolom beku-kanan mengambang lagi di tengah tabel —
     * regresi terhadap bug yang commit 9668455 perbaiki, lewat jalur berbeda.
     * Karena itu CFG.frozen (kiri DAN kanan) wajib ikut jadi input hash, dan
     * pembacaannya wajib aman terhadap CFG.frozen yang tak ada/bukan array
     * (tak pernah melempar).
     */
    public function test_sidik_jari_ikut_membaca_susunan_beku(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awal = strpos($js, 'function hashSusunanKolom(');
        $this->assertNotFalse($awal, 'fungsi hashSusunanKolom tidak ditemukan');
        $akhir = strpos($js, 'const table = new Tabulator(mountEl()', $awal);
        $badan = substr($js, $awal, $akhir - $awal);

        // Pembacaan CFG.frozen.left/right dijaga Array.isArray — tak pernah
        // melempar walau CFG.frozen tak ada / bukan objek / propertinya bukan array.
        $this->assertStringContainsString('Array.isArray(', $badan);
        $this->assertStringContainsString('daftarBekuAman(CFG.frozen && CFG.frozen.left)', $badan);
        $this->assertStringContainsString('daftarBekuAman(CFG.frozen && CFG.frozen.right)', $badan);

        // Kedua daftar beku (kiri & kanan) WAJIB dipakai sebagai bagian string
        // yang di-hash — dipersempit ke badan pemanggilan hashSusunanKolom()
        // saja (dari deklarasi sidikJariKolom sampai deklarasi roleTag
        // berikutnya) supaya assertion menggigit kalau salah satu daftar
        // dibaca tapi dibuang begitu saja, tak pernah dipakai di input hash.
        $posHash = strpos($badan, 'const sidikJariKolom = hashSusunanKolom(');
        $this->assertNotFalse($posHash, 'perhitungan sidikJariKolom tidak ditemukan');
        $posBerikutnya = strpos($badan, 'const roleTag = CFG.mountId', $posHash);
        $this->assertNotFalse($posBerikutnya, 'deklarasi roleTag setelah sidikJariKolom tidak ditemukan');
        $badanPanggilanHash = substr($badan, $posHash, $posBerikutnya - $posHash);

        $this->assertStringContainsString('bekuKiriUntukHash.join(', $badanPanggilanHash, 'daftar beku kiri tidak ikut dipakai sebagai input hash');
        $this->assertStringContainsString('bekuKananUntukHash.join(', $badanPanggilanHash, 'daftar beku kanan tidak ikut dipakai sebagai input hash');
        // Review lanjutan: assertion di atas HANYA memeriksa bagian beku —
        // tidak menjamin daftar KUNCI KOLOM (CFG.columns[].key) masih ikut
        // dipakai sebagai input hash. Tanpa baris ini, menjadikan
        // 'daftarKunciKolom' variabel mati (hash hanya dari bagian beku) tetap
        // lolos HIJAU — sidik jari berhenti melacak pilihan/urutan kolom sama
        // sekali, dan bug ASLI (localStorage basi menimpa urutan kolom server)
        // hidup lagi di kelima role, senyap.
        $this->assertStringContainsString(
            "(CFG.columns || []).map(function (c) { return c.key; }).join(',')",
            $badanPanggilanHash,
            'daftar kunci kolom tidak ikut dipakai sebagai input hash'
        );
    }

    /**
     * USER_RESIZED_FLAG_KEY menentukan pilihan layout fitData vs fitDataStretch
     * (keputusan user 2026-07-22: lebar tarikan user dihormati apa adanya begitu
     * pernah di-resize). Kalau penanda ini ikut disidikjari seperti PERSIST_ID,
     * ia akan ter-reset setiap kali user mengubah susunan kolom (pilih/freeze
     * kolom lain) dan layout melompat balik ke fitDataStretch — regresi terhadap
     * keputusan itu. Karena itu penanda ini WAJIB hanya per-role (roleTag),
     * TANPA sidik jari susunan kolom.
     */
    public function test_user_resized_flag_key_tanpa_sidik_jari(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $pos = strpos($js, 'const USER_RESIZED_FLAG_KEY = ');
        $this->assertNotFalse($pos, 'deklarasi USER_RESIZED_FLAG_KEY tidak ditemukan');
        $akhirBaris = strpos($js, "\n", $pos);
        $this->assertNotFalse($akhirBaris);
        $baris = substr($js, $pos, $akhirBaris - $pos);

        $this->assertStringContainsString('roleTag', $baris);
        $this->assertStringNotContainsString('sidikJariKolom', $baris);
        $this->assertStringNotContainsString('PERSIST_ID', $baris);
    }

    /**
     * Review akhir branch (item 3, 2026-07-28): adaLebarTersimpan dulu HANYA
     * membaca USER_RESIZED_FLAG_KEY (penanda "user pernah resize", sengaja
     * TANPA sidik jari — lihat test di atas). Tapi kunci LEBAR tersimpan
     * Tabulator sendiri ('tabulator-' + PERSIST_ID + '-columns') justru IKUT
     * sidik jari, dan blok pembersihan kunci basi tepat di bawahnya menghapus
     * kunci itu begitu user mengubah kolom/beku (persistenceID berganti).
     * Akibatnya penanda tetap true tapi lebarnya sudah hilang -> layout jatuh
     * ke 'fitData' TANPA lebar tersimpan -> tabel tak melebar penuh, berulang
     * tiap kali user menyentuh kolom. Perbaikan: adaLebarTersimpan wajib true
     * hanya bila KEDUANYA ada -- penanda *dan* kunci lebar aktif.
     */
    public function test_ada_lebar_tersimpan_mengecek_kunci_lebar_aktif(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $awal = strpos($js, 'let adaLebarTersimpan = false;');
        $this->assertNotFalse($awal, 'deklarasi adaLebarTersimpan tidak ditemukan');
        $akhir = strpos($js, '// Bersihkan kunci persistence BASI', $awal);
        $this->assertNotFalse($akhir, 'penutup blok adaLebarTersimpan (sebelum blok pembersihan) tidak ditemukan');
        $badan = substr($js, $awal, $akhir - $awal);

        // Try/catch tetap wajib (localStorage bisa melempar di mode privat).
        $this->assertStringContainsString('try {', $badan);
        $this->assertStringContainsString('catch (e) {}', $badan);

        // Penanda resize dibaca.
        $this->assertStringContainsString('localStorage.getItem(USER_RESIZED_FLAG_KEY)', $badan);

        // adaLebarTersimpan WAJIB juga menuntut kunci lebar aktif benar-benar
        // ada -- bukan cuma bergantung pada penanda.
        $this->assertStringContainsString(
            "adaLebarTersimpan = pernahResize && localStorage.getItem('tabulator-' + PERSIST_ID + '-columns') !== null;",
            $badan
        );
    }

    /**
     * localStorage bisa melempar di mode privat / kuota penuh, dan tabel TIDAK
     * boleh gagal render karenanya (aturan lama berkas ini). Blok pembersihan
     * kunci persistence basi (sidik jari lama) adalah akses localStorage BARU
     * yang ditambahkan — wajib ikut dibungkus try/catch, sama seperti pembacaan
     * USER_RESIZED_FLAG_KEY di atasnya.
     */
    public function test_pembersihan_kunci_basi_dibungkus_try_catch(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        // Anchor persis pada komentar blok pembersihan (BUKAN dari deklarasi
        // USER_RESIZED_FLAG_KEY) — badan sebelumnya juga mencakup try/catch
        // pembacaan flag di atasnya (blok tak terkait), sehingga assertion
        // "try sebelum removeItem" lolos vakum walau try/catch cuma dihapus
        // dari cleanup-nya sendiri (dibuktikan lewat uji mutasi). Anchor ini
        // mempersempit badan HANYA ke blok pembersihan itu sendiri.
        $posAwal = strpos($js, "// Bersihkan kunci persistence BASI milik role ini");
        $this->assertNotFalse($posAwal, 'komentar blok pembersihan kunci basi tidak ditemukan');
        $posTable = strpos($js, 'const table = new Tabulator(mountEl()', $posAwal);
        $this->assertNotFalse($posTable);
        $badan = substr($js, $posAwal, $posTable - $posAwal);

        // Blok pembersihan memakai localStorage.key()/removeItem().
        $this->assertStringContainsString('localStorage.key(', $badan);
        $this->assertStringContainsString('localStorage.removeItem(', $badan);

        // try/catch WAJIB membungkus tepat blok ini: satu-satunya 'try {' dalam
        // badan yang sudah dipersempit ini adalah milik pembersihan sendiri, dan
        // posisinya wajib mendahului baik pemanggilan removeItem() maupun catch
        // penutupnya wajib menyusul SETELAH removeItem() (bukan menutup lebih awal).
        $posTry = strpos($badan, 'try {');
        $posRemove = strpos($badan, 'localStorage.removeItem(');
        $posCatch = strpos($badan, '} catch (e) {}', $posRemove === false ? 0 : $posRemove);
        $this->assertNotFalse($posTry, 'blok try untuk pembersihan kunci basi tidak ditemukan');
        $this->assertNotFalse($posCatch, 'blok catch SETELAH removeItem tidak ditemukan — try mungkin sudah tertutup sebelum removeItem dipanggil');
        $this->assertLessThan($posRemove, $posTry, 'localStorage.removeItem dipanggil di luar blok try');
        $this->assertGreaterThan($posRemove, $posCatch, 'catch menutup sebelum removeItem dipanggil — removeItem berada di luar blok try/catch');
    }

    /**
     * Bug lanjutan 2026-07-28 (ditemukan review, sebelum sempat ke produksi):
     * klausa `k !== kunciBaruTabulator` adalah satu-satunya hal yang mencegah
     * blok pembersihan menghapus kunci lebar kolom yang BARU SAJA dipasang
     * (persistenceID untuk susunan kolom yang sedang aktif SEKARANG). Tanpa
     * klausa ini, setiap page load akan menghapus kunci lebarnya sendiri tepat
     * setelah dipasang — lebar hasil tarikan user tidak akan pernah bertahan
     * lagi lintas kunjungan (senyap total, lolos dari test lain karena tak ada
     * yang memeriksa isi kondisi if-nya, hanya bahwa removeItem() dipanggil).
     */
    public function test_pembersihan_tidak_menghapus_kunci_yang_baru_dipasang(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $posAwal = strpos($js, '// Bersihkan kunci persistence BASI milik role ini');
        $this->assertNotFalse($posAwal, 'komentar blok pembersihan kunci basi tidak ditemukan');
        $posTable = strpos($js, 'const table = new Tabulator(mountEl()', $posAwal);
        $this->assertNotFalse($posTable);
        $badan = substr($js, $posAwal, $posTable - $posAwal);

        // Kunci yang baru dipasang WAJIB dihitung dari PERSIST_ID yang sedang
        // aktif (bukan literal terpisah yang bisa lupa disinkronkan).
        $this->assertStringContainsString("const kunciBaruTabulator = 'tabulator-' + PERSIST_ID + '-columns';", $badan);

        // Kondisi if() di dalam loop penghapusan WAJIB memeriksa KEDUANYA:
        // berpola milik kita (awalanKunciKita) DAN BUKAN kunci yang baru saja
        // dipasang (k !== kunciBaruTabulator) — dipersempit ke badan loop
        // for(...) saja supaya assertion menggigit tepat pada baris kondisi,
        // bukan sekadar menemukan literal 'kunciBaruTabulator' di komentar.
        $posFor = strpos($badan, 'for (let i = localStorage.length - 1; i >= 0; i--) {');
        $this->assertNotFalse($posFor, 'loop penghapusan kunci basi tidak ditemukan');
        $posTutupFor = strpos($badan, 'localStorage.removeItem(k);', $posFor);
        $this->assertNotFalse($posTutupFor);
        $badanLoop = substr($badan, $posFor, $posTutupFor - $posFor);

        $this->assertStringContainsString('awalanKunciKita', $badanLoop, 'penyaringan pola milik kita hilang dari kondisi penghapusan');
        $this->assertStringContainsString('k !== kunciBaruTabulator', $badanLoop, 'penjaga "bukan kunci yang baru dipasang" hilang — risiko kunci lebar aktif ikut terhapus setiap page load');
    }

    /**
     * Task 6: pembayaran pindah ke modal bersama (partial + JS bersama), menggantikan
     * modal/JS inline ~868 baris di dashboardPembayaran.blade.php.
     */
    public function test_pembayaran_memakai_modal_bersama(): void
    {
        $user = User::factory()->create(['role' => 'pembayaran']);
        $res = $this->actingAs($user)->get(route('documents.pembayaran.index'));
        $res->assertOk();

        $res->assertSee('js/column-customization.js', false);
        $res->assertSee('window.COLUMN_CUSTOMIZATION_CONFIG', false);
        $res->assertSee('openColumnCustomizationModal()', false);

        // Jejak modal inline lama benar-benar lenyap.
        $res->assertDontSee('openColumnModal()', false);
        $res->assertDontSee('toggleColumnSelection(', false);
        $res->assertDontSee('applyTemplateAgenda', false);
        $res->assertDontSee('pembayaran_columns', false);
        $res->assertDontSee('enable_customization', false);

        // Fungsi non-modal di blok yang sama WAJIB selamat.
        $res->assertSee('function setViewMode', false);
        $res->assertSee('function refreshPembayaranTable', false);
        $res->assertSee('function changePerPage', false);
        $res->assertSee('function toggleVendorGroup', false);
    }

    /** Nol definisi ganda: tiap nama fungsi bentrok hanya boleh muncul sekali. */
    /**
     * Review akhir branch (item 4, 2026-07-28): daftar 4 nama ini ditulis di
     * spec SEBELUM logika tab Beku dipindah ke column-customization.js —
     * permukaan bentrok tumbuh jadi 9 fungsi (5 tambahan lahir dari situ:
     * switchColumnTab/getFrozenState/setFrozenState/renderFrozenTab/
     * renderFrozenWarning) tapi daftar penjaga tidak ikut tumbuh. Turut
     * ditambah 3 identifier top-level (FROZEN_WIDTH_MAP/PINNED_LEFT_COLUMNS/
     * selectedColumnsOrder) yang risikonya LEBIH PARAH dari sekadar "fungsi
     * inline menimpa versi bersama": const/let terdeklarasi ganda di scope
     * atas yang sama (dua <script> classic berbagi lexical scope global utk
     * let/const) melempar SyntaxError dan MEMATIKAN SELURUH berkas bersama,
     * bukan cuma satu fungsi.
     */
    public function test_pembayaran_tidak_punya_definisi_fungsi_ganda(): void
    {
        $user = User::factory()->create(['role' => 'pembayaran']);
        $html = $this->actingAs($user)->get(route('documents.pembayaran.index'))->getContent();

        foreach ([
            'selectAllColumns', 'removeAllColumns', 'updateSelectedCount', 'saveColumnCustomization',
            'switchColumnTab', 'getFrozenState', 'setFrozenState', 'renderFrozenTab', 'renderFrozenWarning',
        ] as $nama) {
            $this->assertSame(
                0,
                substr_count($html, 'function ' . $nama . '('),
                "Definisi inline {$nama}() masih ada — akan menimpa versi bersama tanpa error."
            );
        }

        // 3 identifier top-level (bentuk asli: FROZEN_WIDTH_MAP/PINNED_LEFT_COLUMNS
        // = const, selectedColumnsOrder = let) — dicek KETIGA bentuk deklarasi
        // (const/let/var) sekaligus, bukan cuma bentuk aslinya, karena redeclare
        // dgn kata kunci APA PUN tetap SyntaxError di scope atas yang sama.
        foreach (['FROZEN_WIDTH_MAP', 'PINNED_LEFT_COLUMNS', 'selectedColumnsOrder'] as $nama) {
            foreach (['const', 'let', 'var'] as $kw) {
                $this->assertSame(
                    0,
                    substr_count($html, $kw . ' ' . $nama),
                    "Deklarasi ganda '{$kw} {$nama}' di script classic pembayaran — akan melempar SyntaxError & mematikan seluruh berkas bersama."
                );
            }
        }
    }

    /**
     * Item 6a (review akhir branch, 2026-07-28): pemisahan persistenceID
     * antar-role (lihat hashSusunanKolom + PERSIST_ID_PREFIX di
     * document-tabulator.js) bersandar SEPENUHNYA pada CFG.mountId yang
     * berbeda per role. Program BERIKUTNYA yang sudah tercatat di roadmap
     * CLAUDE.md §7 ("satukan 4 view Tabulator jadi 1 view + config") adalah
     * yang PALING mungkin menyeragamkan mountId tanpa sadar — begitu itu
     * terjadi tanpa jaring ini, kelima role kembali berbagi satu kunci
     * localStorage dan bug pencemaran antar-role (ditutup Task 5) hidup lagi
     * tanpa suara.
     */
    public function test_mountid_berbeda_di_kelima_halaman_role(): void
    {
        $cases = [
            ['role' => 'operator',        'route' => 'documents.index'],
            ['role' => 'akutansi',        'route' => 'documents.akutansi.index'],
            ['role' => 'perpajakan',      'route' => 'documents.perpajakan.index'],
            ['role' => 'team_verifikasi', 'route' => 'documents.verifikasi.index'],
            ['role' => 'pembayaran',      'route' => 'documents.pembayaran.index'],
        ];

        $awalan = 'window.DOCUMENT_TABULATOR_CONFIG = ';
        $mountIds = [];
        foreach ($cases as $c) {
            $user = User::factory()->create(['role' => $c['role']]);
            $html = $this->actingAs($user)->get(route($c['route']))->getContent();

            $pos = strpos($html, $awalan);
            $this->assertNotFalse($pos, "window.DOCUMENT_TABULATOR_CONFIG tidak ditemukan di role {$c['role']}");
            $akhir = strpos($html, '</script>', $pos);
            $this->assertNotFalse($akhir, "penutup </script> tidak ditemukan di role {$c['role']}");
            $jsonMentah = rtrim(trim(substr($html, $pos + strlen($awalan), $akhir - $pos - strlen($awalan))), ';');

            $config = json_decode($jsonMentah, true);
            $this->assertIsArray($config, "DOCUMENT_TABULATOR_CONFIG role {$c['role']} gagal di-decode dari HTML");
            $this->assertArrayHasKey('mountId', $config, "kunci 'mountId' tidak ada di config role {$c['role']}");
            $mountIds[$c['role']] = $config['mountId'];
        }

        $this->assertSame(
            count($mountIds),
            count(array_unique($mountIds)),
            'mountId TIDAK unik antar role — pencemaran localStorage antar-role akan hidup lagi: ' . json_encode($mountIds)
        );
    }
}
