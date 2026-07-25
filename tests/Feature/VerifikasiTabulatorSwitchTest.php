<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji dokumens() Team Verifikasi menyajikan view Tabulator. View legacy
 * + cabang ?classic sudah dihapus permanen pasca-QA (Rollout 3, Task 6).
 * Mirror AkutansiTabulatorSwitchTest/PerpajakanTabulatorSwitchTest.
 */
class VerifikasiTabulatorSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('REGEXP', fn ($p, $v) => preg_match('/' . $p . '/', (string) $v) ? 1 : 0, 2);
            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', fn ($s, $d, $c) => implode($d, array_slice(explode($d, (string) $s), 0, (int) $c)), 3);
            $pdo->sqliteCreateFunction('LPAD', fn ($s, $l, $p) => str_pad((string) $s, (int) $l, (string) $p, STR_PAD_LEFT), 3);
        }
    }

    private function verifikator(): User
    {
        return User::factory()->create(['role' => 'team_verifikasi']);
    }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $this->actingAs($this->verifikator())->get(route('documents.verifikasi.index'))
            ->assertOk()->assertSee('verifikasiTabulatorTable', false)->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_flag_classic_diabaikan_menyajikan_tabulator(): void
    {
        // Flag ?classic tak lagi berpengaruh — view legacy dihapus, selalu Tabulator.
        $this->actingAs($this->verifikator())->get(route('documents.verifikasi.index', ['classic' => 1]))
            ->assertOk()->assertSee('verifikasiTabulatorTable', false);
    }
}
