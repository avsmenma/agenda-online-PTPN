<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPR extends Model
{
    use HasFactory;

    protected $table = 'dokumen_prs';

    protected $fillable = [
        'dokumen_id',
        'nomor_pr',
    ];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }
}