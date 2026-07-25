<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DokumenStatus;
use App\Support\VerifikasiDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji VerifikasiDocumentRow — DTO baris tabel team_verifikasi (Tabulator,
 * Rollout 3) yang memindahkan pohon keputusan badge Status (~24 cabang,
 * _rows.blade.php:455-608) dan kartu Deadline (_rows.blade.php:300-451) dari
 * Blade ke server.
 *
 * Harness: Feature-style (Tests\TestCase + RefreshDatabase), BUKAN model
 * in-memory — DokumenHelper::canEditDocument()/isDocumentLocked() memanggil
 * relasi via method (query nyata), butuh DB sungguhan. Pola sama dengan
 * AkutansiDocumentRowTest/PerpajakanDocumentRowTest.
 *
 * Nama method uji dalam Bahasa Indonesia; variabel lokal dalam English.
 */
class VerifikasiDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    /** Membuat dokumen minimal + override. */
    private function buatDokumen(array $overrides = []): Dokumen
    {
        $this->seq++;

        return Dokumen::create(array_merge([
            'nomor_agenda'    => 'AG-'.$this->seq,
            'bulan'           => 'Juli',
            'tahun'           => '2026',
            'tanggal_masuk'   => now(),
            'status'          => 'sent_to_team_verifikasi',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
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

    /** Menambahkan satu record dokumen_role_data (received_at/processed_at/display_status). */
    private function buatRoleData(Dokumen $dokumen, string $roleCode, array $attrs = []): DokumenRoleData
    {
        return DokumenRoleData::create(array_merge([
            'dokumen_id' => $dokumen->id,
            'role_code'  => $roleCode,
        ], $attrs));
    }

    /**
     * Muat relasi dengan PRASYARAT PARITAS TeamVerifikasiController::dokumens():456-464:
     * roleData eager-load HANYA role_code='team_verifikasi' (getDataForRole('perpajakan'/
     * 'akutansi'/'pembayaran') jadi null tanpa query), roleStatuses whereIn 4 role.
     */
    private function baris(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = 'team_verifikasi'): array
    {
        $dokumen->load([
            'roleData' => fn ($q) => $q->where('role_code', 'team_verifikasi'),
            'roleStatuses' => fn ($q) => $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']),
            'dibayarKepadas',
            'dokumenPos',
        ]);

        return VerifikasiDocumentRow::fromDokumen($dokumen, $handlerOptions, $viewerRole);
    }

    // === Kasus wajib dari brief (Step 1) ===

    public function test_badge_kembali_dari_perpajakan_saat_returned_to_verifikasi(): void
    {
        // Cabang 1: is_returned_to_verifikasi.
        $dokumen = $this->buatDokumen([
            'status'          => 'returned_to_verifikasi',
            'return_source'   => 'perpajakan',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('fa-inbox', $row['status_badge']['icon']);
        $this->assertSame('Kembali dari Team Perpajakan', $row['status_badge']['text']);
        $this->assertNull($row['status_badge']['link']);
    }

    public function test_badge_dokumen_ditolak_saat_ditolak_role_lain(): void
    {
        // Cabang 2a: is_rejected & is_rejected_by_other_role & rejected_by_role.
        $dokumen = $this->buatDokumen([
            'status'          => 'returned_to_department',
            'return_source'   => 'perpajakan',
            'current_handler' => 'team_verifikasi',
        ]);
        $this->buatStatus($dokumen, 'perpajakan', 'rejected');

        $row = $this->baris($dokumen);

        $this->assertSame('badge-dikembalikan', $row['status_badge']['class']);
        $this->assertSame('fa-times-circle', $row['status_badge']['icon']);
        $this->assertSame('Dokumen ditolak', $row['status_badge']['text']);
    }

    public function test_badge_selesai_saat_status_selesai(): void
    {
        // Cabang 3.
        $dokumen = $this->buatDokumen(['status' => 'selesai']);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-selesai', $row['status_badge']['class']);
        $this->assertSame('✓ Selesai', $row['status_badge']['text']);
    }

    public function test_badge_terkirim_saat_display_status_terkirim_akutansi(): void
    {
        // Cabang 6a: display_status berawalan 'terkirim'.
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_akutansi',
            'current_handler' => 'akutansi',
        ]);
        $this->buatRoleData($dokumen, 'team_verifikasi', ['display_status' => 'terkirim_akutansi']);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertStringStartsWith('📤 ', $row['status_badge']['text']);
    }

    public function test_badge_menunggu_approve_perpajakan_gradient_kuning(): void
    {
        // Cabang 13 (atau intersepsi cabang 7 — sama-sama badge-warning + gradient
        // kuning-oranye ffc107, jadi hasil observable identik).
        $dokumen = $this->buatDokumen([
            'status'          => 'waiting_approval_perpajakan',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-warning', $row['status_badge']['class']);
        $this->assertStringContainsString('linear-gradient', $row['status_badge']['style']);
        $this->assertStringContainsString('ffc107', $row['status_badge']['style']);
    }

    public function test_badge_menunggu_approve_pembayaran_gradient_ungu(): void
    {
        // Cabang 15: satu-satunya gradient UNGU (6610f2) di seluruh cascade.
        $dokumen = $this->buatDokumen([
            'status'          => 'waiting_approval_pembayaran',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-warning', $row['status_badge']['class']);
        $this->assertStringContainsString('6610f2', $row['status_badge']['style']);
    }

    public function test_badge_fallback_sedang_diproses_saat_status_tak_dikenal(): void
    {
        // Cabang 24 (fallback terakhir).
        $dokumen = $this->buatDokumen([
            'status'          => 'foo_bar',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Foo_bar', $row['status_badge']['text']);
    }

    public function test_deadline_card_aman_saat_baru_diterima_2_jam(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sedang diproses',
            'current_handler' => 'team_verifikasi',
        ]);
        $this->buatRoleData($dokumen, 'team_verifikasi', ['received_at' => now()->subHours(2)]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertSame('green', $row['deadline']['color']);
        $this->assertSame('AMAN', $row['deadline']['indicator_label']);
    }

    public function test_deadline_card_terlambat_saat_umur_80_jam(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sedang diproses',
            'current_handler' => 'team_verifikasi',
        ]);
        $this->buatRoleData($dokumen, 'team_verifikasi', ['received_at' => now()->subHours(80)]);

        $row = $this->baris($dokumen);

        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('red', $row['deadline']['color']);
        $this->assertSame('TERLAMBAT', $row['deadline']['indicator_label']);
    }

    public function test_deadline_none_saat_belum_diterima(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_team_verifikasi',
            'current_handler' => 'team_verifikasi',
        ]);
        // Tanpa roleData team_verifikasi sama sekali → belum diterima.

        $row = $this->baris($dokumen);

        $this->assertSame('none', $row['deadline']['variant']);
        $this->assertNull($row['deadline']['received_display']);
    }

    public function test_paritas_is_locked_selalu_false_dan_role_data_lintas_role_null(): void
    {
        // Status yang di role LAIN akan mengunci dokumen (DokumenHelper::isDocumentLocked
        // true untuk 'pending_approval_perpajakan') — verifikasi WAJIB tetap false.
        $dokumen = $this->buatDokumen([
            'status'          => 'pending_approval_perpajakan',
            'current_handler' => 'team_verifikasi',
        ]);
        // roleData perpajakan dibuat di DB tapi TIDAK di-eager-load oleh baris()
        // (parity: role-own-only) → getDataForRole('perpajakan') tetap null.
        $this->buatRoleData($dokumen, 'perpajakan', ['received_at' => now()->subHour()]);

        $row = $this->baris($dokumen);

        $this->assertFalse($row['is_locked']);
        $this->assertNull($dokumen->getDataForRole('perpajakan'));
    }

    public function test_basis_dan_kunci_verifikasi_hadir_tanpa_key_operator(): void
    {
        $dokumen = $this->buatDokumen();
        $this->buatRoleData($dokumen, 'team_verifikasi', ['received_at' => now()->subHours(1)]);

        $row = $this->baris($dokumen, [['value' => 'team_verifikasi', 'label' => 'Team Verifikasi']]);

        $this->assertArrayHasKey('nilai_rupiah_formatted', $row);
        $this->assertArrayHasKey('dates', $row);
        $this->assertArrayHasKey('handler_options', $row);
        $this->assertArrayHasKey('is_at_my_role', $row);
        $this->assertArrayHasKey('status_pembayaran', $row);
        $this->assertArrayHasKey('pemaraf', $row);
        $this->assertArrayHasKey('can_edit', $row);
        $this->assertArrayNotHasKey('display_status', $row); // bukan operator
    }

    public function test_is_at_my_role_true_saat_current_handler_team_verifikasi(): void
    {
        // Cabang pertama is_at_my_role (VerifikasiDocumentRow.php:49): current_handler === 'team_verifikasi'.
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_team_verifikasi',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen);

        $this->assertTrue($row['is_at_my_role']);
    }

    public function test_is_at_my_role_false_saat_bukan_di_team_verifikasi(): void
    {
        // Tak ada satu pun cabang is_at_my_role (VerifikasiDocumentRow.php:49-59)
        // yang menyala: current_handler bukan team_verifikasi, status bukan status
        // "menuju/menunggu role lain", bukan selesai+status_pembayaran, bukan
        // returned_to_department dari perpajakan/akutansi.
        $dokumen = $this->buatDokumen([
            'status'          => 'draft',
            'current_handler' => 'operator',
        ]);

        $row = $this->baris($dokumen);

        $this->assertFalse($row['is_at_my_role']);
    }
}
