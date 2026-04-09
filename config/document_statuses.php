<?php

/**
 * Centralized Document Status Configuration
 *
 * Single source of truth for all document workflow statuses.
 * This replaces hardcoded status strings scattered across controllers.
 *
 * Usage:
 *   $statuses = config('document_statuses.workflow');
 *   $label = config('document_statuses.labels.sent_to_team_verifikasi');
 *   $color = config('document_statuses.colors.draft');
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Workflow Statuses — Document lifecycle states
    |--------------------------------------------------------------------------
    */
    'workflow' => [
        'draft'                     => 'draft',
        'sent_to_team_verifikasi'   => 'sent_to_team_verifikasi',
        'sedang_diproses'           => 'sedang diproses',     // Legacy: uses space
        'menunggu_di_approve'       => 'menunggu_di_approve',
        'sent_to_perpajakan'        => 'sent_to_perpajakan',
        'sent_to_akutansi'          => 'sent_to_akutansi',    // Known typo: "Akutansi" → see KNOWN_ISSUES.md
        'sent_to_pembayaran'        => 'sent_to_pembayaran',
        'returned_to_operator'      => 'returned_to_operator',
        'returned_to_department'    => 'returned_to_department',
        'returned_to_verifikasi'    => 'returned_to_verifikasi',
        'returned_to_bidang'        => 'returned_to_bidang',
        'completed'                 => 'completed',
        'selesai'                   => 'selesai',             // Legacy alias for completed
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Codes — Valid handler/role identifiers
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'operator',
        'team_verifikasi',
        'verifikasi',       // Legacy alias for team_verifikasi
        'perpajakan',
        'akutansi',         // Known typo — see KNOWN_ISSUES.md
        'pembayaran',
        'owner',
        'admin',
        'programmer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Labels — Indonesian display names
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'draft'                     => 'Draft',
        'sent_to_team_verifikasi'   => 'Terkirim ke Team Verifikasi',
        'sedang diproses'           => 'Sedang Diproses',
        'menunggu_di_approve'       => 'Menunggu Approval',
        'sent_to_perpajakan'        => 'Terkirim ke Perpajakan',
        'sent_to_akutansi'          => 'Terkirim ke Akuntansi',
        'sent_to_pembayaran'        => 'Terkirim ke Pembayaran',
        'returned_to_operator'      => 'Dikembalikan ke Operator',
        'returned_to_department'    => 'Dikembalikan ke Bagian',
        'returned_to_verifikasi'    => 'Dikembalikan ke Verifikasi',
        'returned_to_bidang'        => 'Dikembalikan ke Bidang',
        'completed'                 => 'Selesai',
        'selesai'                   => 'Selesai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Colors — Badge/UI color mapping (Bootstrap classes)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'draft'                     => 'secondary',
        'sent_to_team_verifikasi'   => 'primary',
        'sedang diproses'           => 'info',
        'menunggu_di_approve'       => 'warning',
        'sent_to_perpajakan'        => 'primary',
        'sent_to_akutansi'          => 'primary',
        'sent_to_pembayaran'        => 'primary',
        'returned_to_operator'      => 'danger',
        'returned_to_department'    => 'danger',
        'returned_to_verifikasi'    => 'danger',
        'returned_to_bidang'        => 'danger',
        'completed'                 => 'success',
        'selesai'                   => 'success',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Groups — Logical grouping for filters
    |--------------------------------------------------------------------------
    */
    'groups' => [
        'active' => [
            'sent_to_team_verifikasi',
            'sedang diproses',
            'menunggu_di_approve',
        ],
        'forwarded' => [
            'sent_to_perpajakan',
            'sent_to_akutansi',
            'sent_to_pembayaran',
        ],
        'returned' => [
            'returned_to_operator',
            'returned_to_department',
            'returned_to_verifikasi',
            'returned_to_bidang',
        ],
        'finished' => [
            'completed',
            'selesai',
        ],
        'pending_approval' => [
            'pending_approval_perpajakan',
            'pending_approval_akutansi',
            'pending_approval_pembayaran',
            'waiting_approval_perpajakan',
            'waiting_approval_akuntansi',
            'waiting_approval_pembayaran',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role → Next Handler Mapping — Document forwarding rules
    |--------------------------------------------------------------------------
    */
    'forwarding' => [
        'operator'          => 'team_verifikasi',
        'team_verifikasi'   => ['perpajakan', 'akutansi', 'pembayaran'],  // Multiple possible targets
        'perpajakan'        => 'akutansi',
        'akutansi'          => 'pembayaran',
        'pembayaran'        => null,  // Terminal state
    ],
];
