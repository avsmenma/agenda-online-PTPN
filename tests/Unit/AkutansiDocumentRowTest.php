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
        $this->assertSame('TEPAT WAKTU', $row['deadline']['indicator_label']);
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

    // === Fix wave (code review): cabang badge yang tadinya belum diuji ===
    // Nomor cabang mengacu urutan if/elseif di buildStatusBadge().

    public function test_badge_terkirim_ke_pembayaran_via_pembayaran_approved(): void
    {
        // Cabang 4 (sentFromAkutansi), lewat jalur pembayaranHasApproved —
        // BUKAN lewat isBypassedToPayment. Akutansi belum pernah menerima
        // dokumen ini (tanpa roleData akutansi sama sekali), tapi pembayaran
        // sudah approve duluan.
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);
        $this->buatStatus($dokumen, 'pembayaran', DokumenStatus::STATUS_APPROVED);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertNull($row['status_badge']['icon']);
        $this->assertSame('📤 Terkirim ke Pembayaran', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_selesai_saat_status_selesai_dan_sudah_diterima_akutansi(): void
    {
        // Cabang 7: status==='selesai', tidak ditolak/pending/sentFromAkutansi,
        // sudah diterima akutansi (supaya tidak nyangkut di cabang Draft).
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'selesai',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subMinutes(5),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-selesai', $row['status_badge']['class']);
        $this->assertNull($row['status_badge']['icon']);
        $this->assertSame('✓ Selesai', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_kembali_ke_team_verifikasi_saat_returned_to_verifikasi_tanpa_ditolak(): void
    {
        // Cabang 8: status==='returned_to_verifikasi' TANPA status 'rejected'.
        // (Cabang REJECTED — badge-dikembalikan berlink ke halaman Pengembalian
        // mati — sudah dihapus 2026-07-24; dokumen rejected kini jatuh ke cabang
        // badge berikutnya yang cocok, jadi tak ada lagi kasus "DENGAN rejected"
        // yang diuji terpisah di sini.)
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'returned_to_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertSame('fa-paper-plane', $row['status_badge']['icon']);
        $this->assertSame('Kembali ke Team Verifikasi', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_dikembalikan_saat_returned_to_operator(): void
    {
        // Cabang 11. Akutansi sudah pernah menerima dokumen ini sebelum
        // dikembalikan ke operator — supaya guard "!akutansiReceived" pada
        // cabang Draft (2) & isBypassedToPayment (dalam cabang 4) tidak
        // keburu menangkapnya duluan.
        $dokumen = $this->buatDokumen([
            'current_handler' => 'operator',
            'status'          => 'returned_to_operator',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subDay(),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-dikembalikan', $row['status_badge']['class']);
        $this->assertNull($row['status_badge']['icon']);
        $this->assertSame('← Dikembalikan', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_selesai_sudah_dibayar_saat_status_completed(): void
    {
        // Cabang 12. Akutansi sudah menerima SEBELUM status jadi 'completed',
        // supaya isBypassedToPayment (butuh !akutansiReceived) tidak menangkap
        // dokumen ini duluan di cabang 4 (sentFromAkutansi).
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'completed',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subHours(3),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-selesai', $row['status_badge']['class']);
        $this->assertNull($row['status_badge']['icon']);
        $this->assertSame('✓ Selesai - Sudah Dibayar', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_default_sedang_diproses_saat_status_tak_dikenal(): void
    {
        // Cabang 13 (fallback terakhir). Status sintetis yang sengaja tidak
        // dicakup cabang eksplisit manapun, dengan current_handler BUKAN
        // 'akutansi' (supaya bukan cabang 9 yang kebetulan menghasilkan teks
        // sama) dan akutansi sudah menerima (supaya bukan cabang Draft/bypass).
        $dokumen = $this->buatDokumen([
            'current_handler' => 'pembayaran',
            'status'          => 'status_tak_dikenal',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subHours(3),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertNull($row['status_badge']['icon']);
        $this->assertSame('⏳ Sedang Diproses', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    // === Fix wave (code review): sub-kasus buildDeadline() Path A yang tadinya belum diuji ===

    public function test_deadline_card_peringatan_saat_umur_24_sampai_72_jam(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi', // aktif: bukan sent/completed/returned
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subHours(30), // di antara 24 jam dan 72 jam
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertSame('yellow', $row['deadline']['color']);
        $this->assertSame('PERINGATAN', $row['deadline']['indicator_label']);
        $this->assertSame('fa-exclamation-triangle', $row['deadline']['indicator_icon']);
        $this->assertNull($row['deadline']['footer']);
    }

    public function test_deadline_card_terlambat_saat_umur_72_jam_atau_lebih(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'sent_to_akutansi',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subHours(80), // >= 72 jam
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertSame('red', $row['deadline']['color']);
        $this->assertSame('TERLAMBAT', $row['deadline']['indicator_label']);
        $this->assertSame('fa-times-circle', $row['deadline']['indicator_icon']);
        $this->assertNull($row['deadline']['footer']);
    }

    public function test_deadline_card_sent_beku_saat_terkirim_ke_pembayaran(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'pembayaran',
            'status'          => 'sent_to_pembayaran',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at'  => now()->subHours(50),
            'processed_at' => now()->subHours(10),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('sent', $row['deadline']['type']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertStringEndsWith(' ⏸️', $row['deadline']['age_text']);
        $this->assertSame('sent', $row['deadline']['footer']['kind']);
        $this->assertSame('fa-paper-plane', $row['deadline']['footer']['icon']);
        $this->assertSame('Terkirim', $row['deadline']['footer']['text']);
    }

    public function test_deadline_card_completed_beku_saat_status_selesai(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'selesai',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at'  => now()->subHours(50),
            'processed_at' => now()->subHours(10),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('completed', $row['deadline']['type']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertStringEndsWith(' ⏸️', $row['deadline']['age_text']);
        $this->assertSame('completed', $row['deadline']['footer']['kind']);
        $this->assertSame('fa-check-circle', $row['deadline']['footer']['icon']);
        $this->assertSame('Selesai', $row['deadline']['footer']['text']);
    }

    public function test_deadline_card_paused_beku_saat_returned_to_verifikasi(): void
    {
        $dokumen = $this->buatDokumen([
            'current_handler' => 'akutansi',
            'status'          => 'returned_to_verifikasi',
        ]);
        $this->buatRoleData($dokumen, 'akutansi', [
            'received_at' => now()->subHours(10),
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('paused', $row['deadline']['type']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertStringContainsString('⏸️', $row['deadline']['age_text']);
        $this->assertSame('paused', $row['deadline']['footer']['kind']);
        $this->assertSame('fa-pause-circle', $row['deadline']['footer']['icon']);
        $this->assertSame('Berhenti Sementara', $row['deadline']['footer']['text']);
    }
}
