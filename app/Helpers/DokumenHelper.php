<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Dokumen;
use Illuminate\Support\Str;

class DokumenHelper
{
    /**
     * Check if document is locked and cannot be edited
     */
    public static function isDocumentLocked(Dokumen $dokumen): bool
    {
        if (Str::startsWith($dokumen->status, 'returned_') || $dokumen->status === 'returned_to_department') {
            return false;
        }

        // Dokumen yang sedang menunggu approval tidak bisa diedit
        // TAPI untuk perpajakan, dokumen yang sudah dikirim ke akutansi/pembayaran tidak terkunci
        if (
            in_array($dokumen->status, [
                'waiting_reviewer_approval',
                'pending_approval_Team Verifikasi',
                'pending_approval_perpajakan',
            ])
        ) {
            return true; // Lock dokumen yang sedang menunggu approval
        }

        // Untuk 'menunggu_di_approve' (status untuk pembayaran),
        // JANGAN lock dokumen yang sudah dikirim ke pembayaran dari role manapun
        // Dokumen dengan status ini berarti sedang menunggu approval di inbox pembayaran
        // Logika locking akan di-handle di switch case berdasarkan current_handler
        // Untuk role pengirim (akutansi, perpajakan), dokumen tidak terkunci di halaman mereka
        if ($dokumen->status === 'menunggu_di_approve') {
            // Jika current_handler adalah pembayaran, berarti dokumen sudah di inbox pembayaran, tidak lock
            if ($dokumen->current_handler === 'pembayaran') {
                return false;
            }
            // Jika current_handler adalah akutansi atau perpajakan, berarti dokumen sudah dikirim dari role tersebut
            // Tidak lock di halaman role pengirim - lanjutkan ke switch case untuk pengecekan lebih detail
            // Switch case untuk 'akutansi' dan 'perpajakan' akan return false untuk dokumen dengan status ini
            // Tidak perlu return true di sini, biarkan switch case yang menangani
        }

        // Untuk pending_approval_akutansi dan pending_approval_pembayaran,
        // JANGAN lock dokumen yang sudah dikirim dari role pengirim
        // Logika locking akan di-handle di switch case berdasarkan current_handler
        if (in_array($dokumen->status, ['pending_approval_akutansi', 'pending_approval_pembayaran'])) {
            // Jika current_handler adalah role tujuan, berarti dokumen sudah di inbox, tidak lock
            if ($dokumen->status === 'pending_approval_akutansi' && $dokumen->current_handler === 'akutansi') {
                return false;
            }
            if ($dokumen->status === 'pending_approval_pembayaran' && $dokumen->current_handler === 'pembayaran') {
                return false;
            }
            // Jika current_handler adalah perpajakan, berarti dokumen sudah dikirim dari perpajakan
            // Tidak lock di halaman perpajakan (akan di-handle di switch case)
            // Hanya lanjutkan ke switch case untuk pengecekan lebih detail
        }

        // Check if document has pending status in dokumen_statuses
        // TAPI untuk perpajakan, jangan lock dokumen yang sudah dikirim ke akutansi/pembayaran
        $hasPendingStatus = $dokumen->roleStatuses()
            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
            ->exists();

        if ($hasPendingStatus) {
            // Skip lock untuk handler yang menggunakan sistem deadline baru (count-up dari received_at)
            // Perpajakan, Team Verifikasi, dan akutansi tidak perlu di-lock berdasarkan pending status
            // karena mereka menggunakan sistem deadline otomatis
            if (in_array($dokumen->current_handler, ['perpajakan', 'Team Verifikasi', 'akutansi'])) {
                // Handler menggunakan sistem baru, pending status tidak lock dokumen
                // Biarkan logika lanjut ke switch case di bawah
            } else {
                return true; // Lock dokumen yang memiliki pending status untuk handler lainnya
            }
        }

        // Get deadline from dokumen_role_data based on current handler
        $roleCode = strtolower($dokumen->current_handler ?? '');
        $roleData = $dokumen->getDataForRole($roleCode);
        $hasDeadline = $roleData && $roleData->deadline_at;

        // JANGAN lock dokumen yang sudah terkirim ke akutansi/pembayaran (untuk handler selain akutansi/pembayaran)
        // TAPI untuk handler akutansi/pembayaran sendiri, dokumen dengan status sent_to_* HARUS lock jika belum ada deadline
        // Dokumen ini sudah pindah ke role lain, jadi tidak perlu lock di halaman perpajakan
        if (in_array($dokumen->status, ['sent_to_akutansi', 'sent_to_pembayaran'])) {
            // Jika current_handler adalah akutansi/pembayaran, biarkan logika di switch case yang menangani
            // Jika current_handler bukan akutansi/pembayaran, berarti dokumen sudah pindah ke role lain, tidak lock
            if (!in_array($dokumen->current_handler, ['akutansi', 'pembayaran'])) {
                return false; // Dokumen sudah terkirim ke role lain, tidak terkunci
            }
            // Jika current_handler adalah akutansi/pembayaran, lanjutkan ke logika di bawah (switch case)
        }

        // Base condition: must be sent to department without deadline
        $isLocked = !$hasDeadline &&
            in_array($dokumen->status, [
                'sent_to_Team Verifikasi',
                'sedang diproses', // Dokumen yang baru di-approve dari inbox Team Verifikasi
                'sent_to_perpajakan'
            ]);

        // NEW SYSTEM: Documents are NO LONGER locked after approval
        // Deadline is now determined by database config and calculated from received_at (count up)
        // Documents can be edited immediately after approval
        // Only lock documents that are pending approval
        switch ($dokumen->current_handler) {
            case 'Team Verifikasi':
            case 'akutansi':
            case 'perpajakan':
                // Documents are NOT locked after approval
                // Deadline is calculated from received_at automatically
                $isLocked = false;
                break;
            case 'pembayaran':
                // Lock dokumen dengan status 'sent_to_pembayaran' TAPI hanya jika tidak punya deadline
                // TAPI jika status sudah 'sent_to_pembayaran', berarti sudah approve, tidak lock (sudah di-handle di atas)
                $isLocked = !$hasDeadline && $dokumen->status === 'sent_to_pembayaran';
                break;
            default:
                // Untuk dokumen yang current_handler bukan perpajakan tapi masih muncul di halaman perpajakan
                // (karena query menampilkan dokumen dengan status sent_to_akutansi/pembayaran)
                // Jangan lock dokumen ini karena sudah pindah ke role lain (sudah di-handle di atas)
                $isLocked = false;
                break;
        }

        // Don't lock documents that were returned and repaired (only if they have been fixed)
        // Check if document was returned but has been fixed and re-sent
        if ($dokumen->department_returned_at) {
            // Document was returned from department, check if it has been fixed
            // If it has a deadline set, it means it's been fixed and re-sent
            if ($hasDeadline) {
                $isLocked = false;
            }
        }

        return $isLocked;
    }

