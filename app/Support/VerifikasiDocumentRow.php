<?php

namespace App\Support;

use App\Helpers\DokumenHelper;
use App\Models\Dokumen;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * DTO baris tabel team_verifikasi (Tabulator, Rollout 3). Mewarisi derivasi
 * bersama dari App\Support\DocumentRow dan menambah bit khas verifikasi:
 * is_at_my_role, is_locked (SELALU false — dokumen tak lagi terkunci pasca-
 * approval untuk role ini, lihat _rows.blade.php:49-52), can_edit(team_verifikasi),
 * status_pembayaran, pemaraf, plus dua objek siap-render:
 *   - status_badge ← porting _rows.blade.php:455-608 (~24 cabang, kolom Status).
 *   - deadline     ← porting _rows.blade.php:300-451 (kolom Deadline, count-up).
 * Klien (document-tabulator.js) hanya MERENDER objek ini; nol logika bisnis di JS.
 *
 * status_badge di sini punya DUA field tambahan vs Akutansi/Perpajakan:
 * 'style' (gradient inline utk badge menunggu-approval) dan 'title' (tooltip
 * badge "Kembali dari ..."). Keduanya null bila tak dipakai cabang tsb.
 *
 * Prasyarat data (WAJIB sama dengan TeamVerifikasiController::dokumens():456-464):
 * roleData eager-load HANYA role_code='team_verifikasi'; roleStatuses eager-load
 * whereIn 4 role (team_verifikasi, perpajakan, akutansi, pembayaran); dibayarKepadas
 * & dokumenPos ter-eager-load (dipakai baseRow()). Dengan roleData role-own-only,
 * getDataForRole('perpajakan'/'akutansi'/'pembayaran') SELALU null tanpa query —
 * paritas persis tabel lama (termasuk cabang fallback statusContext() yang secara
 * laten tak pernah menyala karena bergantung pada roleData lintas-role itu; port
 * apa adanya, JANGAN "diperbaiki" jadi query lintas-role).
 */
class VerifikasiDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions, ?string $viewerRole = null): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        // is_at_my_role: port TeamVerifikasiController::dokumens():497-514.
        $isAtMyRole = in_array($dokumen->current_handler, ['team_verifikasi'], true)
            || in_array($dokumen->status, [
                'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
                'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_pembayaran',
                'menunggu_di_approve', 'waiting_approval_perpajakan', 'waiting_approval_akuntansi',
                'waiting_approval_pembayaran', 'returned_to_verifikasi', 'sedang diproses', 'sedang_diproses',
            ], true)
            || (in_array($dokumen->status, ['completed', 'selesai'], true) && ! empty($dokumen->status_pembayaran))
            || ($dokumen->status === 'returned_to_department'
                && in_array($dokumen->return_source, ['perpajakan', 'akutansi'], true)
                && $dokumen->current_handler === 'team_verifikasi');

        $row['is_at_my_role'] = $isAtMyRole;
        // Dokumen TIDAK LAGI terkunci pasca-approval untuk verifikasi (paritas _rows.blade.php:49-52).
        // JANGAN panggil DokumenHelper::isDocumentLocked() di sini — override ke false selalu.
        $row['is_locked'] = false;
        $row['can_edit'] = DokumenHelper::canEditDocument($dokumen, 'team_verifikasi');
        $row['status_pembayaran'] = $dokumen->status_pembayaran;
        // Kolom Paraf: mentah, read-only. tanggal_paraf sudah tersedia via baseRow()->dates.
        $row['pemaraf'] = $dokumen->pemaraf;

        $ctx = static::statusContext($dokumen);
        $row['status_badge'] = static::buildStatusBadge($dokumen, $ctx);
        $row['deadline'] = static::buildDeadline($dokumen);

        return $row;
    }

    /**
     * Konteks status bersama (dipakai badge cascade) — port setup _rows.blade.php:3-119
     * (+ flag :307 is_returned_to_verifikasi, dipakai badge cabang 1 & buildDeadline()).
     */
    protected static function statusContext(Dokumen $dokumen): array
    {
        $isReturnedToVerifikasi = $dokumen->status === 'returned_to_verifikasi';

        // :20-23
        $isRejectedByTeamVerifikasi = $dokumen->roleStatuses
            ->where('role_code', 'team_verifikasi')
            ->where('status', 'rejected')
            ->isNotEmpty();

        // :25-39 — hanya dievaluasi bila current_handler === 'team_verifikasi'.
        $isRejectedByOtherRole = false;
        $rejectedByRole = null;
        if ($dokumen->current_handler === 'team_verifikasi') {
            $rejectedStatus = $dokumen->roleStatuses
                ->whereIn('role_code', ['perpajakan', 'akutansi'])
                ->where('status', 'rejected')
                ->sortByDesc('status_changed_at')
                ->first();

            if ($rejectedStatus) {
                $isRejectedByOtherRole = true;
                $rejectedByRole = $rejectedStatus->role_code;
            }
        }

        // :41
        $isRejected = $isRejectedByTeamVerifikasi || $isRejectedByOtherRole;

        // :44-47
        $isPending = $dokumen->roleStatuses
            ->where('role_code', 'team_verifikasi')
            ->where('status', 'pending')
            ->isNotEmpty();

        // :72, :75
        $roleData = $dokumen->getDataForRole('team_verifikasi');
        $displayStatus = $roleData?->display_status;
        $displayStatusLabel = $displayStatus ? Dokumen::getFinalStatusLabel($displayStatus) : null;

        // Fallback (HANYA saat display_status null) — port :83-118 VERBATIM.
        $sentToTeamLabel = null;
        $isPendingPerpajakan = false;
        $isPendingAkuntansi = false;

        if (! $displayStatus) {
            if ($dokumen->status === 'waiting_approval_perpajakan') {
                $isPendingPerpajakan = true;
            } elseif ($dokumen->status === 'waiting_approval_akuntansi') {
                $isPendingAkuntansi = true;
            } else {
                // :94 — getDataForRole('perpajakan') SELALU null (parity: eager-load
                // role-own-only). Cabang "$perpajakanRoleData && ->received_at" di
                // $wasSentToPerpajakan karena itu tak pernah menyala. Port apa adanya
                // (jangan "perbaiki" jadi query) — dua kondisi OR lain tetap berfungsi.
                $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');

                $wasSentToPerpajakan = (
                    ($perpajakanRoleData && $perpajakanRoleData->received_at)
                    || in_array($dokumen->status, ['sent_to_perpajakan', 'pending_approval_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran'], true)
                    || in_array($dokumen->current_handler, ['perpajakan', 'akutansi', 'pembayaran'], true)
                );

                if ($wasSentToPerpajakan && ! $isRejected) {
                    $perpajakanIsPendingInbox = $dokumen->roleStatuses
                        ->where('role_code', 'perpajakan')
                        ->where('status', 'pending')
                        ->isNotEmpty();

                    if ($perpajakanIsPendingInbox) {
                        $isPendingPerpajakan = true;
                    } else {
                        $sentToTeamLabel = 'Team Perpajakan';
                    }
                }
            }
        }

        return [
            'is_returned_to_verifikasi' => $isReturnedToVerifikasi,
            'is_rejected_by_team_verifikasi' => $isRejectedByTeamVerifikasi,
            'is_rejected_by_other_role' => $isRejectedByOtherRole,
            'rejected_by_role' => $rejectedByRole,
            'is_rejected' => $isRejected,
            'is_pending' => $isPending,
            'display_status' => $displayStatus,
            'display_status_label' => $displayStatusLabel,
            'is_pending_perpajakan' => $isPendingPerpajakan,
            'is_pending_akuntansi' => $isPendingAkuntansi,
            'sent_to_team_label' => $sentToTeamLabel,
        ];
    }

    /**
     * Port cascade badge Status _rows.blade.php:455-608 (~24 cabang) →
     * {class, icon, text, link, style, title}. Urutan cabang DIPERTAHANKAN persis.
     */
    protected static function buildStatusBadge(Dokumen $dokumen, array $ctx): array
    {
        $status = $dokumen->status;
        // Paritas: verifikasi tak pernah locked (lihat is_locked di fromDokumen).
        // Cabang 17 & 20 di bawah karenanya tak pernah menyala — port tetap
        // dipertahankan sesuai instruksi paritas.
        $isLocked = false;
        $gradientKuning = 'background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;';

        // 1: Kembali dari role lain ke Team Verifikasi.
        if ($ctx['is_returned_to_verifikasi']) {
            $returnedByLabel = match ($dokumen->return_source) {
                'perpajakan' => 'Team Perpajakan',
                'akutansi' => 'Team Akutansi',
                'pembayaran' => 'Team Pembayaran',
                default => Str::title($dokumen->return_source ?? 'Role Terkait'),
            };

            return static::badge(
                'badge-proses',
                'fa-inbox',
                'Kembali dari '.$returnedByLabel,
                title: $dokumen->return_reason ? 'Catatan: '.$dokumen->return_reason : 'Kembali ke Team Verifikasi'
            );
        }

        // 2a/2b: Ditolak.
        if ($ctx['is_rejected']) {
            if ($ctx['is_rejected_by_other_role'] && $ctx['rejected_by_role']) {
                return static::badge('badge-dikembalikan', 'fa-times-circle', 'Dokumen ditolak');
            }

            return static::badge('badge-proses', null, '⏳ Draft');
        }

        // 3: Selesai.
        if ($status === 'selesai' || $status === 'approved_Team Verifikasi') {
            return static::badge('badge-selesai', null, '✓ '.($status === 'approved_Team Verifikasi' ? 'Approved' : 'Selesai'));
        }

        // 4: Rejected_Team Verifikasi.
        if ($status === 'rejected_Team Verifikasi') {
            return static::badge('badge-dikembalikan', null, 'Rejected');
        }

        // 5: returned_to_bidang.
        if ($status === 'returned_to_bidang') {
            return static::badge('badge-dikembalikan', null, 'Dikembalikan ke '.($dokumen->return_source ? strtoupper($dokumen->return_source) : 'Bagian'));
        }

        // 6a-6e: display_status_label (FINAL/frozen).
        if ($ctx['display_status_label']) {
            $displayStatus = $ctx['display_status'];

            if (str_starts_with($displayStatus, 'terkirim')) {
                return static::badge('badge-sent', null, '📤 '.$ctx['display_status_label']);
            }
            if (str_starts_with($displayStatus, 'menunggu')) {
                return static::badge('badge-warning', 'fa-clock', $ctx['display_status_label']);
            }
            if ($displayStatus === 'sedang_diproses') {
                return static::badge('badge-proses', null, '⏳ '.$ctx['display_status_label']);
            }
            if ($displayStatus === 'terkunci') {
                return static::badge('badge-locked', null, '🔒 '.$ctx['display_status_label']);
            }

            return static::badge('badge-proses', null, $ctx['display_status_label']);
        }

        // 7/8: Fallback pending (legacy, tanpa display_status).
        if ($ctx['is_pending_perpajakan']) {
            return static::badge('badge-warning', 'fa-clock', 'Menunggu Approval dari Team Perpajakan', style: $gradientKuning);
        }
        if ($ctx['is_pending_akuntansi']) {
            return static::badge('badge-warning', 'fa-clock', 'Menunggu Approval dari Team Akutansi', style: $gradientKuning);
        }

        // 9: Fallback terkirim (legacy).
        if ($ctx['sent_to_team_label']) {
            return static::badge('badge-sent', null, '📤 Terkirim ke '.$ctx['sent_to_team_label']);
        }

        // 10-12: sent_to_*.
        if ($status === 'sent_to_perpajakan') {
            return static::badge('badge-sent', null, '📤 Terkirim ke Team Perpajakan');
        }
        if ($status === 'sent_to_akutansi') {
            return static::badge('badge-sent', null, '📤 Terkirim ke Team Akutansi');
        }
        if ($status === 'sent_to_pembayaran') {
            return static::badge('badge-sent', null, '📤 Terkirim ke Team Pembayaran');
        }

        // 13-15: waiting_approval_* (bulk send).
        if ($status === 'waiting_approval_perpajakan') {
            return static::badge('badge-warning', 'fa-clock', 'Menunggu Approve Team Perpajakan', style: $gradientKuning);
        }
        if ($status === 'waiting_approval_akuntansi') {
            return static::badge('badge-warning', 'fa-clock', 'Menunggu Approve Team Akuntansi', style: $gradientKuning);
        }
        if ($status === 'waiting_approval_pembayaran') {
            return static::badge('badge-warning', 'fa-clock', 'Menunggu Approve Team Pembayaran', style: 'background: linear-gradient(135deg, #6610f2 0%, #9b4dca 100%); color: white;');
        }

        // 16: Pending approval umum (class kosong pada legacy — hanya "badge-status").
        if (in_array($status, ['menunggu_di_approve', 'waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_team_verifikasi'], true)
            || $ctx['is_pending']) {
            return static::badge('', 'fa-clock', $dokumen->getDetailedApprovalText(), style: $gradientKuning);
        }

        // 17/18: sedang diproses.
        if ($status === 'sedang diproses' && $isLocked) {
            return static::badge('badge-locked', null, '🔒 Terkunci');
        }
        if ($status === 'sedang diproses') {
            return static::badge('badge-proses', null, '⏳ Sedang Diproses');
        }

        // 19/20: sent_to_team_verifikasi.
        if ($status === 'sent_to_team_verifikasi' && ! $isLocked) {
            return static::badge('badge-proses', null, '⏳ Diproses');
        }
        if ($status === 'sent_to_team_verifikasi' && $isLocked) {
            return static::badge('badge-locked', null, '🔒 Terkunci');
        }

        // 21: returned_to_operator.
        if ($status === 'returned_to_operator') {
            return static::badge('badge-dikembalikan', null, 'Dikembalikan ke Ibu A');
        }

        // 22a/22b: returned_to_department.
        if ($status === 'returned_to_department') {
            $isWorkflowReturn = in_array($dokumen->return_source, ['perpajakan', 'akutansi', 'pembayaran'], true)
                && $dokumen->current_handler === 'team_verifikasi'
                && ! $ctx['is_rejected'];

            $workflowReturnLabel = match ($dokumen->return_source) {
                'perpajakan' => 'Team Perpajakan',
                'akutansi' => 'Team Akutansi',
                'pembayaran' => 'Team Pembayaran',
                default => Str::title($dokumen->return_source ?? 'Bagian Terkait'),
            };

            if ($isWorkflowReturn) {
                return static::badge('badge-proses', 'fa-inbox', 'Kembali dari '.$workflowReturnLabel);
            }

            return static::badge('badge-dikembalikan', null, 'Dikembalikan dari '.Str::title($dokumen->return_source ?? 'Bagian Terkait'));
        }

        // 23: returned_from_*.
        if (Str::startsWith($status, 'returned_from_')) {
            $source = Str::after($status, 'returned_from_');
            $sourceLabel = match ($source) {
                'akutansi' => 'Team Akutansi',
                'perpajakan' => 'Team Perpajakan',
                default => Str::title(str_replace('_', ' ', $source)),
            };

            return static::badge('badge-dikembalikan', null, 'Dikembalikan dari '.$sourceLabel);
        }

        // 24: Fallback umum.
        return static::badge('badge-proses', null, '⏳ '.ucfirst($status));
    }

    /** Helper pembentuk objek status_badge (menghindari duplikasi array literal 6 kunci). */
    protected static function badge(
        string $class,
        ?string $icon,
        string $text,
        ?string $link = null,
        ?string $style = null,
        ?string $title = null
    ): array {
        return [
            'class' => $class,
            'icon' => $icon,
            'text' => $text,
            'link' => $link,
            'style' => $style,
            'title' => $title,
        ];
    }

    /**
     * Port kolom Deadline _rows.blade.php:300-451 → objek siap-render. Count-up
     * murni: HANYA Path A (sudah diterima → kartu umur) dan Path C (belum
     * diterima → 'none'). Verifikasi TIDAK punya jalur bypass (Path B) — legacy
     * tidak mengimplementasikannya, jadi tidak ditambahkan di sini.
     *
     * Beda penting vs Akutansi/Perpajakan: "pause" di sini berarti dokumen
     * MENINGGALKAN Team Verifikasi (kembali ke operator/bagian), BUKAN saat
     * dokumen returned_to_verifikasi (yang justru tetap menghitung jalan —
     * lihat komentar _rows.blade.php:305-306). age_text di sini juga TIDAK
     * mendapat sufiks '⏸️' (beda dari Akutansi/Perpajakan) — legacy verifikasi
     * tidak menambahkannya.
     */
    protected static function buildDeadline(Dokumen $dokumen): array
    {
        $roleData = $dokumen->getDataForRole('team_verifikasi');
        $receivedAt = $roleData?->received_at;
        $displayStatus = $roleData?->display_status;

        // Pause hanya saat dokumen MENINGGALKAN Team Verifikasi. Jika role lain
        // mengembalikannya KE Team Verifikasi, timer tetap jalan (bukan pause).
        $isReturnedAwayFromVerifikasi = in_array($dokumen->status, ['returned_to_operator', 'returned_to_bidang'], true)
            || $dokumen->current_handler === 'operator'
            || str_starts_with((string) $dokumen->current_handler, 'bagian_');
        $isPaused = $isReturnedAwayFromVerifikasi;

        $isSent = in_array($dokumen->status, [
            'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
            'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_pembayaran',
            'waiting_approval_perpajakan', 'waiting_approval_akuntansi',
        ], true) || $isPaused;

        // display_status beku (dokumen "tembak") — status asli mungkin sudah
        // lewat role ini, tapi display_status tetap 'terkirim...'.
        if (! $isSent && $displayStatus && str_starts_with($displayStatus, 'terkirim')) {
            $isSent = true;
        }

        $isCompleted = in_array($dokumen->status, ['selesai', 'completed', 'approved_data_sudah_terkirim'], true)
            || ($dokumen->status_pembayaran === 'sudah_dibayar');

        // === Path C: belum diterima ===
        if (! $receivedAt) {
            return ['variant' => 'none', 'type' => null, 'color' => null,
                'received_display' => null, 'indicator_icon' => null,
                'indicator_label' => null, 'age_text' => null, 'footer' => null];
        }

        // === Path A: sudah diterima team_verifikasi → kartu umur ===
        $receivedAt = $receivedAt instanceof Carbon ? $receivedAt : Carbon::parse($receivedAt);
        $processedAt = $roleData?->processed_at;

        if (($isSent || $isCompleted || $isPaused) && $processedAt) {
            $endTime = $processedAt instanceof Carbon ? $processedAt : Carbon::parse($processedAt);
        } else {
            $endTime = Carbon::now();
        }

        $diff = $receivedAt->diff($endTime);

        $elapsedParts = [];
        if ($diff->days > 0) {
            $elapsedParts[] = $diff->days.' hari';
        }
        if ($diff->h > 0) {
            $elapsedParts[] = $diff->h.' jam';
        }
        if ($diff->i > 0 || empty($elapsedParts)) {
            $elapsedParts[] = $diff->i.' menit';
        }
        $ageText = implode(' ', $elapsedParts);

        $totalHours = ($diff->days * 24) + $diff->h;
        if ($totalHours >= 72) {
            $ageLabel = 'TERLAMBAT';
            $ageIcon = 'fa-times-circle';
        } elseif ($totalHours >= 24) {
            $ageLabel = 'PERINGATAN';
            $ageIcon = 'fa-exclamation-triangle';
        } else {
            $ageLabel = 'AMAN';
            $ageIcon = 'fa-check-circle';
        }

        if ($isSent || $isCompleted || $isPaused) {
            $ageColor = 'gray';
        } elseif ($totalHours >= 72) {
            $ageColor = 'red';
        } elseif ($totalHours >= 24) {
            $ageColor = 'yellow';
        } else {
            $ageColor = 'green';
        }

        // Prioritas: paused > sent > completed > active (sama seperti legacy :407-414).
        $type = 'active';
        if ($isPaused) {
            $type = 'paused';
        } elseif ($isSent) {
            $type = 'sent';
        } elseif ($isCompleted) {
            $type = 'completed';
        }

        $footer = null;
        if ($isPaused) {
            $footer = ['kind' => 'paused', 'icon' => 'fa-pause-circle', 'text' => 'Berhenti Sementara'];
        } elseif ($isSent) {
            $footer = ['kind' => 'sent', 'icon' => 'fa-paper-plane', 'text' => 'Terkirim'];
        } elseif ($isCompleted) {
            $footer = ['kind' => 'completed', 'icon' => 'fa-check-circle', 'text' => 'Selesai'];
        }

        return [
            'variant' => 'card',
            'type' => $type,
            'color' => $ageColor,
            'received_display' => $receivedAt->format('d M Y, H:i'),
            'indicator_icon' => $ageIcon,
            'indicator_label' => $ageLabel,
            'age_text' => $ageText,
            'footer' => $footer,
        ];
    }
}
