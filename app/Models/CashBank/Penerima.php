<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Penerimaan uang masuk (penjualan CPO, Kernel, TBS, Karet, dll.) */
final class Penerima extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'penerimas';
    protected $primaryKey = 'id_penerima';
    protected $guarded    = ['*'];

    protected $casts = [
        'tanggal'       => 'date',
        'volume'        => 'decimal:2',
        'harga'         => 'decimal:2',
        'nilai'         => 'decimal:2',
        'nilai_inc_ppn' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }
}
