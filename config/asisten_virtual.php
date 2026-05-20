<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Asisten Virtual AI Provider
    |--------------------------------------------------------------------------
    |
    | Query database tetap dilakukan oleh service internal yang read-only.
    | Provider AI hanya dipakai untuk merapikan jawaban dari hasil query yang
    | sudah aman, bukan untuk menjalankan SQL bebas.
    |
    */

    'provider' => env('VIRTUAL_ASSISTANT_PROVIDER', 'local'),

    'openai' => [
        'api_key' => env('VIRTUAL_ASSISTANT_OPENAI_API_KEY', env('OPENAI_API_KEY')),
        'model' => env('VIRTUAL_ASSISTANT_OPENAI_MODEL', 'gpt-4o-mini'),
        'endpoint' => env('VIRTUAL_ASSISTANT_OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout' => (int) env('VIRTUAL_ASSISTANT_AI_TIMEOUT', 12),
    ],

    'gemini' => [
        'api_key' => env('VIRTUAL_ASSISTANT_GEMINI_API_KEY', env('GEMINI_API_KEY')),
        'model' => env('VIRTUAL_ASSISTANT_GEMINI_MODEL', 'gemini-2.5-flash'),
        'endpoint' => env('VIRTUAL_ASSISTANT_GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
        'timeout' => (int) env('VIRTUAL_ASSISTANT_AI_TIMEOUT', 12),
    ],

    'limits' => [
        'max_message_length' => (int) env('VIRTUAL_ASSISTANT_MAX_MESSAGE_LENGTH', 800),
        'default_result_limit' => (int) env('VIRTUAL_ASSISTANT_DEFAULT_LIMIT', 15),
    ],
];
