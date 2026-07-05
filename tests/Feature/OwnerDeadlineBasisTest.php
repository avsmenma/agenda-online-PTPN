<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Q9: keterlambatan dihitung dari TANGGAL MASUK KE ROLE (received_at), bukan
 * tanggal dibuat (created_at).
 *
 * Mengunci perbaikan KPI "Ringkasan Hari Ini" di god-view (OwnerDashboardController@home):
 * dokumen yang dibuat lama TAPI baru masuk ke role saat ini TIDAK boleh dihitung
 * terlambat. Dengan logika lama (created_at), ketiga dokumen di bawah — semua
 * dibuat 10 hari lalu — akan salah dihitung terlambat=3. Dengan anchor role
 * (received_at) hasilnya terlambat=1, mendekati=1.
 */
class OwnerDeadlineBasisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /** Dokumen dibuat 10 hari lalu, kini di perpajakan, received_at diatur. */
    private function buatDokumenDiPerpajakan(string $nomor, \Carbon\Carbon $receivedAt): Dokumen
    {
        $dokumen = Dokumen::create([
            'nomor_agenda'    => $nomor,
            'created_by'      => 'operator',
            'current_handler' => 'perpajakan',
            'status'          => 'sent_to_perpajakan',
            'tanggal_masuk'   => now()->subDays(10),
            'bulan'           => now()->translatedFormat('F'),
            'tahun'           => (string) now()->year,
        ]);

        // Paksa created_at ke masa lalu tanpa menyentuh timestamps lain.
        Dokumen::where('id', $dokumen->id)->update(['created_at' => now()->subDays(10)]);

        // Waktu masuk ke role perpajakan (anchor keterlambatan yang benar).
        $dokumen->setDataForRole('perpajakan', ['received_at' => $receivedAt]);

        return $dokumen->fresh();
    }

    public function test_terlambat_dihitung_dari_masuk_role_bukan_created_at(): void
    {
        // A: baru masuk role hari ini → BUKAN terlambat, BUKAN mendekati.
        $this->buatDokumenDiPerpajakan('AG-Q9-A', now());
        // B: sudah 5 hari di role → TERLAMBAT (> 3 hari).
        $this->buatDokumenDiPerpajakan('AG-Q9-B', now()->subDays(5));
        // C: 2 hari di role → MENDEKATI (antara 1–3 hari).
        $this->buatDokumenDiPerpajakan('AG-Q9-C', now()->subDays(2));

        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get(route('owner.home'));
        $response->assertOk();

        $hariIni = $response->viewData('hariIni');

        // Dengan anchor created_at (lama) hasilnya akan terlambat=3, mendekati=0.
        $this->assertSame(1, $hariIni['terlambat'], 'Hanya dokumen B (5 hari di role) yang terlambat');
        $this->assertSame(1, $hariIni['mendekati'], 'Hanya dokumen C (2 hari di role) yang mendekati');
    }
}
