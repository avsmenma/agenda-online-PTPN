<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentActivity extends Model
{
    protected $fillable = [
        'dokumen_id',
        'user_id',
        'activity_type',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    const TYPE_VIEWING = 'viewing';
    const TYPE_EDITING = 'editing';

    /**
     * Get the document that owns the activity.
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active activities (within last 5 minutes).
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope to get viewing activities.
     */
    public function scopeViewing($query)
    {
        return $query->where('activity_type', self::TYPE_VIEWING);
    }

    /**
     * Scope to get editing activities.
     */
    public function scopeEditing($query)
    {
        return $query->where('activity_type', self::TYPE_EDITING);
    }
}