    /**
     * Get locked document status message
     */
    public static function getLockedStatusMessage(Dokumen $dokumen): string
    {
        if (self::isDocumentLocked($dokumen)) {
            $handlerName = match ($dokumen->current_handler) {
                'Team Verifikasi' => 'Team Verifikasi',
                'akutansi' => 'Team Akutansi',
                'perpajakan' => 'Team Perpajakan',
                'pembayaran' => 'Pembayaran',
                default => 'Admin'
            };
            return "🔒 Dokumen terkunci - {$handlerName} harus menetapkan deadline terlebih dahulu";
        }

        // Check deadline from dokumen_role_data
        $roleCode = strtolower($dokumen->current_handler ?? '');
        $roleData = $dokumen->getDataForRole($roleCode);
        if ($roleData && $roleData->deadline_at && $roleData->deadline_at->isPast()) {
            return '⏰ Deadline lewat - segera atur ulang';
        }

        return '✓ Dokumen dapat diedit';
    }

    /**
     * Check if document can be edited by current user
     */
    public static function canEditDocument(Dokumen $dokumen, ?string $userRole = null): bool
    {
        // KHUSUS UNTUK ROLE PEMBAYARAN: Logic terpisah dan diprioritaskan
        // Pembayaran memiliki aturan khusus yang berbeda dari role lain
        if ($userRole && strtolower($userRole) === 'pembayaran') {
            return self::canPembayaranEditDocument($dokumen);
        }

        // Logic untuk role lain (bukan pembayaran)
        // If document is locked, cannot edit
        if (self::isDocumentLocked($dokumen)) {
            return false;
        }

        // Dokumen yang sedang menunggu approval tidak bisa diedit
        if (
            in_array($dokumen->status, [
                'menunggu_di_approve',
                'waiting_reviewer_approval',
                'pending_approval_Team Verifikasi',
                'pending_approval_perpajakan',
                'pending_approval_akutansi'
            ])
        ) {
            return false;
        }

        // Check if document has pending status in dokumen_statuses
        $hasPendingStatus = $dokumen->roleStatuses()
            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
            ->exists();

        if ($hasPendingStatus) {
            return false; // Cannot edit document with pending status
        }

        // If user role is provided, check if they can edit
        if ($userRole) {
            $userRoleLower = strtolower($userRole);
            $currentHandlerLower = strtolower($dokumen->current_handler ?? '');

            // Untuk role lain, gunakan logic standar
            return $currentHandlerLower === $userRoleLower;
        }

        return true;
    }

