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

        return $row;
    }
}
