<?php

namespace App\Support;

use App\Helpers\DokumenHelper;
use App\Models\Dokumen;
use Carbon\Carbon;

/**
 * DTO baris tabel akutansi (Tabulator). Mewarisi derivasi bersama dari
 * App\Support\DocumentRow dan menambah bit khas akutansi: is_at_my_role, kunci
 * lock, can_edit(akutansi), can_set_deadline, status_pembayaran, plus dua objek
 * siap-render yang menggantikan pohon keputusan Blade lama:
 *   - status_badge  ← porting _rows.blade.php:417-511 (kolom Status).
 *   - deadline      ← porting _rows.blade.php:170-414 (kolom Deadline).
 * Klien (document-tabulator.js) hanya MERENDER objek ini; nol logika bisnis di JS.
 *
 * Prasyarat data (WAJIB sama dengan dokumens()): roleData eager-load HANYA
 * role_code='akutansi'; roleStatuses eager-load semua role terkait. Dengan itu
 * getDataForRole('pembayaran'/'team_verifikasi') mengembalikan null tanpa query
 * — paritas persis dengan tabel lama.
 */
class AkutansiDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $isLocked = DokumenHelper::isDocumentLocked($dokumen);

        // Cross-role visibility: dokumen "di role saya" (identik dokumens():266-273).
        $isAtMyRole = in_array($dokumen->current_handler, ['akutansi'])
            || in_array($dokumen->status, [
                'sent_to_pembayaran',
                'pending_approval_pembayaran',
                'waiting_approval_pembayaran',
                'menunggu_di_approve',
            ])
            || (in_array($dokumen->status, ['completed', 'selesai']) && ! empty($dokumen->status_pembayaran));

        $row['is_at_my_role']       = $isAtMyRole;
        $row['is_locked']           = $isLocked;
        $row['lock_status_message'] = DokumenHelper::getLockedStatusMessage($dokumen);
        $row['lock_status_class']   = DokumenHelper::getLockStatusClass($dokumen);
        $row['can_edit']            = DokumenHelper::canEditDocument($dokumen, 'akutansi');
        $row['can_set_deadline']    = DokumenHelper::canSetDeadline($dokumen)['can_set'];
        $row['status_pembayaran']   = $dokumen->status_pembayaran;

        $row['status_badge'] = static::buildStatusBadge($dokumen, $isLocked);
        $row['deadline']     = static::buildDeadline($dokumen);

        return $row;
    }

    /**
     * Port pohon badge Status akutansi (_rows.blade.php:417-511) → objek
     * {class, icon, text, link}. Urutan cabang DIPERTAHANKAN persis.
     */
    protected static function buildStatusBadge(Dokumen $dokumen, bool $isLocked): array
    {
        $isRejected = $dokumen->roleStatuses
            ->whereIn('role_code', ['akutansi', 'pembayaran'])
            ->where('status', 'rejected')
            ->isNotEmpty();

        $akutansiRoleData   = $dokumen->getDataForRole('akutansi');
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity: roleData akutansi-only)

        $pembayaranHasApproved = $dokumen->roleStatuses
            ->where('role_code', 'pembayaran')->where('status', 'approved')->isNotEmpty();
        $pembayaranIsPending = $dokumen->roleStatuses
            ->where('role_code', 'pembayaran')->where('status', 'pending')->isNotEmpty();

        $isBypassedToPayment = (
            $dokumen->current_handler === 'pembayaran'
            || $dokumen->status === 'completed'
            || $dokumen->status_pembayaran === 'sudah_dibayar'
            || ($pembayaranRoleData && $pembayaranRoleData->received_at)
        ) && ! $akutansiRoleData?->received_at;

        $sentFromAkutansi = (
            $isBypassedToPayment
            || $pembayaranHasApproved
            || ($pembayaranRoleData && $pembayaranRoleData->received_at && ! $pembayaranIsPending)
        ) && ! $isRejected;

        if (! ($akutansiRoleData?->received_at)
            && in_array($dokumen->current_handler, ['operator', 'team_verifikasi', 'perpajakan'], true)
            && ! in_array($dokumen->status, ['completed', 'selesai'], true)
            && $dokumen->status_pembayaran !== 'sudah_dibayar') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Draft', 'link' => null];
        }
        if ($pembayaranIsPending) {
            return ['class' => 'badge-warning', 'icon' => 'fa-clock', 'text' => 'Menunggu Approval dari Pembayaran', 'link' => null];
        }
        if ($sentFromAkutansi) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Pembayaran', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_pembayaran' && ! $pembayaranIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Pembayaran', 'link' => null];
        }
        if ($isLocked) {
            return ['class' => 'badge-locked', 'icon' => null, 'text' => '🔒 Terkunci', 'link' => null];
        }
        if ($dokumen->status === 'selesai') {
            return ['class' => 'badge-selesai', 'icon' => null, 'text' => '✓ Selesai', 'link' => null];
        }
        if ($dokumen->status === 'returned_to_verifikasi') {
            return ['class' => 'badge-sent', 'icon' => 'fa-paper-plane', 'text' => 'Kembali ke Team Verifikasi', 'link' => null];
        }
        if ($dokumen->current_handler === 'akutansi'
            && ! in_array($dokumen->status, ['sent_to_pembayaran', 'selesai', 'completed', 'menunggu_di_approve', 'pending_approval_pembayaran'], true)) {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_akutansi' && $dokumen->current_handler !== 'akutansi') {
            return ['class' => 'badge-belum', 'icon' => null, 'text' => '⏳ Belum Diproses', 'link' => null];
        }
        if (in_array($dokumen->status, ['returned_to_operator', 'returned_to_department', 'dikembalikan'], true)) {
            return ['class' => 'badge-dikembalikan', 'icon' => null, 'text' => '← Dikembalikan', 'link' => null];
        }
        if ($dokumen->status === 'completed') {
            return ['class' => 'badge-selesai', 'icon' => null, 'text' => '✓ Selesai - Sudah Dibayar', 'link' => null];
        }

        return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
    }

    /**
     * Port kolom Deadline akutansi (_rows.blade.php:170-414) → objek siap-render.
     * Model "hitung naik" (umur sejak received_at), beku untuk sent/completed/
     * returned. Tanpa live-update klien (updater lama mati: data-attr mismatch).
     */
    protected static function buildDeadline(Dokumen $dokumen): array
    {
        $roleData   = $dokumen->getDataForRole('akutansi');
        $receivedAt = $roleData?->received_at;
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity)

        $isBypassedToPaymentDeadline = (
            $dokumen->current_handler === 'pembayaran'
            || $dokumen->status === 'completed'
            || $dokumen->status_pembayaran === 'sudah_dibayar'
            || ($pembayaranRoleData && $pembayaranRoleData->received_at)
        ) && ! $roleData?->received_at;

        $isSent = in_array($dokumen->status, ['sent_to_pembayaran', 'pending_approval_pembayaran', 'menunggu_di_approve'], true);
        $isCompleted = in_array($dokumen->status, ['selesai', 'completed', 'approved_data_sudah_terkirim'], true)
            || ($dokumen->status_pembayaran === 'sudah_dibayar');
        $isReturned = $dokumen->status === 'returned_to_verifikasi';

        // === Path A: sudah diterima akutansi → kartu umur ===
        if ($receivedAt) {
            $receivedAt = $receivedAt instanceof Carbon ? $receivedAt : Carbon::parse($receivedAt);
            $processedAt = $roleData?->processed_at;
            $timeFrozen = false;

            if (($isSent || $isCompleted || $isReturned) && $processedAt) {
                $endTime = $processedAt instanceof Carbon ? $processedAt : Carbon::parse($processedAt);
                $timeFrozen = true;
            } elseif ($isReturned) {
                $endTime = Carbon::now();
                $timeFrozen = true;
            } else {
                $endTime = Carbon::now();
            }

            $diff = $receivedAt->diff($endTime);

            $elapsedParts = [];
            if ($diff->days > 0)  { $elapsedParts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)     { $elapsedParts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($elapsedParts)) { $elapsedParts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $elapsedParts);
            if ($timeFrozen) { $ageText .= ' ⏸️'; }

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72) {
                $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle';
            } elseif ($totalHours >= 24) {
                $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle';
            } else {
                $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle';
            }

            if ($isSent || $isCompleted || $isReturned) {
                $ageColor = 'gray';
            } elseif ($totalHours >= 72) {
                $ageColor = 'red';
            } elseif ($totalHours >= 24) {
                $ageColor = 'yellow';
            } else {
                $ageColor = 'green';
            }

            $type = 'active';
            if ($isReturned)        { $type = 'paused'; }
            elseif ($isCompleted)   { $type = 'completed'; }
            elseif ($isSent)        { $type = 'sent'; }

            $footer = null;
            if ($isReturned) {
                $footer = ['kind' => 'paused', 'icon' => 'fa-pause-circle', 'text' => 'Berhenti Sementara'];
            } elseif ($isSent) {
                $footer = ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'];
            } elseif ($isCompleted) {
                $footer = ['kind' => 'completed', 'icon' => 'fa-check-circle', 'text' => 'Selesai'];
            }

            return [
                'variant'          => 'card',
                'type'             => $type,
                'color'            => $ageColor,
                'received_display' => $receivedAt->format('d M Y, H:i'),
                'indicator_icon'   => $ageIcon,
                'indicator_label'  => $ageLabel,
                'age_text'         => $ageText,
                'footer'           => $footer,
            ];
        }

        // === Path B: bypass akutansi (langsung ke pembayaran) ===
        if ($isBypassedToPaymentDeadline) {
            $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi'); // null (parity)
            $bypassTimestamp = $pembayaranRoleData?->received_at
                ?? $bypassVerifikasiData?->processed_at
                ?? $dokumen->tanggal_masuk;

            if (! $bypassTimestamp) {
                return static::deadlineSentFallback();
            }

            $bypassStartTime = $bypassTimestamp instanceof Carbon ? $bypassTimestamp : Carbon::parse($bypassTimestamp);
            $bypassProcessedAt = $bypassVerifikasiData?->processed_at ?? $pembayaranRoleData?->received_at;
            $bypassEndTime = $bypassProcessedAt
                ? ($bypassProcessedAt instanceof Carbon ? $bypassProcessedAt : Carbon::parse($bypassProcessedAt))
                : $bypassStartTime;

            $bypassDiff = $bypassStartTime->diff($bypassEndTime);
            $parts = [];
            if ($bypassDiff->days > 0) { $parts[] = $bypassDiff->days . ' hari'; }
            if ($bypassDiff->h > 0)    { $parts[] = $bypassDiff->h . ' jam'; }
            if ($bypassDiff->i > 0 || empty($parts)) { $parts[] = $bypassDiff->i . ' menit'; }
            $bypassAgeText = implode(' ', $parts) . ' ⏸️';

            $bypassTotalHours = ($bypassDiff->days * 24) + $bypassDiff->h;
            if ($bypassTotalHours >= 72) {
                $bypassAgeLabel = 'TERLAMBAT'; $bypassAgeIcon = 'fa-times-circle';
            } elseif ($bypassTotalHours >= 24) {
                $bypassAgeLabel = 'PERINGATAN'; $bypassAgeIcon = 'fa-exclamation-triangle';
            } else {
                $bypassAgeLabel = 'AMAN'; $bypassAgeIcon = 'fa-check-circle';
            }

            return [
                'variant'          => 'card',
                'type'             => 'sent',
                'color'            => 'gray',
                'received_display' => $bypassStartTime->format('d M Y, H:i'),
                'indicator_icon'   => $bypassAgeIcon,
                'indicator_label'  => $bypassAgeLabel,
                'age_text'         => $bypassAgeText,
                'footer'           => ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'],
            ];
        }

        // === Path C: belum diterima ===
        return ['variant' => 'none', 'type' => null, 'color' => null,
                'received_display' => null, 'indicator_icon' => null,
                'indicator_label' => null, 'age_text' => null, 'footer' => null];
    }

    protected static function deadlineSentFallback(): array
    {
        return ['variant' => 'sent_fallback', 'type' => 'sent', 'color' => 'gray',
                'received_display' => null, 'indicator_icon' => null,
                'indicator_label' => null, 'age_text' => null, 'footer' => null];
    }
}
