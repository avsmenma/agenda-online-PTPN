<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TuTkVd extends Model
{
    use HasFactory;

    protected $table = 'tu_tk_vd_2023';
    protected $primaryKey = 'KONTROL';
    public $timestamps = false;

    protected $fillable = [
        'KONTROL', 'AGENDA', 'TGL_SPP', 'NO_SPP', 'KATEGORI', 'VENDOR', 'NO_KONTRAK',
        'TGL__KONTRAK', 'TGL__KONTRAK_BERAKHIR', 'NO_BERITA_ACARA', 'TGL__BERITA_ACARA',
        'TGL__FAKTUR_PAJAK', 'HAL', 'NILAI', 'POSISI_DOKUMEN', 'TANGGAL_MASUK_DOKUMEN',
        'PROSES_VERIFIKASI', 'KETERANGAN_VERIFIKASI', 'NILAI_SETELAH_VERIFIKASI', 'DIBAYAR',
        'BELUM_DIBAYAR', 'NO_PO', 'NO_MIRO_SES', 'SIAP_BAYAR', 'BELUM_SIAP_BAYAR', 'KETERANGAN',
        'DIBUKUKAN_TAHUN_2023', 'DIBUKUKAN_TAHUN_2024', 'TGL_BUKU_TAHUN_2024', 'JUMLAH',
        'TANGGAL_BAYAR_I', 'JUMLAH_1', 'TANGGAL_BAYAR_II', 'JUMLAH_2', 'TANGGAL_BAYAR_III',
        'JUMLAH_3', 'TANGGAL_BAYAR_IV', 'JUMLAH_4', 'TANGGAL_BAYAR_V', 'JUMLAH_5',
        'TANGGAL_BAYAR_VI', 'JUMLAH_6', 'TANGGAL_BAYAR_RAMPUNG', 'JUMLAH_DIBAYAR',
        'BELUM_DIBAYAR_1', 'FILE_SPP', 'SUB_PEKERJAAN', 'TGL_INPUT', 'UMUR_HUTANG_HARI',
        'UMUR_SPP', 'UMUR_SPK_HARI', 'UMUR_SPK', 'UMUR_BA_HARI', 'UMUR_BA', 'SALDO_HUTANG',
        '2023', 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES',
        'SD_2024', 'TOTAL', 'SELISIH', 'JAN_1', 'FEB_1', 'MAR_1', 'APR_1', 'MEI_1', 'JUN_1',
        'JUL_1', 'AGU_1', 'SEP_1', 'OKT_1', 'NOV_1', 'DES_1', '2025', 'TOTAL_2', 'SELISIH_2'
    ];

    protected $casts = [
        'NILAI' => 'decimal:2',
        'DIBAYAR' => 'decimal:2',
        'BELUM_DIBAYAR' => 'decimal:2',
        'NILAI_SETELAH_VERIFIKASI' => 'decimal:2',
        'JUMLAH_DIBAYAR' => 'decimal:2',
        'BELUM_DIBAYAR_1' => 'decimal:2',
        'SALDO_HUTANG' => 'decimal:2',
        'UMUR_HUTANG_HARI' => 'integer',
    ];

    public function getStatusPembayaranAttribute()
    {
        $dibayar = (float) ($this->JUMLAH_DIBAYAR ?? 0);
        $belumDibayar = (float) ($this->BELUM_DIBAYAR_1 ?? 0);
        $nilai = (float) ($this->NILAI ?? 0);

        if ($belumDibayar <= 0 || ($dibayar > 0 && $belumDibayar <= 0)) {
            return 'lunas';
        } elseif ($dibayar > 0 && $belumDibayar > 0) {
            return 'parsial';
        } else {
            return 'belum_lunas';
        }
    }

    public function getPersentasePembayaranAttribute()
    {
        $nilai = (float) ($this->NILAI ?? 0);
        $dibayar = (float) ($this->JUMLAH_DIBAYAR ?? 0);

        if ($nilai <= 0) {
            return 0;
        }

        return ($dibayar / $nilai) * 100;
    }

    public function getWarnaUmurHutangAttribute()
    {
        $umurHari = (int) ($this->UMUR_HUTANG_HARI ?? 0);

        if ($umurHari < 30) {
            return 'hijau';
        } elseif ($umurHari >= 30 && $umurHari <= 60) {
            return 'kuning';
        } elseif ($umurHari > 60 && $umurHari <= 365) {
            return 'merah';
        } else {
            return 'merah-gelap';
        }
    }

    public function scopeStatusPembayaran($query, $status)
    {
        switch ($status) {
            case 'lunas':
                return $query->where(function($q) {
                    $q->whereRaw('COALESCE(BELUM_DIBAYAR_1, 0) <= 0')
                      ->orWhereRaw('COALESCE(JUMLAH_DIBAYAR, 0) > 0 AND COALESCE(BELUM_DIBAYAR_1, 0) <= 0');
                });
            case 'parsial':
                return $query->whereRaw('COALESCE(JUMLAH_DIBAYAR, 0) > 0')
                             ->whereRaw('COALESCE(BELUM_DIBAYAR_1, 0) > 0');
            case 'belum_lunas':
                return $query->whereRaw('COALESCE(BELUM_DIBAYAR_1, 0) > 0')
                             ->whereRaw('COALESCE(JUMLAH_DIBAYAR, 0) = 0');
            default:
                return $query;
        }
    }

    public function scopeUmurHutang($query, $range)
    {
        switch ($range) {
            case 'kurang_30':
                return $query->where('UMUR_HUTANG_HARI', '<', 30);
            case '30_60':
                return $query->whereBetween('UMUR_HUTANG_HARI', [30, 60]);
            case 'lebih_60':
                return $query->where('UMUR_HUTANG_HARI', '>', 60);
            case 'lebih_1_tahun':
                return $query->where('UMUR_HUTANG_HARI', '>', 365);
            default:
                return $query;
        }
    }

    public function scopeKategori($query, $kategori)
    {
        if ($kategori) {
            return $query->where('KATEGORI', $kategori);
        }
        return $query;
    }

    public function scopeVendor($query, $vendor)
    {
        if ($vendor) {
            return $query->where('VENDOR', 'like', '%' . $vendor . '%');
        }
        return $query;
    }

    public function scopePosisiDokumen($query, $posisi)
    {
        if ($posisi) {
            return $query->where('POSISI_DOKUMEN', $posisi);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('AGENDA', 'like', '%' . $search . '%')
                  ->orWhere('NO_SPP', 'like', '%' . $search . '%')
                  ->orWhere('VENDOR', 'like', '%' . $search . '%')
                  ->orWhere('NO_KONTRAK', 'like', '%' . $search . '%')
                  ->orWhere('HAL', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    public function positionTrackings()
    {
        return $this->hasMany(\App\Models\DocumentPositionTracking::class, 'tu_tk_kontrol', 'KONTROL')
                    ->where('data_source', 'input_vd')
                    ->orderBy('changed_at', 'desc');
    }

    public function paymentLogs()
    {
        return $this->hasMany(\App\Models\PaymentLog::class, 'tu_tk_kontrol', 'KONTROL')
                    ->where('data_source', 'input_vd')
                    ->orderBy('payment_sequence')
                    ->orderBy('tanggal_bayar');
    }
}






