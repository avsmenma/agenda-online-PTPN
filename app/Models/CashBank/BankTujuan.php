<?php

declare(strict_types=1);

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

/** READ-ONLY: Virtual Account tujuan pembayaran (kebun / unit) */
final class BankTujuan extends Model
{
    protected $connection = 'cash_bank_new';
    protected $table      = 'bank_tujuan';
    protected $primaryKey = 'id_bank_tujuan';
    protected $guarded    = ['*'];
}
