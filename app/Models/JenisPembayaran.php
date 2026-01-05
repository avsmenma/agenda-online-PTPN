<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPembayaran extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table = 'jenis_pembayarans';
    
    public $timestamps = false;
    
    protected $fillable = [
        'nama',
        'kode',
        'jenis_pembayaran',
        'nama_jenis_pembayaran',
    ];

    /**
     * Get the display name for the jenis pembayaran
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama ?? $this->nama_jenis_pembayaran ?? $this->jenis_pembayaran ?? $this->kode ?? 'Jenis Pembayaran #' . $this->id;
    }

    /**
     * Get the value for the form
     */
    public function getFormValueAttribute()
    {
        return $this->nama ?? $this->nama_jenis_pembayaran ?? $this->jenis_pembayaran ?? $this->kode ?? $this->id;
    }
}

