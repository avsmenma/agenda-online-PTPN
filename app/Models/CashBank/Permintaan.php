<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Permintaan anggaran / rencana pengeluaran mingguan (M1–M4) */
final class Permintaan extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'permintaans';
    protected $primaryKey = 'id_permintaan';
    protected $guarded    = ['*'];

    protected $casts = [
        'M1' => 'decimal:2',
        'M2' => 'decimal:2',
        'M3' => 'decimal:2',
        'M4' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }

    public function subKriteria()
    {
        return $this->belongsTo(SubKriteria::class, 'id_sub_kriteria', 'id_sub_kriteria');
    }
}
