<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * TIDAK ada seed role manual di sini — sengaja.
     *
     * Test ini mengandalkan tabel `roles` yang dibangun sepenuhnya oleh rantai
     * migrasi (RefreshDatabase), sehingga sekaligus MEMBUKTIKAN gap 'system'
     * sudah tertutup:
     * - Kode workflow (operator, team_verifikasi, perpajakan, akutansi,
     *   pembayaran) dihasilkan create_roles -> standardize_role_names (tak
     *   di-guard SQLite, jadi berlaku di test).
     * - Role 'system' disediakan migrasi 2026_07_04_000001_add_system_role_to_
     *   roles_table. Ini WAJIB: auto-forward memanggil sendToRoleInbox($to,
     *   'system'); Dokumen::sendToRoleInbox mencatat display_status pengirim ke
     *   dokumen_role_data dengan role_code='system' (FK ke roles.code). Sebelum
     *   migrasi itu ada, test gagal "FOREIGN KEY constraint failed".
     *
     * Jika suatu saat migrasi 'system' terhapus/rusak, test ini akan MERAH lagi —
     * itulah gunanya tidak menyeed manual.
     */
    protected function setUp(): void
    {
        parent::setUp();
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
