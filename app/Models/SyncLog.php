<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'dokumen_id',
        'direction',
        'status',
        'fields_synced',
        'conflict_fields',
        'source_wins',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'fields_synced'   => 'array',
        'conflict_fields' => 'array',
        'synced_at'       => 'datetime',
    ];
}
