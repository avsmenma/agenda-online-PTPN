<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerpajakanTabulatorSwitchTest extends TestCase
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

    private function perpajakan(): User { return User::factory()->create(['role' => 'perpajakan']); }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $this->actingAs($this->perpajakan())->get(route('documents.perpajakan.index'))
            ->assertOk()->assertSee('perpajakanTabulatorTable', false)->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_classic_menyajikan_view_legacy(): void
    {
        $this->actingAs($this->perpajakan())->get(route('documents.perpajakan.index', ['classic' => 1]))
            ->assertOk()->assertDontSee('perpajakanTabulatorTable', false);
    }
}
