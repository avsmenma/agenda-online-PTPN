<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menguji index() Pembayaran menyajikan view Tabulator. Renderer bespoke lama
 * + cabang ?classic sudah dihapus permanen pasca-QA (Rollout 4, Task 6).
 * Mirror AkutansiTabulatorSwitchTest/PerpajakanTabulatorSwitchTest/VerifikasiTabulatorSwitchTest.
 */
class PembayaranTabulatorSwitchTest extends TestCase
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
            // index() menghitung dropdown filter Tahun via selectRaw('YEAR(created_at) as year')
            // — YEAR() bukan fungsi SQLite bawaan, jadi diregistrasikan di sini.
            $pdo->sqliteCreateFunction('YEAR', fn ($v) => $v ? (int) substr((string) $v, 0, 4) : null, 1);
        }
    }

    private function pembayaran(): User
    {
        return User::factory()->create(['role' => 'pembayaran']);
    }

    private function buatDokumen(string $nomorAgenda, array $overrides = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'    => $nomorAgenda,
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'sent_to_pembayaran',
            'created_by'      => 'operator',
            'current_handler' => 'pembayaran',
        ], $overrides));
    }

    public function test_default_menyajikan_view_tabulator(): void
    {
        $this->buatDokumen('1');

        $response = $this->actingAs($this->pembayaran())->get(route('documents.pembayaran.index'));

        $response->assertOk()
            ->assertSee('pembayaranTabulatorTable', false)
            ->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }

    public function test_flag_classic_diabaikan_menyajikan_tabulator(): void
    {
        // Flag ?classic tak lagi berpengaruh — view legacy dihapus, selalu Tabulator.
        $this->buatDokumen('2');

        $response = $this->actingAs($this->pembayaran())->get(route('documents.pembayaran.index', ['classic' => 1]));

        $response->assertOk()
            ->assertSee('pembayaranTabulatorTable', false)
            ->assertSee('DOCUMENT_TABULATOR_CONFIG', false);
    }
}
