<?php

namespace App\Support;

use App\Helpers\DokumenHelper;
use App\Models\Dokumen;
use Carbon\Carbon;

/**
 * DTO baris tabel perpajakan (Tabulator, Rollout 2). Mewarisi derivasi bersama
 * dari App\Support\DocumentRow dan menambah bit khas perpajakan: is_at_my_role,
 * lock, can_edit(perpajakan), can_set_deadline, status_pembayaran, plus dua objek
 * siap-render (bentuk IDENTIK AkutansiDocumentRow agar formatter engine sama):
 *   - status_badge ← porting _rows.blade.php:14-118 + 587-632 (kolom Status).
 *   - deadline     ← porting _rows.blade.php:355-583 (kolom Deadline).
 * Klien hanya MERENDER objek ini; nol logika bisnis di JS.
 *
 * Prasyarat data (WAJIB sama dgn dokumens()): roleData load HANYA role_code=
 * 'perpajakan'; roleStatuses 4 role. getDataForRole('akutansi'/'pembayaran'/
 * 'team_verifikasi') → null tanpa query (parity byte tabel lama).
 */
class PerpajakanDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        $isLocked = DokumenHelper::isDocumentLocked($dokumen);
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');

        // is_at_my_role: dokumen sedang/pernah di perpajakan (paritas kolom aksi lama).
        $isAtMyRole = $dokumen->current_handler === 'perpajakan'
            || in_array($dokumen->status, ['sent_to_akutansi', 'sent_to_pembayaran', 'pending_approval_akutansi', 'pending_approval_pembayaran'], true)
            || (in_array($dokumen->status, ['completed', 'selesai'], true) && ! empty($dokumen->status_pembayaran));

        $row['is_at_my_role']       = $isAtMyRole;
        $row['is_locked']           = $isLocked;
        $row['lock_status_message'] = DokumenHelper::getLockedStatusMessage($dokumen);
        $row['lock_status_class']   = DokumenHelper::getLockStatusClass($dokumen);
        $row['can_edit']            = DokumenHelper::canEditDocument($dokumen, 'perpajakan');
        $row['can_set_deadline']    = DokumenHelper::canSetDeadline($dokumen)['can_set'];
        $row['status_pembayaran']   = $dokumen->status_pembayaran;

        $ctx = static::statusContext($dokumen);
        $row['status_badge'] = static::buildStatusBadge($dokumen, $isLocked, $ctx);
        $row['deadline']     = static::buildDeadline($dokumen, $ctx);

        return $row;
    }

    /**
     * Konteks status bersama (dipakai badge & deadline) — port _rows.blade.php:14-118.
     * Mengembalikan: is_rejected, sent_to_team (?string), is_pending_downstream (bool),
     * pending_downstream_team (?string), is_bypassed_to_pembayaran (bool — DIDEFINISIKAN
     * TAK-BERSYARAT, memperbaiki bug laten view lama yang merujuknya tanpa selalu men-set).
     */
    protected static function statusContext(Dokumen $dokumen): array
    {
        $statuses = $dokumen->roleStatuses;

        // === is_rejected (port :14-31) ===
        $isRejectedByPerpajakan = $statuses->where('role_code', 'perpajakan')->where('status', 'rejected')->isNotEmpty();
        $isReturnedFromAkutansi = $dokumen->status === 'returned_to_department' && $dokumen->return_source === 'akutansi';
        $isRejectedByAkutansi   = $statuses->where('role_code', 'akutansi')->where('status', 'rejected')->isNotEmpty();
        $isRejected = $isRejectedByPerpajakan || $isReturnedFromAkutansi || $isRejectedByAkutansi;

        // === display_status-first + fallback (port :33-118) ===
        $perpajakanDisplayStatus = $dokumen->getDisplayStatusForRole('perpajakan');
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
        $akutansiRoleData   = $dokumen->getDataForRole('akutansi');   // null (parity)
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran'); // null (parity)

        $akutansiHasApproved = $statuses->where('role_code', 'akutansi')->where('status', 'approved')->isNotEmpty();
        $akutansiIsPending   = $statuses->where('role_code', 'akutansi')->where('status', 'pending')->isNotEmpty();

        $sentToTeam = null;
        $isPendingDownstream = false;
        $pendingDownstreamTeam = null;
        $isBypassedToPembayaran = false;

        if ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'terkirim')) {
            $sentToTeam = match ($perpajakanDisplayStatus) {
                'terkirim_akutansi'   => 'Team Akutansi',
                'terkirim_pembayaran' => 'Team Pembayaran',
                'terkirim'            => 'Team Akutansi',
                default               => 'Team Akutansi',
            };
        } elseif ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'menunggu_approval')) {
            $isPendingDownstream = true;
            $pendingDownstreamTeam = match ($perpajakanDisplayStatus) {
                'menunggu_approval_akutansi'   => 'Team Akutansi',
                'menunggu_approval_pembayaran' => 'Team Pembayaran',
                default                        => 'Team Akutansi',
            };
        } else {
            $isBypassedToPembayaran = (
                $dokumen->current_handler === 'pembayaran'
                || $dokumen->status === 'completed'
                || $dokumen->status_pembayaran === 'sudah_dibayar'
                || ($pembayaranRoleData && $pembayaranRoleData->received_at)
            ) && ! $perpajakanRoleData?->received_at;

            if ($isBypassedToPembayaran) {
                $sentToTeam = 'Team Pembayaran';
            } elseif ($akutansiHasApproved || ($akutansiRoleData && $akutansiRoleData->received_at && ! $akutansiIsPending)) {
                $sentToTeam = 'Team Akutansi';
            }

            if ($akutansiIsPending && ! $sentToTeam) {
                $isPendingDownstream = true;
                $pendingDownstreamTeam = 'Team Akutansi';
            }
        }

        return [
            'is_rejected'               => $isRejected,
            'sent_to_team'              => $sentToTeam,
            'is_pending_downstream'     => $isPendingDownstream,
            'pending_downstream_team'   => $pendingDownstreamTeam,
            'is_bypassed_to_pembayaran' => $isBypassedToPembayaran,
        ];
    }

    /** Port badge Status _rows.blade.php:587-632 → {class, icon, text, link}. Urutan cabang DIPERTAHANKAN. */
    protected static function buildStatusBadge(Dokumen $dokumen, bool $isLocked, array $ctx): array
    {
        $statuses = $dokumen->roleStatuses;
        $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
        $akutansiIsPending  = $statuses->where('role_code', 'akutansi')->where('status', 'pending')->isNotEmpty();
        $pembayaranIsPending = $statuses->where('role_code', 'pembayaran')->where('status', 'pending')->isNotEmpty();

        if ($ctx['is_rejected']) {
            return [
                'class' => 'badge-dikembalikan',
                'icon'  => 'fa-times-circle',
                'text'  => 'Dokumen ditolak,',
                'link'  => [
                    'href' => route('returns.perpajakan.index') . '?search=' . urlencode((string) $dokumen->nomor_agenda),
                    'text' => 'cek disini',
                ],
            ];
        }
        if (! ($perpajakanRoleData?->received_at)
            && in_array($dokumen->current_handler, ['operator', 'team_verifikasi'], true)
            && ! in_array($dokumen->status, ['completed', 'selesai'], true)
            && $dokumen->status_pembayaran !== 'sudah_dibayar') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Draft', 'link' => null];
        }
        if ($ctx['is_pending_downstream']) {
            return ['class' => 'badge-warning', 'icon' => null, 'text' => '⏳ Menunggu Approval dari ' . $ctx['pending_downstream_team'], 'link' => null];
        }
        if ($ctx['sent_to_team']) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke ' . $ctx['sent_to_team'], 'link' => null];
        }
        if ($dokumen->status === 'sent_to_akutansi' && ! $akutansiIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Team Akutansi', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_pembayaran' && ! $pembayaranIsPending) {
            return ['class' => 'badge-sent', 'icon' => null, 'text' => '📤 Terkirim ke Team Pembayaran', 'link' => null];
        }
        if ($dokumen->status === 'sent_to_perpajakan' && $dokumen->current_handler === 'perpajakan') {
            return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
        }
        if ($isLocked) {
            return ['class' => 'badge-locked', 'icon' => null, 'text' => '🔒 Terkunci - Menunggu Deadline', 'link' => null];
        }
        if ($dokumen->status === 'pending_approval_perpajakan') {
            return ['class' => 'badge-warning', 'icon' => null, 'text' => '📥 Baru Diterima', 'link' => null];
        }
        if ($dokumen->status === 'returned_to_verifikasi') {
            return ['class' => 'badge-sent', 'icon' => 'fa-paper-plane', 'text' => 'Kembali ke Team Verifikasi', 'link' => null];
        }
        // 'sedang diproses' & else → sama.
        return ['class' => 'badge-proses', 'icon' => null, 'text' => '⏳ Sedang Diproses', 'link' => null];
    }

    /** Port kolom Deadline _rows.blade.php:355-583 → objek siap-render. Count-up, beku utk sent/completed/returned. */
    protected static function buildDeadline(Dokumen $dokumen, array $ctx): array
    {
        $roleData   = $dokumen->getDataForRole('perpajakan');
        $receivedAt = $roleData?->received_at;

        $isSent = in_array($dokumen->status, ['sent_to_akutansi', 'sent_to_pembayaran', 'pending_approval_akutansi', 'pending_approval_pembayaran'], true);
        $isCompleted = in_array($dokumen->status, ['selesai', 'completed', 'approved_data_sudah_terkirim'], true)
            || ($dokumen->status_pembayaran === 'sudah_dibayar');
        $isReturned = $dokumen->status === 'returned_to_verifikasi';

        // === Path A: sudah diterima perpajakan ===
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
            $parts = [];
            if ($diff->days > 0) { $parts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)    { $parts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($parts)) { $parts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $parts);
            if ($timeFrozen) { $ageText .= ' ⏸️'; }

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72)      { $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle'; }
            elseif ($totalHours >= 24)  { $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle'; }
            else                        { $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle'; }

            if ($isSent || $isCompleted || $isReturned) { $ageColor = 'gray'; }
            elseif ($totalHours >= 72)  { $ageColor = 'red'; }
            elseif ($totalHours >= 24)  { $ageColor = 'yellow'; }
            else                        { $ageColor = 'green'; }

            $type = 'active';
            if ($isReturned)       { $type = 'paused'; }
            elseif ($isCompleted)  { $type = 'completed'; }
            elseif ($isSent)       { $type = 'sent'; }

            $footer = null;
            if ($isReturned)       { $footer = ['kind' => 'paused', 'icon' => 'fa-pause-circle', 'text' => 'Berhenti Sementara']; }
            elseif ($isSent)       { $footer = ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim']; }
            elseif ($isCompleted)  { $footer = ['kind' => 'completed', 'icon' => 'fa-check-circle', 'text' => 'Selesai']; }

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

        // === Path B: bypass perpajakan (langsung ke pembayaran) ===
        if ($ctx['is_bypassed_to_pembayaran']) {
            $bypassPembayaranData = $dokumen->getDataForRole('pembayaran');       // null (parity)
            $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi');  // null (parity)
            $bypassTimestamp = $bypassPembayaranData?->received_at
                ?? $bypassVerifikasiData?->processed_at
                ?? $dokumen->tanggal_masuk;

            if (! $bypassTimestamp) {
                return ['variant' => 'sent_fallback', 'type' => 'sent', 'color' => 'gray',
                        'received_display' => null, 'indicator_icon' => null, 'indicator_label' => null,
                        'age_text' => null, 'footer' => null];
            }

            $start = $bypassTimestamp instanceof Carbon ? $bypassTimestamp : Carbon::parse($bypassTimestamp);
            $processed = $bypassVerifikasiData?->processed_at ?? $bypassPembayaranData?->received_at;
            $end = $processed ? ($processed instanceof Carbon ? $processed : Carbon::parse($processed)) : $start;

            $diff = $start->diff($end);
            $parts = [];
            if ($diff->days > 0) { $parts[] = $diff->days . ' hari'; }
            if ($diff->h > 0)    { $parts[] = $diff->h . ' jam'; }
            if ($diff->i > 0 || empty($parts)) { $parts[] = $diff->i . ' menit'; }
            $ageText = implode(' ', $parts) . ' ⏸️';

            $totalHours = ($diff->days * 24) + $diff->h;
            if ($totalHours >= 72)     { $ageLabel = 'TERLAMBAT'; $ageIcon = 'fa-times-circle'; }
            elseif ($totalHours >= 24) { $ageLabel = 'PERINGATAN'; $ageIcon = 'fa-exclamation-triangle'; }
            else                       { $ageLabel = 'AMAN'; $ageIcon = 'fa-check-circle'; }

            return [
                'variant'          => 'card',
                'type'             => 'sent',
                'color'            => 'gray',
                'received_display' => $start->format('d M Y, H:i'),
                'indicator_icon'   => $ageIcon,
                'indicator_label'  => $ageLabel,
                'age_text'         => $ageText,
                'footer'           => ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'],
            ];
        }

        // === Path C: belum diterima ===
        return ['variant' => 'none', 'type' => null, 'color' => null,
                'received_display' => null, 'indicator_icon' => null, 'indicator_label' => null,
                'age_text' => null, 'footer' => null];
    }
}
