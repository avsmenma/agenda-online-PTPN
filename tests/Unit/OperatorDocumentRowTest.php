<?php

namespace Tests\Unit;

use App\Models\DibayarKepada;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenStatus;
use App\Support\OperatorDocumentRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji kelas transform baris OperatorDocumentRow — sumber kebenaran tunggal
 * untuk derivasi badge status, format rupiah/tanggal, join relasi, dan aturan
 * can_edit yang sebelumnya tersebar di _tableRowsAjax.blade.php.
 *
 * Nama method uji dalam Bahasa Indonesia; variabel lokal dalam English.
 */
class OperatorDocumentRowTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    /** Membuat dokumen minimal (field yang dipakai inlineCreate) + override. */
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

    /** Menambahkan satu record status peran (unik per dokumen+role). */
    private function buatStatus(Dokumen $dokumen, string $roleCode, string $status, array $extra = []): void
    {
        DokumenStatus::create(array_merge([
            'dokumen_id'        => $dokumen->id,
            'role_code'         => $roleCode,
            'status'            => $status,
            'status_changed_at' => now(),
        ], $extra));
    }

    /** Memuat relasi yang dibutuhkan lalu memanggil transform. */
    private function baris(Dokumen $dokumen, array $handlerOptions = []): array
    {
        $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

        return OperatorDocumentRow::fromDokumen($dokumen, $handlerOptions);
    }

    public function test_status_dikembalikan_saat_returned_to_operator(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'returned_to_operator',
            'current_handler' => 'operator',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('dikembalikan', $row['display_status']['code']);
        $this->assertSame('Dikembalikan', $row['display_status']['label']);
        $this->assertSame('dikembalikan', $row['display_status']['variant']);
    }

    public function test_status_ditolak_saat_team_verifikasi_rejected(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_REJECTED, [
            'notes'      => 'Berkas kurang lengkap',
            'changed_by' => 'Verifikator A',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('ditolak_verifikasi', $row['display_status']['code']);
        $this->assertSame('Dokumen Ditolak oleh Team Verifikasi', $row['display_status']['label']);
        $this->assertSame('Berkas kurang lengkap', $row['reject_reason']);
    }

    public function test_status_menunggu_saat_team_verifikasi_pending(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'waiting_reviewer_approval',
            'current_handler' => 'team_verifikasi',
        ]);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_PENDING);

        $row = $this->baris($dokumen);

        $this->assertSame('menunggu_approval_verifikasi', $row['display_status']['code']);
        $this->assertSame('Menunggu Approve Team Verifikasi', $row['display_status']['label']);
    }

    public function test_status_terkirim_saat_ada_role_lain(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_perpajakan',
            'current_handler' => 'perpajakan',
        ]);
        // Ada status peran hilir (perpajakan) => hasOtherRoles true.
        $this->buatStatus($dokumen, 'perpajakan', DokumenStatus::STATUS_PENDING);

        $row = $this->baris($dokumen);

        $this->assertSame('terkirim', $row['display_status']['code']);
        $this->assertSame('Terkirim', $row['display_status']['label']);
    }

    public function test_status_belum_dikirim_saat_draft_di_operator(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);

        $row = $this->baris($dokumen);

        $this->assertSame('draft', $row['display_status']['code']);
        $this->assertSame('Belum Dikirim', $row['display_status']['label']);
    }

    public function test_status_belum_dikirim_saat_handler_non_operasional(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'draft',
            'current_handler' => 'bagian_x',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('draft', $row['display_status']['code']);
        $this->assertSame('Belum Dikirim', $row['display_status']['label']);
    }

    public function test_can_edit_true_saat_draft_di_operator(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);

        $row = $this->baris($dokumen);

        $this->assertTrue($row['can_edit']);
    }

    public function test_can_edit_false_saat_handler_bukan_operator(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_perpajakan',
            'current_handler' => 'perpajakan',
        ]);

        $row = $this->baris($dokumen);

        $this->assertFalse($row['can_edit']);
    }

    public function test_can_edit_true_saat_ditolak_verifikasi_di_operator(): void
    {
        // isRejected (tvRejected) && current_handler operator => boleh edit lagi.
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_REJECTED, [
            'notes' => 'perbaiki',
        ]);

        $row = $this->baris($dokumen);

        $this->assertTrue($row['can_edit']);
        $this->assertSame('ditolak_verifikasi', $row['display_status']['code']);
    }

    public function test_format_rupiah_nilai_pakai_titik_dan_dpp_tanpa_titik(): void
    {
        $dokumen = $this->buatDokumen([
            'nilai_rupiah'  => 1234567,
            'dpp_pph'       => 1234,
            'ppn_terhutang' => null,
        ]);

        $row = $this->baris($dokumen);

        // Accessor model: 'Rp. ' DENGAN titik.
        $this->assertSame('Rp. 1.234.567', $row['nilai_rupiah_formatted']);
        // dpp/ppn: 'Rp ' TANPA titik setelah Rp.
        $this->assertSame('Rp 1.234', $row['dpp_pph_formatted']);
        // null => '-'.
        $this->assertSame('-', $row['ppn_terhutang_formatted']);
    }

    public function test_reject_reason_dari_notes_team_verifikasi(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_REJECTED, [
            'notes'             => 'Nominal tidak sesuai',
            'changed_by'        => 'Ibu Yuni',
            'status_changed_at' => '2026-07-01 09:30:00',
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('Nominal tidak sesuai', $row['reject_reason']);
        $this->assertSame('Ibu Yuni', $row['rejected_by']);
        $this->assertSame('01-07-2026 09:30', $row['rejected_at']);
    }

    public function test_reject_reason_null_saat_tidak_ditolak(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_APPROVED);

        $row = $this->baris($dokumen);

        $this->assertNull($row['reject_reason']);
        $this->assertNull($row['rejected_by']);
        $this->assertNull($row['rejected_at']);
    }

    public function test_join_dibayar_kepada_dan_nomor_po(): void
    {
        $dokumen = $this->buatDokumen();
        DibayarKepada::create(['dokumen_id' => $dokumen->id, 'nama_penerima' => 'Budi']);
        DibayarKepada::create(['dokumen_id' => $dokumen->id, 'nama_penerima' => 'Ani']);
        DokumenPO::create(['dokumen_id' => $dokumen->id, 'nomor_po' => 'PO-1']);
        DokumenPO::create(['dokumen_id' => $dokumen->id, 'nomor_po' => 'PO-2']);

        $row = $this->baris($dokumen);

        $this->assertSame('Budi, Ani', $row['dibayar_kepada']);
        $this->assertSame('PO-1, PO-2', $row['nomor_po']);
    }

    public function test_nomor_po_fallback_ke_no_po_saat_tanpa_relasi(): void
    {
        $dokumen = $this->buatDokumen(['NO_PO' => 'PO-CSV-9']);

        $row = $this->baris($dokumen);

        $this->assertSame('PO-CSV-9', $row['nomor_po']);
    }

    public function test_semua_kolom_base_dan_kunci_turunan_ada(): void
    {
        $dokumen = $this->buatDokumen();

        $row = $this->baris($dokumen);

        // Semua key config base wajib ada.
        foreach (array_keys(config('document_columns.base')) as $key) {
            $this->assertArrayHasKey($key, $row, "Kolom base '{$key}' harus ada di baris");
        }

        // Kunci turunan wajib.
        foreach ([
            'id', 'display_status', 'nilai_rupiah_formatted', 'dpp_pph_formatted',
            'ppn_terhutang_formatted', 'dibayar_kepada', 'nomor_po', 'nomor_miro_display',
            'reject_reason', 'rejected_by', 'rejected_at', 'can_edit', 'handler', 'handler_options',
        ] as $key) {
            $this->assertArrayHasKey($key, $row, "Kunci turunan '{$key}' harus ada di baris");
        }

        $this->assertSame($dokumen->id, $row['id']);
        $this->assertSame('operator', $row['handler']);
    }

    public function test_handler_options_diteruskan_apa_adanya(): void
    {
        $dokumen = $this->buatDokumen();
        $handlerOptions = [
            ['value' => 'operator', 'label' => 'Operator'],
            ['value' => 'bagian_akn', 'label' => 'Akuntansi'],
        ];

        $row = $this->baris($dokumen, $handlerOptions);

        $this->assertSame($handlerOptions, $row['handler_options']);
    }
}
