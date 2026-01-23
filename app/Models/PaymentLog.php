<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'tu_tk_kontrol',
        'tu_tk_agenda', // CRITICAL: Use AGENDA (unique) instead of KONTROL (not unique)
        'data_source',
        'payment_sequence',
        'tanggal_bayar',
        'jumlah',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the TuTk document that owns this payment log
     */
    public function tuTk(): BelongsTo
    {
        return $this->belongsTo(TuTk::class, 'tu_tk_kontrol', 'KONTROL');
    }
}





