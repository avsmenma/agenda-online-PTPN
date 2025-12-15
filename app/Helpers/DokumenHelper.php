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
        if (in_array($dokumen->status, [
            'menunggu_di_approve',
            'waiting_reviewer_approval',
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi'
        ])) {
            return true; // Lock dokumen yang sedang menunggu approval
        }

        // Check if document has pending status in dokumen_statuses
        $hasPendingStatus = $dokumen->roleStatuses()
            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
            ->exists();
        
        if ($hasPendingStatus) {
            return true; // Lock dokumen yang memiliki pending status
        }

        // Get deadline from dokumen_role_data based on current handler
        $roleCode = strtolower($dokumen->current_handler ?? '');
        $roleData = $dokumen->getDataForRole($roleCode);
        $hasDeadline = $roleData && $roleData->deadline_at;

        // Base condition: must be sent to department without deadline
        $isLocked = !$hasDeadline &&
                   in_array($dokumen->status, [
                       'sent_to_ibub',
                       'sedang diproses', // Dokumen yang baru di-approve dari inbox IbuB
                       'sent_to_akutansi',
                       'sent_to_perpajakan',
                       'sent_to_pembayaran'
                   ]);

        // Additional validation based on current handler
        switch ($dokumen->current_handler) {
            case 'ibuB':
                // Lock dokumen dengan status 'sent_to_ibub' atau 'sedang diproses' (baru di-approve dari inbox)
                $isLocked = $isLocked && in_array($dokumen->status, ['sent_to_ibub', 'sedang diproses']);
                break;
            case 'akutansi':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_akutansi';
                break;
            case 'perpajakan':
                // Lock dokumen dengan status 'sent_to_perpajakan' (baru di-approve dari inbox)
                $isLocked = $isLocked && $dokumen->status === 'sent_to_perpajakan';
                break;
            case 'pembayaran':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_pembayaran';
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