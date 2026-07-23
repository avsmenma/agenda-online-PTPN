<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji cabang view pada DokumenController@index:
 * - operator selalu disajikan view Tabulator (daftarDokumenTabulator) + memuat aset dist.
 * - flag ?classic sudah tidak berpengaruh (jalur classic dimatikan).
 */
class OperatorTabulatorViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // index() memakai buildOperatorQuery() yang memakai fungsi MySQL
        // (REGEXP, SUBSTRING_INDEX) di ORDER BY nomor_agenda — polyfill untuk SQLite.
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('regexp', function ($pattern, $value) {
                return preg_match('/' . $pattern . '/u', (string) $value) ? 1 : 0;
            });
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);
                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('operatorTabulatorTable', false);
        $response->assertSee('vendor/tabulator/tabulator.min.js', false);
        $response->assertSee('bulanList', false);
        $response->assertSee('bagian', false);
    }

    public function test_view_memakai_config_global_dan_berkas_document_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('window.DOCUMENT_TABULATOR_CONFIG', false);
        $response->assertSee('js/document-tabulator.js', false);
        $response->assertDontSee('OPERATOR_TABULATOR_CONFIG', false);
        $response->assertDontSee('operator-tabulator.js', false);
    }

    /**
     * Fitur Detail Dokumen dihapus atas permintaan user (2026-07-22) — modal
     * detail beserta tombol pembukanya dicabut seluruhnya dari view Tabulator.
     * Tombol toolbar Hapus baris aktif kini satu-satunya jalur menghapus dokumen
     * dari tabel (menggantikan tombol Hapus yang dulu ada di footer modal Detail).
     */
    public function test_toolbar_menyediakan_tombol_hapus_baris_aktif(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('id="btnHapusBarisAktif"', false);
    }

    /**
     * Jaring pengaman regresi untuk penghapusan fitur Detail (2026-07-22): tanpa
     * assertion negatif ini, modal/tombol Detail bisa diam-diam kembali muncul
     * (mis. lewat revert sebagian) tanpa ada test yang menangkapnya.
     */
    public function test_modal_dan_tombol_detail_tidak_lagi_muncul(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertDontSee('viewDocumentModal', false);
        $response->assertDontSee('id="btnDetailBarisAktif"', false);
    }

    /**
     * Spec 2026-07-22: tema tabel menyetel font-family "Source Sans Pro". Webfont-nya
     * sengaja dimuat di view ini, BUKAN di layouts/app.blade.php, agar tipografi role
     * lain tidak ikut berubah. Tanpa link ini font diam-diam jatuh ke Arial dan
     * restyle terlihat gagal tanpa error apa pun.
     */
    public function test_view_memuat_webfont_source_sans_pro(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('family=Source+Sans+Pro', false);
    }

    public function test_flag_classic_diabaikan_menyajikan_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        // Flag ?classic tak lagi berpengaruh — tabel classic dihapus, selalu Tabulator.
        $response->assertSee('operatorTabulatorTable', false);
        $response->assertDontSee('id="btnTambahBarisInline"', false);
    }

    /**
     * Fix 1 (review 2026-07-22): tabel Tabulator tak lagi mengirim sort/order, tapi
     * tabel klasik (?classic=1) masih menulis operator_sort_column/operator_sort_order
     * ke sesi. Tanpa dibersihkan, sesi lama mengunci urutan tabel Tabulator selamanya
     * tanpa ada UI untuk membatalkannya. Mengunjungi view Tabulator harus membersihkan
     * kedua kunci sesi tersebut.
     */
    public function test_kunjungan_tabel_tabulator_membersihkan_sesi_sort_lama(): void
    {
        $response = $this->actingAs($this->operator())
            ->withSession([
                'operator_sort_column' => 'nomor_spp',
                'operator_sort_order'  => 'asc',
            ])
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSessionMissing('operator_sort_column');
        $response->assertSessionMissing('operator_sort_order');
    }

    /**
     * Flag ?classic tak lagi punya jalur sort sendiri (tabel classic dihapus), jadi
     * mengunjungi index dengan ?classic=1 pun tetap membersihkan sesi sort lama —
     * tidak ada lagi kondisi yang mempertahankannya.
     */
    public function test_flag_classic_diabaikan_tetap_membersihkan_sesi_sort(): void
    {
        $response = $this->actingAs($this->operator())
            ->withSession([
                'operator_sort_column' => 'nomor_spp',
                'operator_sort_order'  => 'asc',
            ])
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        $response->assertSessionMissing('operator_sort_column');
        $response->assertSessionMissing('operator_sort_order');
    }

    /**
     * nginx menyajikan css/js lokal dengan `Cache-Control: immutable`
     * (agenda:21-26), jadi nama file yang stabil membuat browser tak pernah
     * mengambil versi baru. View ini wajib memuat tabulator-agenda.css lewat
     * Asset::versioned() sehingga URL-nya membawa `?v=<mtime>`.
     */
    public function test_view_memuat_css_tabulator_dengan_query_string_versi(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee(
            \App\Support\Asset::versioned('css/tabulator-agenda.css'),
            false
        );
    }

    public function test_view_menyetel_mountid_dan_kelas_doc_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        // Elemen mount membawa kelas bersama (target CSS) + tetap id instance operator.
        $response->assertSee('id="operatorTabulatorTable" class="doc-tabulator"', false);
        // Engine membaca id mount dari config.
        $response->assertSee('mountId', false);
    }
}
