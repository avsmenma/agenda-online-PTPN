<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Daftar rekening / sumber dana (Mandiri, BNI, Bank Raya, dll.) */
final class SumberDana extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'sumber_dana';
    protected $primaryKey = 'id_sumber_dana';
    protected $guarded    = ['*'];
}
