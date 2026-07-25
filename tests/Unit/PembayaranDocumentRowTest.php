<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\PembayaranDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji PembayaranDocumentRow — DTO baris tabel pembayaran (Tabulator,
 * Rollout 4) yang memindahkan pill status 3-state (port
 * getPembayaranComputedStatus() + renderPembayaranStatusPill(),
 * DashboardPembayaranController.php:1108-1138) dari Blade ke server, plus
 * aturan edit-anywhere (can_edit SELALU true) dan ketiadaan kolom deadline
 * (pembayaran tak punya deadline per-baris, beda dari akutansi/perpajakan/
 * verifikasi).
 *
 * Harness: Feature-style (Tests\TestCase + RefreshDatabase) — sama seperti
 * VerifikasiDocumentRowTest/AkutansiDocumentRowTest/PerpajakanDocumentRowTest,
 * karena DocumentRow::baseRow() memakai relasi (roleStatuses/dibayarKepadas/
 * dokumenPos) yang butuh DB sungguhan (lazy-load otomatis bila tak di-load
 * eksplisit — cukup untuk unit test, endpoint asli Task 2 wajib eager-load).
 *
 * Nama method uji dalam Bahasa Indonesia; variabel lokal dalam English.
 */
class PembayaranDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    /** Membuat dokumen minimal + override (mirror VerifikasiDocumentRowTest::buatDokumen). */
    private function buatDokumen(array $overrides = []): Dokumen
    {
        $this->seq++;

        return Dokumen::create(array_merge([
            'nomor_agenda'    => 'AG-'.$this->seq,
            'bulan'           => 'Juli',
            'tahun'           => '2026',
            'tanggal_masuk'   => now(),
            'status'          => 'sent_to_pembayaran',
            'created_by'      => 'operator',
            'current_handler' => 'pembayaran',
        ], $overrides));
    }

    // === Kasus wajib dari brief (task-1-brief.md Step 1) ===

    public function test_badge_sudah_dibayar_saat_tanggal_dibayar_terisi(): void
    {
        $dokumen = $this->buatDokumen(['tanggal_dibayar' => now()]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertSame('sudah_dibayar', $row['status_badge']['state']);
        $this->assertSame('status-pill--paid', $row['status_badge']['class']);
        $this->assertSame('Sudah Dibayar', $row['status_badge']['text']);
    }

    public function test_badge_sudah_dibayar_saat_status_pembayaran_uppercase_dengan_spasi(): void
    {
        // Toleransi casing CSV lama: "SUDAH DIBAYAR" (uppercase + spasi, bukan
        // underscore) tetap dipetakan ke paid — dan menang atas cabang handler
        // (current_handler sengaja BUKAN 'pembayaran' di sini untuk membuktikan urutan cabang).
        $dokumen = $this->buatDokumen([
            'current_handler'   => 'akutansi',
            'status'            => 'draft',
            'status_pembayaran' => 'SUDAH DIBAYAR',
        ]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertSame('sudah_dibayar', $row['status_badge']['state']);
        $this->assertSame('status-pill--paid', $row['status_badge']['class']);
    }

    public function test_badge_siap_dibayar_saat_current_handler_pembayaran_belum_bayar(): void
    {
        $dokumen = $this->buatDokumen(['current_handler' => 'pembayaran']);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertSame('siap_dibayar', $row['status_badge']['state']);
        $this->assertSame('status-pill--ready', $row['status_badge']['class']);
        $this->assertSame('Siap Dibayar', $row['status_badge']['text']);
    }

    public function test_badge_belum_siap_saat_current_handler_akutansi(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertSame('belum_siap_dibayar', $row['status_badge']['state']);
        $this->assertSame('status-pill--pending', $row['status_badge']['class']);
        $this->assertSame('Belum Siap', $row['status_badge']['text']);
    }

    public function test_can_edit_selalu_true_walau_bukan_di_pembayaran(): void
    {
        // Edit-anywhere: aturan bisnis pembayaran yang disengaja — BUKAN
        // DokumenHelper::canEditDocument()/gate is_at_my_role.
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertTrue($row['can_edit']);
    }

    public function test_tanpa_key_deadline(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertArrayNotHasKey('deadline', $row);
    }

    // === Kolom katalog (getPembayaranDashboardAvailableColumns()) ===

    public function test_kolom_katalog_pembayaran_hadir_sebagai_raw(): void
    {
        // Kolom katalog pembayaran yang tak sudah disediakan baseRow()
        // (config('document_columns.base')) harus tetap hadir sebagai key raw
        // di row (klien/engine getFormatter yang memformat).
        $dokumen = $this->buatDokumen([
            'status_pembayaran' => 'sudah_dibayar',
        ]);

        $row = PembayaranDocumentRow::fromDokumen($dokumen);

        $this->assertArrayHasKey('status_pembayaran', $row);
        $this->assertSame('sudah_dibayar', $row['status_pembayaran']);
        $this->assertArrayHasKey('nomor_mirror', $row);
        $this->assertArrayHasKey('link_bukti_pembayaran', $row);
        $this->assertArrayHasKey('tanggal_berakhir_ba', $row);
        $this->assertArrayHasKey('umur_dokumen_tanggal_masuk', $row);
        $this->assertArrayHasKey('umur_dokumen_tanggal_spp', $row);
        $this->assertArrayHasKey('umur_dokumen_tanggal_ba', $row);
    }
}
