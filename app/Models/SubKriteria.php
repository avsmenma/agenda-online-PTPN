<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKriteria extends Model
{
    protected $connection = 'cash_bank';
    protected $table = 'sub_kriteria';
    protected $primaryKey = 'id_sub_kriteria';
    
    public $timestamps = false; // Karena created_at dan updated_at NULL
    
    protected $fillable = [
        'nama_sub_kriteria',
        'id_kategori_kriteria',
    ];
    
    // Relasi
    public function kategoriKriteria()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }
    
    public function itemSubKriteria()
    {
        return $this->hasMany(ItemSubKriteria::class, 'id_sub_kriteria', 'id_sub_kriteria');
    }
}




