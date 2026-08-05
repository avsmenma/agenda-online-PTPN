<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji penegakan sisi-server pada DocumentHandlerController::update():
 * dokumen hanya boleh dikembalikan ke bagian ASALNYA SENDIRI.
 *
 * Dropdown "Pengurus Dokumen" sudah dipangkas ke satu pilihan lewat
 * App\Support\HandlerOptions::forDokumen(), tapi opsi itu ditanam di data baris
 * yang dikirim ke klien — jadi bisa diakali. Tanpa guard ini pemangkasan dropdown
 * hanya kosmetik, dan "salah pilih bagian" yang jadi keluhan awal tetap mungkin.
 */
class PengembalianKeBagianAsalTest extends TestCase
{
    use RefreshDatabase;

    private function verifikasi(): User
    {
        return User::factory()->create(['role' => 'team_verifikasi']);
    }

    private function dokumenDiVerifikasi(?string $bagian): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Juli',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-07-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'bagian'          => $bagian,
        ]);
    }

    public function test_pengembalian_ke_bagian_asal_diterima(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('returned_to_bidang', $dokumen->fresh()->status);
    }

    public function test_pengembalian_ke_bagian_lain_ditolak(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        Bagian::create(['kode' => 'SDM', 'nama' => 'Sumber Daya Manusia']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_sdm',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonFragment([
                'message' => 'Dokumen hanya boleh dikembalikan ke bagian asalnya, yaitu Keuangan.',
            ]);

        // Dokumen tidak boleh bergerak sama sekali.
        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }

    public function test_dokumen_tanpa_bagian_tidak_bisa_dikembalikan_ke_bagian(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi(null);

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }
}
