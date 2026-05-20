<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualAssistantTestCase extends Model
{
    protected $fillable = [
        'name',
        'question',
        'expected_intent',
        'expected_params',
        'expected_result_type',
        'is_active',
        'last_status',
        'last_notes',
        'last_run_at',
    ];

    protected $casts = [
        'expected_params' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
