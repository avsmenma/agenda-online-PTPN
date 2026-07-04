<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Characterization test (golden-master) untuk alur AUTO-FORWARD.
 *
 * Alur bisnis paling rawan & paling sering regresi:
 *   Dokumen ditandai "sudah dibayar" (via tanggal_dibayar / status_pembayaran)
 *   → DokumenObserver memicu AutoForwardDokumenService
 *   → dokumen diteruskan sampai ke role Pembayaran (melewati tahap antara).
 *
 * Test ini MENGUNCI perilaku yang ada sekarang supaya refaktor mendatang
 * (Fase 3/4) tidak diam-diam merusaknya. Bukan menilai benar/salah desain —
 * hanya menangkap perilaku aktual.
 */
class AutoForwardDokumenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed kode role yang dipakai workflow ke tabel `roles`.
     *
     * CATATAN PENTING (diverifikasi empiris, bukan asumsi):
     * - Kelima kode workflow (operator, team_verifikasi, perpajakan, akutansi,
     *   pembayaran) SUDAH dihasilkan dengan benar oleh rantai migrasi: create_roles
     *   menyeed kode lama (ibuA/ibuB/...) lalu standardize_role_names (2026_01_24)
     *   me-rename-nya. Migrasi itu TIDAK di-guard SQLite, jadi berlaku di test juga.
     * - Yang benar-benar wajib di-seed di sini adalah role 'system'. Saat auto-forward
     *   melewati tahap antara, AutoForwardDokumenService memanggil
     *   sendToRoleInbox($toRole, 'system'); Dokumen::sendToRoleInbox lalu mencatat
     *   display_status PENGIRIM ke dokumen_role_data dengan role_code='system'.
     *   Kolom dokumen_role_data.role_code punya FK ke roles.code, sedangkan TIDAK ADA
     *   migrasi/seeder yang membuat role 'system'. Tanpa baris ini, auto-forward gagal
     *   dengan "FOREIGN KEY constraint failed" (insert dokumen_role_data role_code=system).
     *   Produksi hanya bekerja bila baris 'system' disisipkan di luar migrasi — gap
     *   reproducibility nyata (lihat catatan Fase 2 untuk keputusan perbaikannya).
     * Kita seed keenam kode agar test deterministik & FK selalu lolos.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $codes = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran', 'system'];
        foreach ($codes as $i => $code) {
            DB::table('roles')->updateOrInsert(
                ['code' => $code],
                ['name' => ucfirst($code), 'sequence' => $i + 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function buatDokumenOperator(string $nomorAgenda): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => $nomorAgenda,
            'created_by'      => 'operator',
            'current_handler' => 'operator',
            'status'          => 'draft',
            'tanggal_masuk'   => now(),
            'bulan'           => now()->translatedFormat('F'),
            'tahun'           => (string) now()->year,
        ]);
    }

    /** Observer: mengisi tanggal_dibayar otomatis menandai status_pembayaran = sudah_dibayar. */
    public function test_mengisi_tanggal_dibayar_menandai_sudah_dibayar(): void
    {
        Queue::fake(); // cegah SyncDokumenToCashBankJob berjalan di test

        $dokumen = $this->buatDokumenOperator('AG-AF-STATUS');
        $this->assertNull($dokumen->status_pembayaran);

        $dokumen->tanggal_dibayar = now();
        $dokumen->save();

        $this->assertSame('sudah_dibayar', $dokumen->fresh()->status_pembayaran);
    }

    /** Dokumen operator yang jadi "sudah dibayar" harus diteruskan sampai Pembayaran. */
    public function test_dokumen_sudah_dibayar_diteruskan_ke_pembayaran(): void
    {
        Queue::fake();

        $dokumen = $this->buatDokumenOperator('AG-AF-FWD');

        // Tandai dibayar → memicu observer → auto-forward
        $dokumen->tanggal_dibayar = now();
        $dokumen->save();

        $dokumen->refresh();

        // Perilaku yang dikunci:
        $this->assertSame('sudah_dibayar', $dokumen->status_pembayaran);
        $this->assertNotNull($dokumen->auto_forwarded_at, 'auto_forwarded_at harus terisi setelah forward');
        $this->assertSame('pembayaran', $dokumen->current_handler, 'current_handler harus pindah ke pembayaran');

        // Jejak workflow ke pembayaran terbentuk
        $this->assertDatabaseHas('dokumen_statuses', [
            'dokumen_id' => $dokumen->id,
            'role_code'  => 'pembayaran',
        ]);
    }

    /** Idempotensi: auto-forward tidak berjalan dua kali (guard auto_forwarded_at). */
    public function test_auto_forward_idempoten(): void
    {
        Queue::fake();

        $dokumen = $this->buatDokumenOperator('AG-AF-IDEM');
        $dokumen->tanggal_dibayar = now();
        $dokumen->save();

        $waktuForwardPertama = $dokumen->fresh()->auto_forwarded_at;
        $this->assertNotNull($waktuForwardPertama);

        // Update lagi (mis. edit field lain) — tidak boleh forward ulang / reset
        $dokumen = $dokumen->fresh();
        $dokumen->uraian_spp = 'diedit setelah dibayar';
        $dokumen->save();

        $this->assertEquals(
            (string) $waktuForwardPertama,
            (string) $dokumen->fresh()->auto_forwarded_at,
            'auto_forwarded_at tidak boleh berubah pada update berikutnya'
        );
        $this->assertSame('pembayaran', $dokumen->fresh()->current_handler);
    }
}
