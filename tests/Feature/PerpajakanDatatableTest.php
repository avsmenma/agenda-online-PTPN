<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji endpoint JSON `GET documents/perpajakan/data` (name
 * `documents.perpajakan.data`) yang dipakai Tabulator perpajakan untuk
 * progressive load. Endpoint memakai ulang buildPerpajakanQuery() (sumber
 * tunggal dgn dokumens()) + PerpajakanDocumentRow dan membalas struktur
 * {last_page, total, data:[...]}.
 */
class PerpajakanDatatableTest extends TestCase
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

    private function perpajakan(): User
    {
        return User::factory()->create(['role' => 'perpajakan']);
    }

    public function test_endpoint_data_mengembalikan_bentuk_datatable(): void
    {
        Dokumen::create(['nomor_agenda' => '1', 'current_handler' => 'perpajakan', 'status' => 'sent_to_perpajakan', 'nilai_rupiah' => 1000]);

        $this->actingAs($this->perpajakan())->getJson(route('documents.perpajakan.data'))
            ->assertOk()
            ->assertJsonStructure(['last_page', 'total', 'data' => [['id', 'nomor_agenda', 'status_badge', 'deadline', 'handler_options']]]);
    }

    public function test_baris_memuat_objek_status_badge_dan_deadline(): void
    {
        Dokumen::create(['nomor_agenda' => '2', 'current_handler' => 'operator', 'status' => 'draft', 'nilai_rupiah' => 1000]);
        $resp = $this->actingAs($this->perpajakan())->getJson(route('documents.perpajakan.data'));
        $first = $resp->json('data.0');
        $this->assertArrayHasKey('class', $first['status_badge']);
        $this->assertArrayHasKey('variant', $first['deadline']);
    }

    public function test_endpoint_menolak_tamu(): void
    {
        $this->getJson(route('documents.perpajakan.data'))->assertUnauthorized();
    }
}
