<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Fonnte WhatsApp Gateway API integration.
    | Get your API token from: https://fonnte.com
    |
    */

    // API endpoint for sending messages
    'api_url' => env('FONNTE_API_URL', 'https://api.fonnte.com/send'),

    // Your Fonnte API token (required)
    'api_token' => env('FONNTE_API_TOKEN'),

    // Country code for phone number formatting (default: Indonesia)
    'country_code' => env('FONNTE_COUNTRY_CODE', '62'),

    // Delay in seconds between messages when sending bulk
    'delay' => env('FONNTE_DELAY', 5),

    // Enable/disable WhatsApp notifications globally
    'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    // Cooldown period in hours before sending same notification type for same document
    'cooldown_hours' => env('WHATSAPP_NOTIFICATION_COOLDOWN', 24),

    // Role-specific thresholds (in hours)
    'thresholds' => [
        'team_verifikasi' => [
            'warning' => 24,    // Yellow warning at 24 hours
            'danger' => 72,     // Red danger at 72 hours (3 days)
        ],
        'perpajakan' => [
            'warning' => 24,
            'danger' => 72,
        ],
        'akutansi' => [
            'warning' => 24,
            'danger' => 72,
        ],
        'pembayaran' => [
            'warning' => 168,   // 1 week = 168 hours
            'danger' => 504,    // 3 weeks = 504 hours
        ],
    ],

    // Roles to send notifications to
    'notify_roles' => ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'],
];
