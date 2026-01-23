<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DibayarKepada extends Model
{
    protected $fillable = [
        'dokumen_id',
        'nama_penerima',
    ];

    /**
     * Get the dokumen that owns the dibayarKepada.
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }
}



