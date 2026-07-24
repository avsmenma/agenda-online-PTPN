<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Support\AkutansiDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji AkutansiDocumentRow — DTO baris tabel akutansi (Tabulator) yang
 * memindahkan pohon keputusan badge Status (_rows.blade.php:417-511) dan kartu
 * Deadline (_rows.blade.php:170-414) dari Blade ke server.
 *
 * Harness: Feature-style (Tests\TestCase + RefreshDatabase), BUKAN model
 * in-memory seperti draf awal brief. Alasan: DokumenHelper::isDocumentLocked()
 * memanggil `$dokumen->roleStatuses()->where(...)->exists()` — pemanggilan
 * method relasi (dengan tanda kurung), bukan akses ke koleksi yang sudah
 * di-eager-load — sehingga SELALU mengeksekusi query nyata untuk sebagian
 * besar status (draft, sent_to_akutansi, dst). Ini butuh DB sungguhan.
 * Pola helper (buatDokumen/buatStatus/baris) meniru OperatorDocumentRowTest.
 *
 * Nama method uji dalam Bahasa Indonesia; variabel lokal dalam English.
 */
class AkutansiDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    /** Membuat dokumen minimal + override. */
    private function buatDokumen(array $overrides = []): Dokumen
    {
        $this->seq++;

        return Dokumen::create(array_merge([
            'nomor_agenda'    => 'AG-' . $this->seq,
            'bulan'           => 'Juli',
            'tahun'           => '2026',
            'tanggal_masuk'   => now(),
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ], $overrides));
    }

    /** Menambahkan satu record status peran. */
    private function buatStatus(Dokumen $dokumen, string $roleCode, string $status, array $extra = []): void
    {
        DokumenStatus::create(array_merge([
            'dokumen_id'        => $dokumen->id,
            'role_code'         => $roleCode,
            'status'            => $status,
            'status_changed_at' => now(),
        ], $extra));
    }

    /** Menambahkan satu record dokumen_role_data (received_at/processed_at/deadline_at). */
    private function buatRoleData(Dokumen $dokumen, string $roleCode, array $attrs = []): DokumenRoleData
    {
        return DokumenRoleData::create(array_merge([
            'dokumen_id' => $dokumen->id,
            'role_code'  => $roleCode,
        ], $attrs));
    }

    /**
     * Muat relasi dengan PRASYARAT PARITAS dokumens(): roleData eager-load
     * HANYA role_code='akutansi' (getDataForRole('pembayaran'/'team_verifikasi')
     * jadi null tanpa query), roleStatuses tanpa filter (semua role terkait).
     */
    private function baris(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = 'akutansi'): array
    {
        $dokumen->load([
            'roleData' => fn ($q) => $q->where('role_code', 'akutansi'),
            'roleStatuses',
            'dibayarKepadas',
            'dokumenPos',
        ]);

        return AkutansiDocumentRow::fromDokumen($dokumen, $handlerOptions, $viewerRole);
    }

    // === Kasus wajib dari brief (Step 1) ===

    public function test_badge_draft_saat_dokumen_masih_di_hulu(): void
    {
        // Belum sampai akutansi (handler operator), belum selesai/dibayar → "Draft".
        $dokumen = $this->buatDokumen([
            'current_handler'   => 'operator',
            'status'            => 'draft',
            'status_pembayaran' => null,
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Draft', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
        // Belum diterima → deadline variant none.
        $this->assertSame('none', $row['deadline']['variant']);
    }

    public function test_badge_ditolak_menyertakan_link_cek_disini(): void
    {
        $dokumen = $this->buatDokumen([
            'nomor_agenda'    => '99',
            'current_handler' => 'akutansi',
            'status'          => 'returned_to_verifikasi',
        ]);
        $this->buatStatus($dokumen, 'akutansi', DokumenStatus::STATUS_REJECTED);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-dikembalikan', $row['status_badge']['class']);
        $this->assertSame('fa-times-circle', $row['status_badge']['icon']);
        $this->assertIsArray($row['status_badge']['link']);
        $this->assertSame('cek disini', $row['status_badge']['link']['text']);
        $this->assertStringContainsString('99', $row['status_badge']['link']['href']);
    }

    public function test_basis_ikut_terbawa(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
            'nilai_rupiah'    => 2000000,
        ]);

        $row = $this->baris($dokumen, [['value' => 'akutansi', 'label' => 'Tim Akuntansi']]);

        // Kunci basis hadir.
        $this->assertArrayHasKey('nilai_rupiah_formatted', $row);
        $this->assertArrayHasKey('dates', $row);
        $this->assertArrayHasKey('handler_options', $row);
        // Kunci akutansi hadir.
        $this->assertArrayHasKey('is_at_my_role', $row);
        $this->assertArrayHasKey('status_pembayaran', $row);
        // Tidak membawa kunci operator.
        $this->assertArrayNotHasKey('display_status', $row);
    }

    // === Cakupan tambahan: cabang badge lain yang realistis dicapai ===

    public function test_badge_menunggu_approval_pembayaran_saat_pending(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'pembayaran',
            'status'          => 'sent_to_pembayaran',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at'  => now()->subHours(2),
            'processed_at' => now()->subHour(),
        ]);
        $this->buatStatus($dokumen, 'pembayaran', DokumenStatus::STATUS_PENDING);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-warning', $row['status_badge']['class']);
        $this->assertSame('fa-clock', $row['status_badge']['icon']);
        $this->assertSame('Menunggu Approval dari Pembayaran', $row['status_badge']['text']);
    }

    public function test_badge_terkirim_ke_pembayaran_saat_status_sent(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'pembayaran',
            'status'          => 'sent_to_pembayaran',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at'  => now()->subDay(),
            'processed_at' => now()->subHours(20),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertSame('📤 Terkirim ke Pembayaran', $row['status_badge']['text']);
    }

    public function test_badge_sedang_diproses_saat_di_akutansi(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Sedang Diproses', $row['status_badge']['text']);
    }

    public function test_badge_terkunci_saat_pending_approval_perpajakan(): void
    {
        // isDocumentLocked() return true LEBIH AWAL untuk status ini (tanpa
        // memandang current_handler) — jalur menuju badge-locked.
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'pending_approval_perpajakan',
        ]);

        $row = $this->baris($dokumen);

        $this->assertTrue($row['is_locked']);
        $this->assertSame('badge-locked', $row['status_badge']['class']);
        $this->assertSame('🔒 Terkunci', $row['status_badge']['text']);
    }

    public function test_is_at_my_role_false_saat_masih_di_operator(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'operator',
            'status'          => 'draft',
        ]);

        $row = $this->baris($dokumen);

        $this->assertFalse($row['is_at_my_role']);
    }

    // === Cakupan tambahan: cabang deadline lain ===

    public function test_deadline_card_aktif_saat_baru_diterima_akutansi(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subMinutes(10),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertSame('green', $row['deadline']['color']);
        $this->assertSame('fa-check-circle', $row['deadline']['indicator_icon']);
        $this->assertSame('AMAN', $row['deadline']['indicator_label']);
        $this->assertNull($row['deadline']['footer']);
    }

    public function test_deadline_bypass_sent_saat_langsung_ke_pembayaran(): void
    {
        // Akutansi tidak pernah menerima dokumen ini (roleData akutansi kosong)
        // tapi status_pembayaran sudah lunas → bypass, fallback ke tanggal_masuk.
        $dokumen = $this->buatDokumen([
            'current_handler'   => 'pembayaran',
            'status'            => 'completed',
            'status_pembayaran' => 'sudah_dibayar',
            'tanggal_masuk'     => now()->subDays(3),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('sent', $row['deadline']['type']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertNotNull($row['deadline']['footer']);
        $this->assertSame('sent', $row['deadline']['footer']['kind']);
    }

    public function test_deadline_sent_fallback_saat_tanpa_timestamp_apapun(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler'   => 'pembayaran',
            'status'            => 'completed',
            'status_pembayaran' => 'sudah_dibayar',
            'tanggal_masuk'     => null,
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('sent_fallback', $row['deadline']['variant']);
        $this->assertSame('sent', $row['deadline']['type']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertNull($row['deadline']['received_display']);
    }
}
