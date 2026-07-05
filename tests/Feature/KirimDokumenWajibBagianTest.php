<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Hygiene: draft quick-add (inlineCreate) boleh dibuat TANPA bagian, tapi tak
 * boleh DIKIRIM ke alur keuangan tanpa bagian. Ini menutup celah agar dokumen
 * tidak lolos ke alur tanpa `bagian` (yang membuatnya tak terlihat di monitoring
 * role Bagian view-only). Penegakan ada di titik kirim (DocumentHandlerController),
 * bukan saat pembuatan stub — supaya UX quick-add tetap cepat.
 */
class KirimDokumenWajibBagianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function buatDraftOperator(?string $bagian = null): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => 'AG-WAJIB-BAGIAN',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
            'status'          => 'draft',
            'bagian'          => $bagian,
            'tanggal_masuk'   => now(),
            'bulan'           => now()->translatedFormat('F'),
            'tahun'           => (string) now()->year,
        ]);
    }

    public function test_tidak_bisa_kirim_ke_verifikasi_tanpa_bagian(): void
    {
        $dokumen = $this->buatDraftOperator(null);
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->patchJson(
            route('documents.handler.update', $dokumen),
            ['target_handler' => 'team_verifikasi']
        );

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        // Dokumen tetap di operator, tidak terkirim.
        $this->assertSame('operator', $dokumen->fresh()->current_handler);
    }

    public function test_bisa_kirim_setelah_bagian_diisi(): void
    {
        $dokumen = $this->buatDraftOperator('SDM');
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->patchJson(
            route('documents.handler.update', $dokumen),
            ['target_handler' => 'team_verifikasi']
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertSame('team_verifikasi', $dokumen->fresh()->current_handler);
    }
}
