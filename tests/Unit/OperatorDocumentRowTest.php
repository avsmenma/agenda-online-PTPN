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
 * can_edit. Logika baris ini dulu diekstrak dari _tableRowsAjax.blade.php (view
 * classic, dihapus 2026-07-23). Kini OperatorDocumentRow adalah sumber tunggalnya.
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
    private function baris(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = null): array
    {
        $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

        return OperatorDocumentRow::fromDokumen($dokumen, $handlerOptions, $viewerRole);
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

    public function test_link_disanitasi_safeurl(): void
    {
        $dokumen = $this->buatDokumen([
            'link'               => 'javascript:alert(1)',
            'link_dokumen_pajak' => 'https://pajak.example.com/doc',
        ]);

        $row = $this->baris($dokumen);

        // Skema berbahaya dinetralkan: SafeUrl memaksa prefix https:// sehingga
        // hasil BUKAN skema 'javascript:' mentah lagi.
        $this->assertStringStartsNotWith('javascript:', $row['link_safe']);
        $this->assertStringStartsWith('https://', $row['link_safe']);
        // URL https normal lolos apa adanya.
        $this->assertSame('https://pajak.example.com/doc', $row['link_dokumen_pajak_safe']);
    }

    public function test_link_safe_null_saat_kosong(): void
    {
        $dokumen = $this->buatDokumen([
            'link'               => '',
            'link_dokumen_pajak' => null,
        ]);

        $row = $this->baris($dokumen);

        $this->assertNull($row['link_safe']);
        $this->assertNull($row['link_dokumen_pajak_safe']);
    }

    public function test_dates_terformat_sesuai_peta(): void
    {
        $dokumen = $this->buatDokumen([
            'tanggal_spp'   => '2026-07-01',           // cast date  → d-m-Y
            'tanggal_masuk' => '2026-07-01 09:30:00',  // cast datetime → d-m-Y H:i
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('01-07-2026', $row['dates']['tanggal_spp']);
        $this->assertSame('01-07-2026 09:30', $row['dates']['tanggal_masuk']);
    }

    public function test_dates_null_jadi_strip(): void
    {
        // tanggal_faktur tidak di-set → harus '-'.
        $dokumen = $this->buatDokumen();

        $row = $this->baris($dokumen);

        $this->assertSame('-', $row['dates']['tanggal_faktur']);
    }

    public function test_dates_format_d_m_y_h_i_dan_d_m_y(): void
    {
        $dokumen = $this->buatDokumen([
            'tanggal_paraf'   => '2026-07-10 08:15:00', // cast datetime → d/m/Y H:i
            'tanggal_dibayar' => '2026-07-15',           // cast date     → d/m/Y
        ]);

        $row = $this->baris($dokumen);

        $this->assertSame('10/07/2026 08:15', $row['dates']['tanggal_paraf']);
        $this->assertSame('15/07/2026', $row['dates']['tanggal_dibayar']);
    }

    public function test_dates_kolom_non_cast_diparse_defensif(): void
    {
        // tanggal_kembali_ke_bagian BUKAN kolom DB (tak ada di $fillable/$casts
        // model Dokumen) — di-set langsung ke atribut in-memory (bukan mass
        // assignment) untuk menguji jalur parse defensif OperatorDocumentRow.
        $dokumen = $this->buatDokumen();
        $dokumen->tanggal_kembali_ke_bagian = '2026-07-05 14:00:00';

        $row = $this->baris($dokumen);

        $this->assertSame('05/07/2026 14:00', $row['dates']['tanggal_kembali_ke_bagian']);
    }

    public function test_dates_kolom_non_cast_tak_terparse_jadi_strip(): void
    {
        // Nilai mentah yang tidak bisa di-parse Carbon → fallback '-'.
        $dokumen = $this->buatDokumen();
        $dokumen->tanggal_kembali_ke_bagian = 'bukan-tanggal-valid';

        $row = $this->baris($dokumen);

        $this->assertSame('-', $row['dates']['tanggal_kembali_ke_bagian']);
    }

    public function test_status_dokumen_custom_fallback_ke_csv(): void
    {
        // status_dokumen_csv (kolom CSV real) diutamakan atas status_dokumen_custom.
        $dokumen = $this->buatDokumen(['status_dokumen_csv' => 'Selesai Dibayar']);

        $row = $this->baris($dokumen);

        $this->assertSame('Selesai Dibayar', $row['status_dokumen_custom']);
    }

    public function test_status_dokumen_custom_dipakai_saat_csv_kosong(): void
    {
        // status_dokumen_custom BUKAN kolom fillable/DB nyata — di-set langsung
        // ke atribut in-memory (bukan mass assignment), sama seperti kolom
        // tanggal non-cast di atas.
        $dokumen = $this->buatDokumen();
        $dokumen->status_dokumen_custom = 'Dikembalikan';

        $row = $this->baris($dokumen);

        $this->assertSame('Dikembalikan', $row['status_dokumen_custom']);
    }

    public function test_can_change_handler_true_saat_viewer_pengurus_saat_ini_tanpa_pending(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);

        $row = $this->baris($dokumen, [], 'operator');

        $this->assertTrue($row['can_change_handler']);
    }

    public function test_can_change_handler_false_saat_viewer_bukan_pengurus_saat_ini(): void
    {
        $dokumen = $this->buatDokumen([
            'status'          => 'sent_to_verifikasi',
            'current_handler' => 'team_verifikasi',
        ]);

        $row = $this->baris($dokumen, [], 'operator');

        $this->assertFalse($row['can_change_handler']);
    }

    public function test_can_change_handler_false_saat_ada_status_pending(): void
    {
        $dokumen = $this->buatDokumen(['status' => 'draft', 'current_handler' => 'operator']);
        $this->buatStatus($dokumen, 'team_verifikasi', DokumenStatus::STATUS_PENDING);

        $row = $this->baris($dokumen, [], 'operator');

        $this->assertFalse($row['can_change_handler']);
    }
}
