<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'dokumen_id',
        'stage',
        'action',
        'action_description',
        'performed_by',
        'details',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'details' => 'array',
    ];

    /**
     * Get the dokumen that owns the activity log
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }
}





