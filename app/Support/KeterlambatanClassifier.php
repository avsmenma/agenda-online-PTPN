<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * KeterlambatanClassifier
 *
 * Sumber kebenaran tunggal untuk klasifikasi keterlambatan dokumen per role,
 * agar widget "Status Keterlambatan" di dashboard dan halaman "Rekap
 * Keterlambatan" tidak lagi berbeda hasil.
 *
 * Aturan (mengikuti logika kartu Rekap Keterlambatan):
 *  - Umur dihitung dari received_at ke waktu selesai (endTime).
 *  - Dokumen SELESAI → umur dibekukan di processed_at (waktu permanen);
 *    dokumen masih berjalan → dibandingkan ke now (waktu terus berjalan).
 *    Kriteria selesai:
 *      * pembayaran: status_pembayaran = 'sudah_dibayar' DAN ada processed_at.
 *      * role lain : ada processed_at.
 *  - Ambang:
 *      * pembayaran: < 168 jam (1 minggu) AMAN, 168–504 jam PERINGATAN, > 504 TERLAMBAT.
 *      * role lain : < 24 jam AMAN, 24–72 jam PERINGATAN, > 72 jam TERLAMBAT.
 *  - Tanpa received_at (belum diterima) → NETRAL (null), tidak dihitung.
 */
class KeterlambatanClassifier
{
    /**
     * @return 'aman'|'peringatan'|'terlambat'|null  null = netral (belum diterima)
     */
    public static function bucket(
        string $roleCode,
        ?Carbon $receivedAt,
        ?Carbon $processedAt,
        ?string $statusPembayaran,
        Carbon $now
    ): ?string {
        if (!$receivedAt) {
            return null;
        }

        if ($roleCode === 'pembayaran') {
            $endTime = ($statusPembayaran === 'sudah_dibayar' && $processedAt) ? $processedAt : $now;
            $warn = 168;  // 1 minggu
            $late = 504;  // 3 minggu
        } else {
            $endTime = $processedAt ?: $now;
            $warn = 24;
            $late = 72;
        }

        $hours = $receivedAt->diffInHours($endTime);

        if ($hours < $warn) {
            return 'aman';
        }
        if ($hours < $late) {
            return 'peringatan';
        }
        return 'terlambat';
    }
}
