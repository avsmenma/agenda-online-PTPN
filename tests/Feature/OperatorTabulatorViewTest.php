<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji cabang flag pada DokumenController@index:
 * - default (tanpa flag) menyajikan view Tabulator (daftarDokumenTabulator) + memuat aset dist.
 * - ?classic=1 menyajikan view lama (daftarDokumen) sebagai fallback.
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

    /**
     * CLAUDE.md §8: dobel-klik kini memulai inline edit, sehingga modal Detail
     * Dokumen HARUS punya jalur lain. Tombol toolbar inilah satu-satunya jalur —
     * bila hilang, detail dokumen tidak bisa dibuka sama sekali dari tabel.
     */
    public function test_toolbar_menyediakan_tombol_detail_baris_aktif(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('id="btnDetailBarisAktif"', false);
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

    public function test_flag_classic_menyajikan_view_lama(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        $response->assertSee('id="btnTambahBarisInline"', false);
        $response->assertDontSee('operatorTabulatorTable', false);
    }
}
