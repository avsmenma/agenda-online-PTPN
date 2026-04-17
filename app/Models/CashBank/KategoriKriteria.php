<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Kategori / jenis transaksi (tipe: Keluar / Masuk / Penerima) */
final class KategoriKriteria extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'kategori_kriteria';
    protected $primaryKey = 'id_kategori_kriteria';
    protected $guarded    = ['*'];
}
