<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint JSON `GET /documents/data` (name `documents.data`) yang
 * dipakai Tabulator untuk progressive load pada daftar dokumen Operator.
 * Endpoint memakai ulang buildOperatorQuery() + OperatorDocumentRow dan
 * membalas struktur {last_page, total, data:[...]}.
 */
class OperatorDatatableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // buildOperatorQuery memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX)
        // pada ORDER BY nomor_agenda — tidak tersedia di SQLite. Daftarkan polyfill.
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

    private function buatDokumen(int $index, int $tahun = 2026): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => $index . '_' . $tahun,
            'bulan'           => 'Juli',
            'tahun'           => $tahun,
            'tanggal_masuk'   => $tahun . '-07-01',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);
    }

    public function test_endpoint_data_balas_struktur_progressive_load(): void
    {
        $this->buatDokumen(1);
        $this->buatDokumen(2);

        $response = $this->actingAs($this->operator())
            ->getJson(route('documents.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'last_page',
                'total',
                'data' => [[
                    'id',
                    'nomor_agenda',
                    'display_status' => ['code', 'label', 'variant'],
                    'can_edit',
                    'handler_options',
                ]],
            ]);

        $this->assertSame(2, $response->json('total'));
    }

    public function test_filter_tahun_dihormati(): void
    {
        $this->buatDokumen(1, 2025);
        $dok2026 = $this->buatDokumen(2, 2026);

        $response = $this->actingAs($this->operator())
            ->getJson(route('documents.data', ['year' => 2026]));

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertSame($dok2026->id, $response->json('data.0.id'));
        $this->assertSame('2_2026', $response->json('data.0.nomor_agenda'));
    }

    public function test_paginasi_size_dan_page(): void
    {
        for ($i = 1; $i <= 150; $i++) {
            $this->buatDokumen($i);
        }

        $response = $this->actingAs($this->operator())
            ->getJson(route('documents.data', ['size' => 100, 'page' => 2]));

        $response->assertOk();
        $this->assertSame(150, $response->json('total'));
        $this->assertSame(2, $response->json('last_page'));
        $this->assertCount(50, $response->json('data'));
    }

    public function test_non_operator_ditolak(): void
    {
        $pembayaran = User::factory()->create(['role' => 'pembayaran']);

        $response = $this->actingAs($pembayaran)
            ->getJson(route('documents.data'));

        $response->assertStatus(403);
    }
}
