<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Aturan badge Status Pembayaran untuk role Bagian (3-state).
 *
 * Diekstrak dari blok @php inline di dalam <td> agar tabel desktop dan kartu
 * mobile membaca aturan yang SAMA. Menyalinnya ke partial kartu akan melahirkan
 * salinan kedua aturan bisnis — penyakit utama yang dilarang CLAUDE.md §3.1.
 *
 * Kelas biasa + method statis (bukan accessor model): bisa di-unit-test tanpa
 * DB maupun kelas inang. Preseden: App\Support\ColumnCustomization.
 */
class StatusPembayaranBagian
{
    /**
     * @return array{kelas: string, teks: string, ikon: string, tanggal: mixed}
     */
    public static function untuk(Dokumen $doc): array
    {
        // "Sudah dibayar" = status final ATAU tanggal_dibayar terisi (OR, bukan AND).
        $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

        if ($isPaid) {
            return [
                'kelas'   => 'sudah-dibayar',
                'teks'    => 'Sudah Dibayar',
                'ikon'    => 'fa-check-circle',
                'tanggal' => $doc->tanggal_dibayar,
            ];
        }

        // Dokumen sedang berada di Tim Pembayaran — belum dibayar, tapi sudah siap.
        if (str_contains(strtolower($doc->current_handler ?? ''), 'pembayaran')) {
            return [
                'kelas'   => 'siap-dibayar',
                'teks'    => 'Siap Dibayar',
                'ikon'    => 'fa-money-bill-wave',
                'tanggal' => $doc->getDataForRole('pembayaran')?->received_at,
            ];
        }

        return [
            'kelas'   => 'belum-dibayar',
            'teks'    => 'Belum Siap Dibayar',
            'ikon'    => 'fa-clock',
            'tanggal' => $doc->sent_at ?? $doc->created_at,
        ];
    }
}
