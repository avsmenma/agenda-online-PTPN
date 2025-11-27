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

        // Base condition: must be sent to department without deadline
        $isLocked = is_null($dokumen->deadline_at) &&
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
        if ($dokumen->returned_from_perpajakan_at && $dokumen->returned_from_perpajakan_fixed_at) {
            // Document was returned and fixed, don't lock
            $isLocked = false;
        }
        if ($dokumen->department_returned_at && $dokumen->returned_from_perpajakan_fixed_at) {
            // Document was returned from department and fixed, don't lock
            $isLocked = false;
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

        if ($dokumen->deadline_at && $dokumen->deadline_at->isPast()) {
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

        if ($dokumen->deadline_at && $dokumen->deadline_at->isPast()) {
            return 'overdue-row';
        }

        return 'unlocked-row';
    }

    /**
     * Validate if deadline can be set for document
     */
    public static function canSetDeadline(Dokumen $dokumen): array
    {
        $debug = [
            'document_id' => $dokumen->id,
            'current_handler' => $dokumen->current_handler,
            'status' => $dokumen->status,
            'deadline_exists' => $dokumen->deadline_at ? $dokumen->deadline_at->format('Y-m-d H:i:s') : 'null'
        ];

        // Check if document already has deadline
        if ($dokumen->deadline_at) {
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