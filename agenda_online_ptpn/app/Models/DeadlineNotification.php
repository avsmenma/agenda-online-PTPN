<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeadlineNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'dokumen_id',
        'handler',
        'deadline_type',
        'deadline_at',
        'status',
        'days_overdue',
        'last_notified_at',
        'is_read',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'is_read' => 'boolean',
        'days_overdue' => 'integer',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Get notification color based on status
     */
    public function getColorAttribute()
    {
        return $this->status === 'danger' ? 'red' : 'yellow';
    }
}
