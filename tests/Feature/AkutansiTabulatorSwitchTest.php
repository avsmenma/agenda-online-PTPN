<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji transisi Rollout 1: dokumens() akutansi menyajikan view Tabulator
 * secara default, dengan ?classic=1 tetap menyajikan view legacy untuk
 * banding QA (dihapus di Tugas 8 setelah QA lolos).
 */
class AkutansiTabulatorSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', function ($s, $d, $c) {
                return implode($d, array_slice(explode($d, (string) $s), 0, (int) $c));
            }, 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    private function buatDokumen(string $nomorAgenda, array $overrides = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomorAgenda,
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'sent_to_akutansi',
            'created_by'      => 'operator',
            'current_handler' => 'akutansi',
        ], $overrides));
    }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $this->buatDokumen('1');

        $response = $this->actingAs($this->akutansi())->get(route('documents.akutansi.index'));
        $response->assertOk()
            ->assertSee('akutansiTabulatorTable', false)
            ->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_classic_menyajikan_view_legacy(): void
    {
        $this->buatDokumen('2');

        $response = $this->actingAs($this->akutansi())->get(route('documents.akutansi.index', ['classic' => 1]));
        $response->assertOk()
            ->assertDontSee('akutansiTabulatorTable', false);
    }
}
