<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'name',
        'filters',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the preset
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
