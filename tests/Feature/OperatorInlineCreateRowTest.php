<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint `POST /documents/inline-create` (name `documents.inline-create`).
 * Selain tetap membalas `html` (view lama, fase fallback), balasan kini WAJIB
 * menyertakan objek `row` hasil OperatorDocumentRow untuk konsumsi Tabulator.
 */
class OperatorInlineCreateRowTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    public function test_inline_create_balas_objek_row(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson(route('documents.inline-create'), ['nomor_agenda' => '9001_2026']);

        $response->assertOk();

        $this->assertTrue($response->json('success'));
        $this->assertNotNull($response->json('id'));
        $this->assertSame('9001_2026', $response->json('row.nomor_agenda'));
        $this->assertSame('draft', $response->json('row.display_status.code'));
        $this->assertTrue($response->json('row.can_edit'));
        $this->assertNotNull($response->json('row.handler_options'));
    }

    public function test_inline_create_tetap_balas_html(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson(route('documents.inline-create'), ['nomor_agenda' => '9002_2026']);

        $response->assertOk();

        $html = $response->json('html');
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }

    public function test_inline_create_duplikat_422(): void
    {
        Dokumen::create([
            'nomor_agenda'    => '9003_2026',
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);

        $response = $this->actingAs($this->operator())
            ->postJson(route('documents.inline-create'), ['nomor_agenda' => '9003_2026']);

        $response->assertStatus(422);
    }
}
