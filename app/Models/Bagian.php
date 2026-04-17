<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bagian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active bagians
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get bagians ordered by kode
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('kode');
    }
}






