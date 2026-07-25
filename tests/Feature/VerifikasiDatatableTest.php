<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji endpoint JSON `GET documents/verifikasi/data` (name
 * `documents.verifikasi.data`) yang dipakai Tabulator Team Verifikasi
 * (Rollout 3, Task 2). Endpoint memakai ulang buildVerifikasiQuery() (sumber
 * tunggal dgn dokumens()) + VerifikasiDocumentRow dan membalas {data:[...]}
 * (TANPA wrapper last_page/total — beda dgn Perpajakan/Akutansi, sesuai
 * kontrak Task 2 brief).
 */
class VerifikasiDatatableTest extends TestCase
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

    public function test_endpoint_data_verifikasi_mengembalikan_json(): void
    {
        Dokumen::create(['nomor_agenda' => '1', 'current_handler' => 'team_verifikasi', 'status' => 'sent_to_team_verifikasi', 'nilai_rupiah' => 1000]);

        $res = $this->actingAs($this->verifikator())->getJson(route('documents.verifikasi.data'));

        $res->assertOk()->assertJsonStructure(['data' => [['id', 'status_badge', 'deadline', 'handler']]]);
    }

    public function test_baris_memuat_objek_status_badge_dan_deadline(): void
    {
        Dokumen::create(['nomor_agenda' => '2', 'current_handler' => 'operator', 'status' => 'draft', 'nilai_rupiah' => 1000]);

        $resp = $this->actingAs($this->verifikator())->getJson(route('documents.verifikasi.data'));
        $first = $resp->json('data.0');

        $this->assertArrayHasKey('class', $first['status_badge']);
        $this->assertArrayHasKey('variant', $first['deadline']);
    }

    public function test_endpoint_menolak_tamu(): void
    {
        $this->getJson(route('documents.verifikasi.data'))->assertUnauthorized();
    }
}
