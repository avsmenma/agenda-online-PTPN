<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\DokumenStatus;

/**
 * Transform satu model Dokumen menjadi array baris JSON untuk tabel operator
 * (Tabulator). Sumber kebenaran TUNGGAL untuk derivasi badge status, format
 * rupiah, join relasi, dan aturan can_edit — dipindahkan dari partial Blade
 * `_tableRowsAjax.blade.php` agar bisa dipakai ulang oleh endpoint datatable
 * (Tugas 2) dan inlineCreate (Tugas 3) serta bisa diuji secara unit.
 *
 * Prasyarat: relasi `roleStatuses`, `dibayarKepadas`, `dokumenPos` sudah
 * ter-eager-load pada $dokumen (kelas ini TIDAK melakukan query database).
 * `$handlerOptions` disediakan pemanggil (agar tak query per-baris) dan hanya
 * ditanamkan apa adanya ke keluaran.
 */
class OperatorDocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions): array
    {
        $statuses = $dokumen->roleStatuses;

        // === Status Team Verifikasi terbaru (identik _tableRowsAjax.blade.php:11-14) ===
        $tvStatus   = $statuses->where('role_code', 'team_verifikasi')->sortByDesc('status_changed_at')->first();
        $tvRejected = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_REJECTED;
        $tvPending  = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_PENDING;
        $tvApproved = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_APPROVED;

        $statusLower            = strtolower($dokumen->status ?? '');
        $currentHandlerLower    = strtolower($dokumen->current_handler ?? '');
        $currentHandlerOperator = $currentHandlerLower === 'operator';

        // Ada status peran hilir (identik :27) — bukan sekadar "selain operator".
        $hasOtherRoles = $statuses->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran'])->isNotEmpty();

        // Penanda ditolak untuk aturan can_edit (identik :16-19).
        $isRejected = $tvRejected;
        if (! $isRejected && $statusLower === 'returned_to_operator') {
            $isRejected = $statuses->where('status', DokumenStatus::STATUS_REJECTED)->isNotEmpty();
        }

        // === Pohon keputusan display_status (identik :111-125) ===
        if ($statusLower === 'returned_to_operator') {
            $code = 'dikembalikan';
        } elseif ($tvRejected) {
            $code = 'ditolak_verifikasi';
        } elseif ($tvPending) {
            $code = 'menunggu_approval_verifikasi';
        } elseif ($tvApproved || $hasOtherRoles) {
            $code = 'terkirim';
        } elseif ($statusLower === 'menunggu_approval_keuangan' && $currentHandlerOperator) {
            $code = 'draft';
        } elseif ($currentHandlerOperator && in_array($statusLower, ['draft', 'returned_to_operator'], true)) {
            $code = 'draft';
        } else {
            $code = in_array($currentHandlerLower, ['team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran'], true)
                ? 'terkirim'
                : 'draft';
        }

        // Label badge (identik match :126-132).
        $label = match ($code) {
            'draft'                        => 'Belum Dikirim',
            'menunggu_approval_verifikasi' => 'Menunggu Approve Team Verifikasi',
            'ditolak_verifikasi'           => 'Dokumen Ditolak oleh Team Verifikasi',
            'dikembalikan'                 => 'Dikembalikan',
            default                        => 'Terkirim',
        };

        // === Aturan can_edit (identik :48-50; && mengikat lebih kuat dari ||) ===
        $canEdit = ($currentHandlerOperator
                && in_array($statusLower, ['draft', 'returned_to_operator', 'belum_dikirim', 'belum dikirim', 'menunggu_approval_keuangan'], true))
            || ($isRejected && $currentHandlerOperator);

        // === Alasan penolakan dari status verifikasi/team_verifikasi ===
        $rejectReason = null;
        $rejectedBy   = null;
        $rejectedAt   = null;
        $verifStatus  = $statuses->whereIn('role_code', ['verifikasi', 'team_verifikasi'])->first();
        if ($verifStatus && $verifStatus->status === DokumenStatus::STATUS_REJECTED) {
            $rejectReason = $verifStatus->notes;
            $rejectedBy   = $verifStatus->changed_by;
            $rejectedAt   = $verifStatus->status_changed_at?->format('d-m-Y H:i');
        }

        // === Nilai ATTRIBUTE mentah untuk setiap kolom base (formatter di klien) ===
        $row = ['id' => $dokumen->id];
        foreach (array_keys(config('document_columns.base')) as $key) {
            $row[$key] = $dokumen->{$key};
        }

        // Status dokumen: utamakan nilai CSV bila ada, fallback ke custom
        // (identik _tableRowsAjax.blade.php:203-204: status_dokumen_csv ?? status_dokumen_custom ?? '-').
        // Kosong/null tetap null/kosong di sini — klien yang merender '-'.
        $row['status_dokumen_custom'] = $dokumen->status_dokumen_csv ?? $dokumen->status_dokumen_custom;

        // === Kunci turunan tampilan (override sebagian kolom base bila perlu) ===
        $row['display_status']          = ['code' => $code, 'label' => $label, 'variant' => $code];
        $row['nilai_rupiah_formatted']  = $dokumen->formatted_nilai_rupiah;
        $row['dpp_pph_formatted']       = $dokumen->dpp_pph !== null
            ? 'Rp ' . number_format((float) $dokumen->dpp_pph, 0, ',', '.')
            : '-';
        $row['ppn_terhutang_formatted'] = $dokumen->ppn_terhutang !== null
            ? 'Rp ' . number_format((float) $dokumen->ppn_terhutang, 0, ',', '.')
            : '-';
        // Join nama penerima, fallback ke kolom flat lama bila relasi kosong (identik :173-178).
        $row['dibayar_kepada']     = $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: ($dokumen->dibayar_kepada ?? '');
        // Join nomor PO dengan fallback ke kolom CSV NO_PO (identik :219).
        $row['nomor_po']           = $dokumen->dokumenPos->pluck('nomor_po')->filter()->join(', ') ?: ($dokumen->NO_PO ?? '-');
        $row['nomor_miro_display'] = $dokumen->nomor_miro_display;
        $row['reject_reason']      = $rejectReason;
        $row['rejected_by']        = $rejectedBy;
        $row['rejected_at']        = $rejectedAt;
        $row['can_edit']           = $canEdit;
        $row['handler']            = $dokumen->current_handler;
        $row['handler_options']    = $handlerOptions;

        // === URL link ter-sanitasi (identik render lama :231,238) ===
        // SafeUrl::external mengembalikan null bila kosong/tak ada, dan SELALU
        // memaksa skema http(s) untuk menetralkan javascript:/data:/dll. Klien
        // cukup memasang atribut href tanpa sanitasi ulang.
        $row['link_safe']              = SafeUrl::external($dokumen->link);
        $row['link_dokumen_pajak_safe'] = SafeUrl::external($dokumen->link_dokumen_pajak);

        // === Tanggal terformat sisi-server (peta format identik _tableRowsAjax) ===
        // Null/kosong → '-'. Kolom yang ada di $casts model = Carbon (format
        // langsung); kolom string mentah di-parse defensif dengan fallback '-'.
        $row['dates'] = self::formatDates($dokumen);

        return $row;
    }

    /**
     * Peta kolom tanggal → string terformat, mengikuti PERSIS format render lama
     * di `_tableRowsAjax.blade.php`. Null/kosong → '-'.
     *
     * Kolom yang tercantum di `$casts` model Dokumen (date/datetime) sudah berupa
     * Carbon sehingga cukup `->format()`. Dua kolom `tanggal_kembali_ke_bagian`
     * dan `tanggal_hasil_koreksi_bagian` BUKAN cast (string mentah) → di-parse
     * defensif via Carbon::parse dalam try/catch, gagal → '-'.
     */
    private static function formatDates(Dokumen $dokumen): array
    {
        $formats = [
            // d-m-Y
            'tanggal_spp'                      => 'd-m-Y',
            'tanggal_berita_acara'             => 'd-m-Y',
            'tanggal_spk'                      => 'd-m-Y',
            'tanggal_berakhir_spk'             => 'd-m-Y',
            // d-m-Y H:i
            'tanggal_masuk'                    => 'd-m-Y H:i',
            // d/m/Y H:i
            'tanggal_paraf'                    => 'd/m/Y H:i',
            'tanggal_selesai_diproses'         => 'd/m/Y H:i',
            'tanggal_kembali_ke_bagian'        => 'd/m/Y H:i',
            'tanggal_hasil_koreksi_bagian'     => 'd/m/Y H:i',
            // d/m/Y
            'tanggal_dibayar'                  => 'd/m/Y',
            'tanggal_faktur'                   => 'd/m/Y',
            'tanggal_selesai_verifikasi_pajak' => 'd/m/Y',
        ];

        $dates = [];
        foreach ($formats as $col => $format) {
            $value = $dokumen->{$col} ?? null;

            if ($value === null || $value === '') {
                $dates[$col] = '-';
                continue;
            }

            // Kolom cast (Carbon/DateTime) — format langsung.
            if ($value instanceof \DateTimeInterface) {
                $dates[$col] = $value->format($format);
                continue;
            }

            // Kolom string mentah (tak di-cast) — parse defensif.
            try {
                $dates[$col] = \Illuminate\Support\Carbon::parse($value)->format($format);
            } catch (\Throwable $e) {
                $dates[$col] = '-';
            }
        }

        return $dates;
    }
}