    /**
     * Check if Pembayaran role can edit document
     * Pembayaran memiliki aturan khusus: bisa edit dokumen yang sudah sampai di pembayaran
     */
    private static function canPembayaranEditDocument(Dokumen $dokumen): bool
    {
        $currentHandlerLower = strtolower($dokumen->current_handler ?? '');
        $status = $dokumen->status ?? '';

        // 1. Jika current_handler adalah pembayaran, bisa edit
        if ($currentHandlerLower === 'pembayaran') {
            return true;
        }

        // 2. Jika dokumen sudah dikirim ke pembayaran
        if ($status === 'sent_to_pembayaran') {
            return true;
        }

        // 3. Cek computed_status untuk siap_bayar atau sudah_dibayar
        $computedStatus = strtolower($dokumen->computed_status ?? '');
        if (in_array($computedStatus, ['siap_bayar', 'siap_dibayar', 'sudah_dibayar'])) {
            return true;
        }

        // 4. Cek status_pembayaran jika ada
        $statusPembayaran = strtolower($dokumen->status_pembayaran ?? '');
        if (in_array($statusPembayaran, ['siap_bayar', 'siap_dibayar', 'sudah_dibayar'])) {
            return true;
        }

        // 5. Cek apakah dokumen yang tampil di daftar pembayaran (dari roleData)
        // Ini untuk dokumen yang mungkin terlewat di kondisi atas
        $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');
        if ($pembayaranRoleData && $pembayaranRoleData->received_at) {
            return true;
        }

        return false;
    }

    /**
     * Get lock status for CSS classes
     */
    public static function getLockStatusClass(Dokumen $dokumen): string
    {
        if (self::isDocumentLocked($dokumen)) {
            return 'locked-row';
        }

        // Check deadline from dokumen_role_data
        $roleCode = strtolower($dokumen->current_handler ?? '');
        $roleData = $dokumen->getDataForRole($roleCode);
        if ($roleData && $roleData->deadline_at && $roleData->deadline_at->isPast()) {
            return 'overdue-row';
        }

        return 'unlocked-row';
    }

    /**
     * Validate if deadline can be set for document
     */
    public static function canSetDeadline(Dokumen $dokumen): array
    {
        // Get deadline from dokumen_role_data based on current handler
        $roleCode = strtolower($dokumen->current_handler ?? '');
        $roleData = $dokumen->getDataForRole($roleCode);
        $deadlineAt = $roleData?->deadline_at;

        $debug = [
            'document_id' => $dokumen->id,
            'current_handler' => $dokumen->current_handler,
            'status' => $dokumen->status,
            'deadline_exists' => $deadlineAt ? $deadlineAt->format('Y-m-d H:i:s') : 'null'
        ];

        // Check if document already has deadline
        if ($deadlineAt) {
            return [
                'can_set' => false,
                'message' => 'Dokumen sudah memiliki deadline yang aktif.',
                'debug' => $debug
            ];
        }

        // Check document status based on handler
        $validStatuses = match ($dokumen->current_handler) {
            'Team Verifikasi' => ['sent_to_Team Verifikasi', 'sedang diproses'], // Include 'sedang diproses' for newly approved from inbox
            'akutansi' => ['sent_to_akutansi', 'approved_data_sudah_terkirim'],
            'perpajakan' => ['sent_to_perpajakan'], // Dokumen yang baru di-approve dari inbox Perpajakan
            'pembayaran' => ['sent_to_pembayaran'],
            default => []
        };

        if (empty($validStatuses) || !in_array($dokumen->status, $validStatuses)) {
            return [
                'can_set' => false,
                'message' => "Status dokumen tidak valid untuk menetapkan deadline. Status saat ini: {$dokumen->status}.",
                'debug' => $debug
            ];
        }

        return [
            'can_set' => true,
            'message' => 'Deadline dapat ditetapkan',
            'debug' => $debug
        ];
    }
}

