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
        if (in_array($dokumen->status, [
            'waiting_reviewer_approval',
            'pending_approval_ibub',
            'pending_approval_perpajakan',
        ])) {
            return true; // Lock dokumen yang sedang menunggu approval
        }
        
        // Untuk 'menunggu_di_approve' (status untuk pembayaran),
        // hanya lock jika current_handler bukan perpajakan (artinya dokumen sudah pindah ke pembayaran)
        // Jika current_handler adalah perpajakan, berarti dokumen sudah dikirim dari perpajakan, jadi tidak lock
        if ($dokumen->status === 'menunggu_di_approve') {
            // Jika current_handler bukan perpajakan, berarti dokumen sudah pindah ke role lain, lock
            if ($dokumen->current_handler !== 'perpajakan') {
                return true;
            }
            // Jika current_handler masih perpajakan, berarti dokumen sudah dikirim, tidak lock
            // (akan di-handle di switch case nanti)
        }
        
        // Untuk pending_approval_akutansi dan pending_approval_pembayaran,
        // hanya lock jika current_handler bukan perpajakan (artinya dokumen sudah pindah ke role lain)
        // Jika current_handler adalah perpajakan, berarti dokumen sudah dikirim dari perpajakan, jadi tidak lock
        if (in_array($dokumen->status, ['pending_approval_akutansi', 'pending_approval_pembayaran'])) {
            // Jika current_handler bukan perpajakan, berarti dokumen sudah pindah ke role lain, lock
            if ($dokumen->current_handler !== 'perpajakan') {
                return true;
            }
            // Jika current_handler masih perpajakan, berarti dokumen sudah dikirim, tidak lock
            // (akan di-handle di switch case nanti)
        }

        // Check if document has pending status in dokumen_statuses
        // TAPI untuk perpajakan, jangan lock dokumen yang sudah dikirim ke akutansi/pembayaran
        $hasPendingStatus = $dokumen->roleStatuses()
            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
            ->exists();
        
        if ($hasPendingStatus) {
            // Jika current_handler adalah perpajakan dan status adalah pending_approval_akutansi/pembayaran/menunggu_di_approve,
            // berarti dokumen sudah dikirim dari perpajakan, jadi tidak lock
            if ($dokumen->current_handler === 'perpajakan' && 
                in_array($dokumen->status, [
                    'pending_approval_akutansi', 
                    'pending_approval_pembayaran',
                    'menunggu_di_approve' // Status untuk pembayaran
                ])) {
                // Dokumen sudah dikirim, tidak lock (akan di-handle di switch case nanti)
            } else {
                return true; // Lock dokumen yang memiliki pending status
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
                       'sent_to_ibub',
                       'sedang diproses', // Dokumen yang baru di-approve dari inbox IbuB
                       'sent_to_perpajakan'
                   ]);

        // Additional validation based on current handler
        switch ($dokumen->current_handler) {
            case 'ibuB':
                // Lock dokumen dengan status 'sent_to_ibub' atau 'sedang diproses' (baru di-approve dari inbox)
                // TAPI hanya jika tidak punya deadline
                $isLocked = !$hasDeadline && in_array($dokumen->status, ['sent_to_ibub', 'sedang diproses']);
                break;
            case 'akutansi':
                // Lock dokumen dengan status 'sent_to_akutansi' TAPI hanya jika tidak punya deadline
                // Setelah deadline di-set, status berubah menjadi 'sedang diproses', jadi tidak terkunci lagi
                // TAPI jika status sudah 'sent_to_akutansi', berarti sudah approve, tidak lock (sudah di-handle di atas)
                // JANGAN lock dokumen yang sudah dikirim ke pembayaran (status menunggu_di_approve atau sent_to_pembayaran)
                if (in_array($dokumen->status, [
                    'menunggu_di_approve',
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran'
                ])) {
                    $isLocked = false; // Dokumen sudah dikirim ke pembayaran, tidak terkunci
                } else {
                    $isLocked = !$hasDeadline && $dokumen->status === 'sent_to_akutansi';
                }
                break;
            case 'perpajakan':
                // Lock dokumen dengan status 'sent_to_perpajakan' (baru di-approve dari inbox)
                // TAPI hanya jika tidak punya deadline
                // JANGAN lock dokumen yang sudah dikirim ke akutansi/pembayaran (status pending_approval_* atau menunggu_di_approve)
                if (in_array($dokumen->status, [
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve' // Status untuk pembayaran
                ])) {
                    $isLocked = false; // Dokumen sudah dikirim, tidak terkunci
                } else {
                    $isLocked = !$hasDeadline && $dokumen->status === 'sent_to_perpajakan';
                }
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
            $handlerName = match($dokumen->current_handler) {
                'ibuB' => 'Ibu Yuni',
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
        // If document is locked, cannot edit
        if (self::isDocumentLocked($dokumen)) {
            return false;
        }

        // Dokumen yang sedang menunggu approval tidak bisa diedit
        if (in_array($dokumen->status, [
            'menunggu_di_approve',
            'waiting_reviewer_approval',
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi'
        ])) {
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
            return strtolower($dokumen->current_handler) === strtolower($userRole);
        }

        return true;
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
        $validStatuses = match($dokumen->current_handler) {
            'ibuB' => ['sent_to_ibub', 'sedang diproses'], // Include 'sedang diproses' for newly approved from inbox
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