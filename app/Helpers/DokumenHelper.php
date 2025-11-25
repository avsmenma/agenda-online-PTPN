<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Dokumen;

class DokumenHelper
{
    /**
     * Check if document is locked and cannot be edited
     */
    public static function isDocumentLocked(Dokumen $dokumen): bool
    {
        // Base condition: must be sent to department without deadline
        $isLocked = is_null($dokumen->deadline_at) &&
                   in_array($dokumen->status, [
                       'sent_to_ibub',
                       'sent_to_akutansi',
                       'sent_to_perpajakan',
                       'sent_to_pembayaran'
                   ]);

        // Additional validation based on current handler
        switch ($dokumen->current_handler) {
            case 'ibuB':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_ibub';
                break;
            case 'akutansi':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_akutansi';
                break;
            case 'perpajakan':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_perpajakan';
                break;
            case 'pembayaran':
                $isLocked = $isLocked && $dokumen->status === 'sent_to_pembayaran';
                break;
        }

        // Don't lock documents that were returned and repaired
        if ($dokumen->returned_from_perpajakan_at || $dokumen->department_returned_at) {
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
            'ibuB' => ['sent_to_ibub'],
            'akutansi' => ['sent_to_akutansi', 'approved_data_sudah_terkirim'],
            'perpajakan' => ['sent_to_perpajakan'],
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