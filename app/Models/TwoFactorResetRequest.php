<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TwoFactorResetRequest extends Model
{
    protected $table = 'two_factor_reset_requests';

    protected $fillable = [
        'requester_id',
        'programmer_id',
        'reason',
        'status',
        'handled_at',
        'notes',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function programmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'programmer_id');
    }
}

