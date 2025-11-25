<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mengirim notifikasi WhatsApp
    | Support multiple provider: fonte, fonnte, twilio, custom
    |
    */

    'enabled' => env('WHATSAPP_ENABLED', false),

    'method' => env('WHATSAPP_METHOD', 'fonte'), // fonte, fonnte, twilio, custom

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api_url' => env('WHATSAPP_API_URL'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'sender_number' => env('WHATSAPP_SENDER_NUMBER', '089524429935'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration (if using Twilio)
    |--------------------------------------------------------------------------
    */
    'twilio_account_sid' => env('TWILIO_ACCOUNT_SID'),
    'twilio_from_number' => env('TWILIO_WHATSAPP_FROM_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'default_country_code' => '62', // Indonesia
];
