<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPO extends Model
{
    use HasFactory;

    protected $table = 'dokumen_pos';

    protected $fillable = [
        'dokumen_id',
        'nomor_po',
    ];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }
}