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

    public function test_flag_classic_menyajikan_view_lama(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        $response->assertSee('id="btnTambahBarisInline"', false);
        $response->assertDontSee('operatorTabulatorTable', false);
    }
}
