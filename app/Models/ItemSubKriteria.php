<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSubKriteria extends Model
{
    protected $connection = 'cash_bank';
    protected $table = 'item_sub_kriteria';
    protected $primaryKey = 'id_item_sub_kriteria';
    
    public $timestamps = false; // Karena created_at dan updated_at NULL
    
    protected $fillable = [
        'nama_item_sub_kriteria',
        'id_sub_kriteria',
    ];
    
    public function subKriteria()
    {
        return $this->belongsTo(SubKriteria::class, 'id_sub_kriteria', 'id_sub_kriteria');
    }
}





