<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\DokumenRoleData;
use App\Support\PerpajakanDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerpajakanDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private function buatDokumen(array $attrs = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'nomor_agenda'  => '100',
            'current_handler' => 'perpajakan',
            'status'        => 'sent_to_perpajakan',
            'nilai_rupiah'  => 1000000,
        ], $attrs));
    }

    private function buatStatus(Dokumen $d, string $role, string $status): void
    {
        // status_changed_at wajib NOT NULL di skema (2025_12_15_100001_create_dokumen_statuses_table);
        // brief tak menyertakannya — disesuaikan ke skema nyata, pola sama dgn AkutansiDocumentRowTest.
        DokumenStatus::create(['dokumen_id' => $d->id, 'role_code' => $role, 'status' => $status, 'status_changed_at' => now()]);
    }

    private function buatRoleData(Dokumen $d, string $role, array $attrs = []): void
    {
        DokumenRoleData::create(array_merge(['dokumen_id' => $d->id, 'role_code' => $role], $attrs));
    }

    /** Muat relasi persis seperti dokumens(): roleData perpajakan-only + roleStatuses 4 role. */
    private function row(Dokumen $d, array $handlerOptions = []): array
    {
        $d->load([
            'roleData' => fn ($q) => $q->where('role_code', 'perpajakan'),
            'roleStatuses' => fn ($q) => $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']),
            'dibayarKepadas', 'dokumenPos',
        ]);
        return PerpajakanDocumentRow::fromDokumen($d, $handlerOptions, 'perpajakan');
    }

    public function test_badge_draft_saat_masih_di_hulu(): void
    {
        // current_handler operator, perpajakan belum terima → "Draft".
        $d = $this->buatDokumen(['current_handler' => 'operator', 'status' => 'draft']);
        $row = $this->row($d);
        $this->assertSame('badge-proses', $row['status_badge']['class']);
        $this->assertSame('⏳ Draft', $row['status_badge']['text']);
        $this->assertSame('none', $row['deadline']['variant']); // belum diterima
    }

    public function test_badge_terkirim_ke_akutansi_saat_akutansi_approve(): void
    {
        // display_status belum final → fallback: akutansi approved → "Terkirim ke Team Akutansi".
        $d = $this->buatDokumen(['status' => 'sent_to_akutansi']);
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(3), 'processed_at' => now()->subHours(1)]);
        $this->buatStatus($d, 'akutansi', 'approved');
        $row = $this->row($d);
        $this->assertSame('badge-sent', $row['status_badge']['class']);
        $this->assertStringContainsString('Terkirim ke Team Akutansi', $row['status_badge']['text']);
        // deadline: sudah diterima + sent → kartu beku gray.
        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('gray', $row['deadline']['color']);
        $this->assertStringContainsString('⏸️', $row['deadline']['age_text']);
    }

    public function test_deadline_aktif_hijau_saat_diproses(): void
    {
        $d = $this->buatDokumen(['status' => 'sent_to_perpajakan']);
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(5)]); // <24j → AMAN/green
        $row = $this->row($d);
        $this->assertSame('card', $row['deadline']['variant']);
        $this->assertSame('green', $row['deadline']['color']);
        $this->assertSame('AMAN', $row['deadline']['indicator_label']);
        $this->assertSame('active', $row['deadline']['type']);
        $this->assertNull($row['deadline']['footer']);
    }

    public function test_basis_dan_kunci_perpajakan_hadir_tanpa_key_operator(): void
    {
        $d = $this->buatDokumen();
        $this->buatRoleData($d, 'perpajakan', ['received_at' => now()->subHours(1)]);
        $row = $this->row($d, [['value' => 'perpajakan', 'label' => 'Tim Perpajakan']]);
        $this->assertArrayHasKey('nilai_rupiah_formatted', $row);
        $this->assertArrayHasKey('dates', $row);
        $this->assertArrayHasKey('handler_options', $row);
        $this->assertArrayHasKey('is_at_my_role', $row);
        $this->assertArrayHasKey('status_pembayaran', $row);
        $this->assertArrayNotHasKey('display_status', $row); // bukan operator
    }
}
