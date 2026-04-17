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
    | Role to Next Handler Mapping — Document forwarding rules
    |--------------------------------------------------------------------------
    */
    'forwarding' => [
        'operator'          => 'team_verifikasi',
        'team_verifikasi'   => ['perpajakan', 'akutansi', 'pembayaran'],  // Multiple possible targets
        'perpajakan'        => 'akutansi',
        'akutansi'          => 'pembayaran',
        'pembayaran'        => null,  // Terminal state
    ],

    /*
    |--------------------------------------------------------------------------
    | R9: Known Ambiguous Statuses — Dokumentasi Perbedaan Status
    |--------------------------------------------------------------------------
    |
    | Terdapat dua status "return" yang terlihat mirip namun memiliki makna
    | berbeda dalam alur dokumen. Perbedaan ini HARUS diketahui oleh semua
    | developer yang bekerja pada modul ini.
    |
    | == returned_to_department ==
    | SIAPA yang menggunakan:
    |   - Perpajakan, Akutansi, Pembayaran -> mengembalikan dokumen ke Team Verifikasi
    | SIAPA yang menerima:
    |   - Team Verifikasi (current_handler segera pindah ke 'team_verifikasi')
    | CARA membedakan asal pengembalian:
    |   - Lihat field `return_source`:
    |     * 'perpajakan' = dikembalikan dari Perpajakan
    |     * 'akutansi'   = dikembalikan dari Akutansi
    |     * 'pembayaran' = dikembalikan dari Pembayaran
    | SKENARIO: Handler menemukan masalah, mengembalikan untuk ditinjau ulang.
    |
    | == returned_to_verifikasi ==
    | SIAPA yang menggunakan:
    |   - Perpajakan, Akutansi -> mengembalikan untuk klarifikasi
    | SIAPA yang menerima:
    |   - Team Verifikasi (tapi current_handler BELUM berpindah)
    | PERBEDAAN vs returned_to_department:
    |   1. returned_to_department  -> current_handler segera pindah ke 'team_verifikasi'
    |   2. returned_to_verifikasi  -> current_handler BELUM pindah, dokumen masih
    |      "dipegang" Perpajakan/Akutansi sampai Team Verifikasi mengambil alih
    |      via sendBackToMainList().
    | SKENARIO: Handler butuh klarifikasi, tapi belum sepenuhnya melepas dokumen.
    |
    | == returned_to_bidang ==
    | SIAPA yang menggunakan:
    |   - Team Verifikasi -> mengembalikan ke Bagian (sub-unit internal)
    | SIAPA yang menerima:
    |   - User dengan role 'bagian_XXX' sesuai return_source
    |
    | == returned_to_operator ==
    | SIAPA yang menggunakan:
    |   - Team Verifikasi -> mengembalikan ke Operator (pembuat dokumen)
    | SIAPA yang menerima:
    |   - User dengan role 'operator'
    |
    | == REKOMENDASI REFACTORING (future work) ==
    | Jika ingin dikonsolidasi, pertimbangkan menggabungkan returned_to_department
    | dan returned_to_verifikasi menjadi satu status 'returned_to_verifikasi'
    | dengan field 'return_mode':
    |   - return_mode = 'immediate' -> current_handler langsung berpindah
    |   - return_mode = 'pending'   -> menunggu Team Verifikasi ambil alih
    |
    | PERINGATAN: Jangan konsolidasi tanpa migration + testing menyeluruh!
    |
    */
    'known_ambiguities' => [
        'returned_to_department' => [
            'description'   => 'Dikembalikan oleh Perpajakan/Akutansi/Pembayaran ke Team Verifikasi. current_handler segera berpindah ke team_verifikasi.',
            'set_by'        => ['perpajakan', 'akutansi', 'pembayaran'],
            'received_by'   => 'team_verifikasi',
            'discriminator' => 'return_source: perpajakan|akutansi|pembayaran',
        ],
        'returned_to_verifikasi' => [
            'description'   => 'Dikembalikan oleh Perpajakan/Akutansi ke Team Verifikasi untuk klarifikasi. current_handler BELUM berpindah - dokumen masih terlihat di Perpajakan/Akutansi sampai Team Verifikasi ambil alih via sendBackToMainList().',
            'set_by'        => ['perpajakan', 'akutansi'],
            'received_by'   => 'team_verifikasi',
            'discriminator' => 'return_source: perpajakan|akutansi',
        ],
        'returned_to_bidang' => [
            'description'   => 'Dikembalikan oleh Team Verifikasi ke Bagian (sub-unit). Bagian harus revisi dan kirim ulang.',
            'set_by'        => ['team_verifikasi'],
            'received_by'   => 'bagian_{code}',
            'discriminator' => 'return_source: AKN|DPM|KPL|PMO|SDM|SKH|TAN|TEP',
        ],
        'returned_to_operator' => [
            'description'   => 'Dikembalikan oleh Team Verifikasi ke Operator. Operator harus revisi dan kirim ulang.',
            'set_by'        => ['team_verifikasi'],
            'received_by'   => 'operator',
            'discriminator' => 'return_source: team_verifikasi',
        ],
    ],
];
