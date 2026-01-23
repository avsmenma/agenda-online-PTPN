<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TuTkDokumen extends Model
{
    protected $table = 'tu_tk_2023';
    
    protected $primaryKey = 'KONTROL';
    
    public $incrementing = true;
    
    public $timestamps = false;
    
    // Mass assignment protection - semua kolom bisa diisi
    protected $guarded = [];
    
    /**
     * Get status pembayaran
     */
    public function getStatusPembayaranAttribute()
    {
        $dibayar = (float)($this->JUMLAH_DIBAYAR ?? 0);
        $nilai = (float)($this->NILAI ?? 0);
        
        if ($nilai == 0) {
            return 'tidak_valid';
        }
        
        if ($dibayar == 0) {
            return 'belum_lunas';
        } elseif ($dibayar >= $nilai) {
            return 'lunas';
        } else {
            return 'parsial';
        }
    }
    
    /**
     * Get persentase pembayaran
     */
    public function getPersentasePembayaranAttribute()
    {
        $dibayar = (float)($this->JUMLAH_DIBAYAR ?? 0);
        $nilai = (float)($this->NILAI ?? 0);
        
        if ($nilai == 0) {
            return 0;
        }
        
        return round(($dibayar / $nilai) * 100, 2);
    }
    
    /**
     * Get umur hutang dalam hari
     */
    public function getUmurHutangHariAttribute()
    {
        $umur = $this->attributes['UMUR_HUTANG_HARI'] ?? null;
        
        if ($umur !== null) {
            return (int)$umur;
        }
        
        // Calculate from tanggal masuk dokumen
        if (!empty($this->TANGGAL_MASUK_DOKUMEN)) {
            try {
                $tanggalMasuk = Carbon::parse($this->TANGGAL_MASUK_DOKUMEN);
                return Carbon::now()->diffInDays($tanggalMasuk);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get aging category
     */
    public function getAgingCategoryAttribute()
    {
        $umur = $this->umur_hutang_hari;
        
        if ($umur === null) {
            return 'unknown';
        }
        
        if ($umur < 30) {
            return 'aman'; // Hijau
        } elseif ($umur <= 60) {
            return 'waspada'; // Kuning
        } else {
            return 'prioritas'; // Merah
        }
    }
}






