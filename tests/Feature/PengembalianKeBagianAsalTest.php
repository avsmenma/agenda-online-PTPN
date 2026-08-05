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
                'return_reason'  => 'Nilai rupiah tidak cocok dengan lampiran faktur.',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('returned_to_bidang', $dokumen->fresh()->status);
    }

    public function test_alasan_pengguna_tersimpan_bukan_kalimat_tetap(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $alasan = 'Lampiran SPP belum ditandatangani oleh pejabat berwenang.';

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => $alasan,
            ])
            ->assertOk();

        $this->assertSame($alasan, $dokumen->fresh()->return_reason);
    }

    public function test_pengembalian_tanpa_alasan_ditolak(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }

    public function test_alasan_terlalu_pendek_ditolak(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'salah',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }

    public function test_alasan_melebihi_batas_ditolak(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => str_repeat('a', 1001),
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }

    public function test_perpindahan_bukan_ke_bagian_tidak_butuh_alasan(): void
    {
        // Guard alasan HANYA untuk target Bagian — jangan sampai memblokir alur maju
        // (mis. verifikasi -> perpajakan) yang tidak ada urusannya dengan pengembalian.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDiVerifikasi('KEU');

        $this->actingAs($this->verifikasi())
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'perpajakan',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
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
