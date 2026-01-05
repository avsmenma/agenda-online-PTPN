<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPembayaran extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table = 'jenis_pembayarans';
    protected $primaryKey = 'id_jenis_pembayaran';
    
    public $timestamps = true; // Tabel memiliki created_at dan updated_at
    
    protected $fillable = [
        'nama_jenis_pembayaran',
    ];

    /**
     * Get the display name for the jenis pembayaran
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama_jenis_pembayaran ?? 'Jenis Pembayaran #' . ($this->id_jenis_pembayaran ?? 'N/A');
    }

    /**
     * Get the value for the form
     */
    public function getFormValueAttribute()
    {
        return $this->nama_jenis_pembayaran ?? $this->id_jenis_pembayaran;
    }
    
    /**
     * Convert stdClass to model instance jika diperlukan
     */
    public static function fromStdClass($stdClass)
    {
        $model = new static();
        // Set attributes menggunakan setRawAttributes untuk menghindari mass assignment protection
        $attributes = [];
        foreach ((array)$stdClass as $key => $value) {
            $attributes[$key] = $value;
        }
        $model->setRawAttributes($attributes, true);
        return $model;
    }
}

