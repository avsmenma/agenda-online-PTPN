<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint JSON `GET documents/akutansi/data` (name `documents.akutansi.data`)
 * yang dipakai Tabulator akutansi untuk progressive load. Endpoint memakai ulang
 * buildAkutansiQuery() (sumber tunggal dgn dokumens()) + AkutansiDocumentRow dan
 * membalas struktur {last_page, total, data:[...]}.
 */
class AkutansiDatatableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // buildAkutansiQuery() memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX, LPAD)
        // pada ORDER BY nomor_agenda — tidak tersedia di SQLite. Daftarkan polyfill
        // (pola sama seperti AkutansiHapusAksiMatiTest / OperatorDatatableTest).
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('regexp', function ($pattern, $value) {
                return preg_match('/' . $pattern . '/u', (string) $value) ? 1 : 0;
            });
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);
                return implode($delim, array_slice($parts, 0, (int) $count));
            });
            $pdo->sqliteCreateFunction('lpad', function ($str, $len, $pad) {
                return str_pad((string) $str, (int) $len, (string) $pad, STR_PAD_LEFT);
            });
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

    public function test_endpoint_data_akutansi_mengembalikan_bentuk_json_datatable(): void
    {
        $this->buatDokumen('1');

        $response = $this->actingAs($this->akutansi())->getJson(route('documents.akutansi.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'last_page',
                'total',
                'data' => [['id', 'nomor_agenda', 'status_badge', 'deadline', 'handler_options']],
            ]);
    }

    public function test_baris_memuat_objek_status_badge_dan_deadline(): void
    {
        $this->buatDokumen('2', ['current_handler' => 'operator', 'status' => 'draft']);

        $response = $this->actingAs($this->akutansi())->getJson(route('documents.akutansi.data'));

        $first = $response->json('data.0');
        $this->assertArrayHasKey('class', $first['status_badge']);
        $this->assertArrayHasKey('variant', $first['deadline']);
    }

    public function test_endpoint_data_menolak_tamu(): void
    {
        $this->getJson(route('documents.akutansi.data'))->assertUnauthorized();
    }
}
