<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPositionTracking extends Model
{
    protected $fillable = [
        'tu_tk_kontrol',
        'data_source',
        'posisi_lama',
        'posisi_baru',
        'changed_by',
        'keterangan',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the TuTk document that owns this position tracking
     */
    public function tuTk(): BelongsTo
    {
        return $this->belongsTo(TuTk::class, 'tu_tk_kontrol', 'KONTROL');
    }

    /**
     * Log position change
     */
    public static function logPositionChange($kontrol, $posisiBaru, $posisiLama = null, $keterangan = null, $changedBy = null, $dataSource = 'input_ks')
    {
        return self::create([
            'tu_tk_kontrol' => $kontrol,
            'data_source' => $dataSource,
            'posisi_lama' => $posisiLama,
            'posisi_baru' => $posisiBaru,
            'changed_by' => $changedBy ?? auth()->user()->name ?? 'System',
            'keterangan' => $keterangan,
            'changed_at' => now(),
        ]);
    }
}




