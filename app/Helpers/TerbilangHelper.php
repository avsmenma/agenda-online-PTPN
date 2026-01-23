<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Convert number to Indonesian words (terbilang)
     * 
     * @param float|int|string $number
     * @return string
     */
    public static function terbilang($number): string
    {
        $number = (float) $number;
        
        if ($number == 0) {
            return 'nol rupiah';
        }

        $angka = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
            'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
            'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
        ];

        $hasil = '';

        // Handle triliun
        if ($number >= 1000000000000) {
            $triliun = floor($number / 1000000000000);
            $hasil .= self::terbilangSatuan($triliun, $angka) . ' triliun ';
            $number = $number % 1000000000000;
        }

        // Handle milyar
        if ($number >= 1000000000) {
            $milyar = floor($number / 1000000000);
            $hasil .= self::terbilangSatuan($milyar, $angka) . ' milyar ';
            $number = $number % 1000000000;
        }

        // Handle juta
        if ($number >= 1000000) {
            $juta = floor($number / 1000000);
            $hasil .= self::terbilangSatuan($juta, $angka) . ' juta ';
            $number = $number % 1000000;
        }

        // Handle ribu
        if ($number >= 1000) {
            $ribu = floor($number / 1000);
            if ($ribu == 1) {
                $hasil .= 'seribu ';
            } else {
                $hasil .= self::terbilangSatuan($ribu, $angka) . ' ribu ';
            }
            $number = $number % 1000;
        }

        // Handle ratusan, puluhan, dan satuan
        if ($number > 0) {
            $hasil .= self::terbilangSatuan($number, $angka);
        }

        return trim($hasil) . ' rupiah';
    }

    /**
     * Convert number (0-999) to Indonesian words
     * 
     * @param int $number
     * @param array $angka
     * @return string
     */
    private static function terbilangSatuan($number, $angka): string
    {
        $hasil = '';
        $number = (int) $number;

        if ($number == 0) {
            return '';
        }

        // Handle ratusan
        if ($number >= 100) {
            $ratus = floor($number / 100);
            if ($ratus == 1) {
                $hasil .= 'seratus ';
            } else {
                $hasil .= $angka[$ratus] . ' ratus ';
            }
            $number = $number % 100;
        }

        // Handle puluhan dan satuan (0-99)
        if ($number > 0) {
            if ($number < 20) {
                $hasil .= $angka[$number] . ' ';
            } else {
                $puluhan = floor($number / 10);
                $satuan = $number % 10;
                
                if ($puluhan == 1) {
                    $hasil .= $angka[10 + $satuan] . ' ';
                } else {
                    $hasil .= $angka[$puluhan] . ' puluh ';
                    if ($satuan > 0) {
                        $hasil .= $angka[$satuan] . ' ';
                    }
                }
            }
        }

        return trim($hasil);
    }
}





