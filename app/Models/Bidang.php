<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $fillable = [
        'kode_bidang',
        'nama_bidang',
        'deskripsi',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function dokumens()
    {
        return $this->hasMany(Dokumen::class, 'return_source', 'kode_bidang');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getDisplayNamaAttribute()
    {
        return "{$this->kode_bidang} - {$this->nama_bidang}";
    }
}






