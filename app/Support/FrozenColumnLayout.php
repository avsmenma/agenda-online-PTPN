<?php

namespace App\Support;

/**
 * Menyusun konfigurasi kolom beku (frozen) untuk tabel dokumen pembayaran.
 *
 * Kolom beku WAJIB menempel tepi tabel: `position: sticky` tidak bisa
 * membekukan kolom di tengah tanpa kolom di kirinya ikut beku — hasilnya
 * saling tumpang tindih saat digulir. Karena itu membekukan sebuah kolom
 * berarti memindahkannya ke tepi saat render.
 */
class FrozenColumnLayout
{
    /**
     * Bersihkan pilihan beku dari key yang tidak sah.
     *
     * Aturan: key harus dikenal ($available) DAN sedang ditampilkan
     * ($selected); duplikat dibuang; key yang muncul di kiri sekaligus di
     * kanan dimenangkan oleh kiri.
     *
     * @param  array<int,mixed>  $left
     * @param  array<int,mixed>  $right
     * @param  array<int,string>  $selected
     * @param  array<string,mixed>  $available  peta key => label
     * @return array{left: array<int,string>, right: array<int,string>}
     */
    public static function normalize(array $left, array $right, array $selected, array $available): array
    {
        $sanitize = static function (array $keys) use ($selected, $available): array {
            $result = [];

            foreach ($keys as $key) {
                $key = is_string($key) ? trim($key) : '';

                if ($key === '' || in_array($key, $result, true)) {
                    continue;
                }

                if (!array_key_exists($key, $available) || !in_array($key, $selected, true)) {
                    continue;
                }

                $result[] = $key;
            }

            return $result;
        };

        $left = $sanitize($left);
        $right = array_values(array_diff($sanitize($right), $left));

        return ['left' => $left, 'right' => $right];
    }

    /**
     * Urutan render tabel: beku kiri -> kolom bebas -> beku kanan.
     * Urutan di dalam tiap kelompok mengikuti urutan pilihan user.
     *
     * @param  array<int,string>  $selected
     * @param  array<int,string>  $left
     * @param  array<int,string>  $right
     * @return array<int,string>
     */
    public static function renderOrder(array $selected, array $left, array $right): array
    {
        // Diberi awalan "frozen" agar tidak bentrok dengan parameter $left/$right.
        $frozenLeft = [];
        $middle = [];
        $frozenRight = [];

        foreach ($selected as $key) {
            if (in_array($key, $left, true)) {
                $frozenLeft[] = $key;
            } elseif (in_array($key, $right, true)) {
                $frozenRight[] = $key;
            } else {
                $middle[] = $key;
            }
        }

        return array_merge($frozenLeft, $middle, $frozenRight);
    }
}
