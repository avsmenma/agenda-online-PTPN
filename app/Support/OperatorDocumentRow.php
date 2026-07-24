<?php

namespace App\Support;

use App\Models\Dokumen;
use App\Models\DokumenStatus;

/**
 * DTO baris tabel operator (Tabulator). Mewarisi derivasi bersama dari
 * App\Support\DocumentRow (kolom base, rupiah, join, tanggal, handler) dan
 * menambah bit khas operator: pohon display_status, can_edit operator, dan
 * alasan penolakan. Sumber kebenaran TUNGGAL badge status operator.
 *
 * Prasyarat: roleStatuses, dibayarKepadas, dokumenPos sudah eager-load (tanpa
 * query DB). $handlerOptions disediakan pemanggil.
 */
class OperatorDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $statuses = $dokumen->roleStatuses;

        // === Status Team Verifikasi terbaru ===
        $tvStatus   = $statuses->where('role_code', 'team_verifikasi')->sortByDesc('status_changed_at')->first();
        $tvRejected = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_REJECTED;
        $tvPending  = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_PENDING;
        $tvApproved = $tvStatus && strtolower($tvStatus->status ?? '') === DokumenStatus::STATUS_APPROVED;

        $statusLower            = strtolower($dokumen->status ?? '');
        $currentHandlerLower    = strtolower($dokumen->current_handler ?? '');
        $currentHandlerOperator = $currentHandlerLower === 'operator';

        // Ada status peran hilir — bukan sekadar "selain operator".
        $hasOtherRoles = $statuses->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran'])->isNotEmpty();

        // Penanda ditolak untuk aturan can_edit.
        $isRejected = $tvRejected;
        if (! $isRejected && $statusLower === 'returned_to_operator') {
            $isRejected = $statuses->where('status', DokumenStatus::STATUS_REJECTED)->isNotEmpty();
        }

        // === Pohon keputusan display_status ===
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

        $label = match ($code) {
            'draft'                        => 'Belum Dikirim',
            'menunggu_approval_verifikasi' => 'Menunggu Approve Team Verifikasi',
            'ditolak_verifikasi'           => 'Dokumen Ditolak oleh Team Verifikasi',
            'dikembalikan'                 => 'Dikembalikan',
            default                        => 'Terkirim',
        };

        // === Aturan can_edit operator (&& mengikat lebih kuat dari ||) ===
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

        // === Overlay bit operator di atas basis ===
        $row['display_status'] = ['code' => $code, 'label' => $label, 'variant' => $code];
        $row['reject_reason']  = $rejectReason;
        $row['rejected_by']    = $rejectedBy;
        $row['rejected_at']    = $rejectedAt;
        $row['can_edit']       = $canEdit;

        return $row;
    }
}
