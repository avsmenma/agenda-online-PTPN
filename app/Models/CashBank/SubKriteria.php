<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Sub-kategori kriteria (Karyawan Pimpinan, Karyawan Pelaksana, dll.) */
final class SubKriteria extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'sub_kriteria';
    protected $primaryKey = 'id_sub_kriteria';
    protected $guarded    = ['*'];
}
