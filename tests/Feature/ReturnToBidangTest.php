<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Q11(a): saat dokumen dinilai TIDAK LAYAK, Team Verifikasi mengembalikannya ke
 * BIDANG/BAGIAN asal (keluar dari alur keuangan) dengan alasan.
 *
 * Mengunci perilaku returnToBidang (status returned_to_bidang) yang mewujudkan
 * maksud pemilik. Q11(b) (pengembalian antar-role keuangan) sudah dikunci di
 * InboxApprovalAuthorizationTest (routing reject perpajakan→verifikasi, dst).
 */
class ReturnToBidangTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function buatDokumenDiVerifikasi(string $nomor, string $bagian = 'SDM'): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => $nomor,
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
            'status'          => 'sedang diproses',
            'bagian'          => $bagian,
            'tanggal_masuk'   => now(),
            'bulan'           => now()->translatedFormat('F'),
            'tahun'           => (string) now()->year,
        ]);
    }

    /** Verifikasi mengembalikan dokumen ke bidang asal dengan alasan. */
    public function test_verifikasi_mengembalikan_ke_bidang(): void
    {
        $dokumen = $this->buatDokumenDiVerifikasi('AG-BID-OK', 'SDM');
        $user = User::factory()->create(['role' => 'team_verifikasi']);

        $response = $this->actingAs($user)->postJson(
            route('returns.verifikasi.to-bidang', $dokumen),
            ['bidang_return_reason' => 'Dokumen tidak layak, berkas pendukung kurang.']
        );

        $response->assertOk();

        $dokumen->refresh();
        $this->assertSame('returned_to_bidang', $dokumen->status);
        $this->assertSame('SDM', $dokumen->return_source);
        $this->assertNotNull($dokumen->returned_at);
        $this->assertSame('Dokumen tidak layak, berkas pendukung kurang.', $dokumen->return_reason);
    }

    /** Alasan pengembalian wajib (min 10 karakter). */
    public function test_pengembalian_ke_bidang_wajib_alasan(): void
    {
        $dokumen = $this->buatDokumenDiVerifikasi('AG-BID-NOREASON');
        $user = User::factory()->create(['role' => 'team_verifikasi']);

        $this->actingAs($user)->postJson(
            route('returns.verifikasi.to-bidang', $dokumen),
            ['bidang_return_reason' => 'pendek']
        )->assertStatus(422);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
    }

    /** Hanya dokumen yang sedang dipegang Verifikasi yang bisa dikembalikan ke bidang. */
    public function test_tidak_bisa_kembalikan_bila_bukan_di_verifikasi(): void
    {
        $dokumen = $this->buatDokumenDiVerifikasi('AG-BID-WRONG');
        $dokumen->update(['current_handler' => 'perpajakan']); // sudah pindah dari verifikasi

        $user = User::factory()->create(['role' => 'team_verifikasi']);

        $this->actingAs($user)->postJson(
            route('returns.verifikasi.to-bidang', $dokumen),
            ['bidang_return_reason' => 'Dokumen tidak layak, berkas pendukung kurang.']
        )->assertStatus(403);

        $this->assertSame('perpajakan', $dokumen->fresh()->current_handler);
    }
}
