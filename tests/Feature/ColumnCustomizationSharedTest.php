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
        }
    }

    public function test_partial_merender_modal_dan_jembatan_config(): void
    {
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
        $this->assertStringContainsString('function appendActiveFilterInputs', $js);
        // Baca data dari jembatan window, BUKAN @json Blade (file statis).
        $this->assertStringContainsString('COLUMN_CUSTOMIZATION_CONFIG', $js);
        $this->assertStringNotContainsString('@json', $js);
    }
}
