<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TuTkPupuk extends Model
{
    use HasFactory;

    // Table name sesuai dengan database yang sudah ada
    protected $table = 'tu_tk_pupuk_2023';

    // Primary key - menggunakan EXTRA_COL_0 atau kombinasi kolom
    // Karena tidak ada primary key yang jelas, kita akan menggunakan kombinasi
    protected $primaryKey = 'EXTRA_COL_0';
    public $incrementing = false;

    // Disable timestamps karena tabel tidak memiliki created_at/updated_at
    public $timestamps = false;

    // Field yang bisa diisi secara mass assignment
    protected $fillable = [
        'EXTRA_COL_0',
        'AGENDA',
        'TGL_SPP',
        'NO_SPP',
        'KEBUN',
        'VENDOR',
        'NO_KONTRAK',
        'TGL__KONTRAK',
        'TGL__KONTRAK_BERAKHIR',
        'NO__BERITA_ACARA',
        'TGL__BERITA_ACARA',
        'TGL__FAKTUR_PAJAK',
        'HAL',
        'NILAI',
        'POSISI_DOKUMEN',
        'TANGGAL_MASUK_DOKUMEN',
        'PROSES_VERIFIKASI',
        'KETERANGAN_VERIFIKASI',
        'NILAI_SETELAH_VERIFIKASI',
        'DIBAYAR',
        'BELUM_DIBAYAR',
        'NO_PO',
        'NO_MIRO_SES',
        'SIAP_BAYAR',
        'BELUM_SIAP_BAYAR',
        'KETERANGAN',
        'DIBUKUKAN_TAHUN_2023',
        'DIBUKUKAN_TAHUN_2024',
        'TGL_BUKU_TAHUN_2024',
        'JUMLAH',
        'TANGGAL_BAYAR_I',
        'JUMLAH_1',
        'TANGGAL_BAYAR_II',
        'JUMLAH_2',
        'TANGGAL_BAYAR_III',
        'JUMLAH_3',
        'TANGGAL_BAYAR_IV',
        'JUMLAH_4',
        'TANGGAL_BAYAR_V',
        'JUMLAH_5',
        'TANGGAL_BAYAR_VI',
        'JUMLAH_6',
        'TANGGAL_BAYAR_RAMPUNG',
        'JUMLAH_DIBAYAR',
        'BELUM_DIBAYAR_1',
        'FILE_SPP',
        'EXTRA_COL_46',
        'EXTRA_COL_47',
        'UMUR_HUTANG_HARI',
        'UMUR_SPP',
        'UMUR_SPK_HARI',
        'UMUR_SPK',
        'UMUR_BA_HARI',
        'UMUR_BA',
        'SALDO_HUTANG',
    ];

    // Cast types untuk field tertentu
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

    /**
     * Get status pembayaran
     * Lunas: BELUM_DIBAYAR_1 = 0 atau null
     * Parsial: JUMLAH_DIBAYAR > 0 tapi BELUM_DIBAYAR_1 > 0
     * Belum Lunas: BELUM_DIBAYAR_1 > 0 dan JUMLAH_DIBAYAR = 0
     */
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

    /**
     * Get persentase pembayaran
     */
    public function getPersentasePembayaranAttribute()
    {
        $nilai = (float) ($this->NILAI ?? 0);
        $dibayar = (float) ($this->JUMLAH_DIBAYAR ?? 0);

        if ($nilai <= 0) {
            return 0;
        }

        return ($dibayar / $nilai) * 100;
    }

    /**
     * Get warna umur hutang
     */
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

    /**
     * Scope untuk filter status pembayaran
     */
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

    /**
     * Scope untuk filter umur hutang
     */
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

    /**
     * Scope untuk filter vendor
     */
    public function scopeVendor($query, $vendor)
    {
        if ($vendor) {
            return $query->where('VENDOR', 'like', '%' . $vendor . '%');
        }
        return $query;
    }

    /**
     * Scope untuk filter posisi dokumen
     */
    public function scopePosisiDokumen($query, $posisi)
    {
        if ($posisi) {
            return $query->where('POSISI_DOKUMEN', $posisi);
        }
        return $query;
    }

    /**
     * Scope untuk search
     */
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

    /**
     * Get position tracking history
     */
    public function positionTrackings()
    {
        return $this->hasMany(\App\Models\DocumentPositionTracking::class, 'tu_tk_kontrol', 'EXTRA_COL_0')
                    ->where('data_source', 'input_pupuk')
                    ->orderBy('changed_at', 'desc');
    }

    /**
     * Get payment logs
     */
    public function paymentLogs()
    {
        return $this->hasMany(\App\Models\PaymentLog::class, 'tu_tk_kontrol', 'EXTRA_COL_0')
                    ->where('data_source', 'input_pupuk')
                    ->orderBy('payment_sequence')
                    ->orderBy('tanggal_bayar');
    }
}



