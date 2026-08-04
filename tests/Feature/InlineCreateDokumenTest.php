<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint inline-create: operator membuat baris dokumen draft
 * langsung dari daftar dokumen hanya dengan Nomor Agenda.
 */
class InlineCreateDokumenTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    public function test_operator_dapat_membuat_baris_via_nomor_agenda(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-001']);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'id', 'row'])
            ->assertJsonMissingPath('html');

        $this->assertDatabaseHas('dokumens', [
            'nomor_agenda'    => 'AG-001',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);

        $dokumen = Dokumen::where('nomor_agenda', 'AG-001')->first();
        $this->assertNotNull($dokumen->tanggal_masuk);
        $this->assertNotEmpty($dokumen->bulan);
        $this->assertSame((string) now()->year, (string) $dokumen->tahun);
    }

    public function test_nomor_agenda_duplikat_ditolak(): void
    {
        Dokumen::create(['nomor_agenda' => 'AG-DUP', 'status' => 'draft']);

        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-DUP']);

        $response->assertStatus(422)->assertJsonValidationErrors('nomor_agenda');
    }

    public function test_nomor_agenda_kosong_dinomori_otomatis(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => '']);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame('1_' . now()->year, $response->json('row.nomor_agenda'));
    }

    public function test_tanpa_kunci_nomor_agenda_dinomori_otomatis(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', []);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame('1_' . now()->year, $response->json('row.nomor_agenda'));
    }

    public function test_penomoran_otomatis_melanjutkan_nomor_tertinggi_tahun_berjalan(): void
    {
        $tahun = now()->year;
        Dokumen::create(['nomor_agenda' => '7_' . $tahun, 'status' => 'draft']);
        Dokumen::create(['nomor_agenda' => '3_' . $tahun, 'status' => 'draft']);
        // Tahun lain tidak boleh ikut dihitung.
        Dokumen::create(['nomor_agenda' => '99_' . ($tahun - 1), 'status' => 'draft']);

        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', []);

        $response->assertStatus(200);
        $this->assertSame('8_' . $tahun, $response->json('row.nomor_agenda'));
    }

    public function test_nomor_agenda_manual_tetap_dihormati(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-MANUAL']);

        $response->assertStatus(200);
        $this->assertSame('AG-MANUAL', $response->json('row.nomor_agenda'));
    }

    public function test_nomor_agenda_terlalu_panjang_ditolak(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => str_repeat('A', 256)]);

        $response->assertStatus(422)->assertJsonValidationErrors('nomor_agenda');
    }

    public function test_non_operator_diblokir(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)
            ->post('/documents/inline-create', ['nomor_agenda' => 'AG-X']);

        $response->assertRedirect('/login');
    }
}
