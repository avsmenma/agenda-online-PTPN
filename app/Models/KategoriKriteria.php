<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKriteria extends Model
{
    protected $connection = 'cash_bank';
    protected $table = 'kategori_kriteria';
    protected $primaryKey = 'id_kategori_kriteria';
    
    public $timestamps = false; // Karena created_at dan updated_at NULL
    
    protected $fillable = [
        'nama_kriteria',
        'tipe',
    ];
    
    // Relasi ke sub_kriteria
    public function subKriteria()
    {
        return $this->hasMany(SubKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }
}




