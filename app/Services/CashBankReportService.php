<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CashBankReportService
 * ─────────────────────
 * Semua query ke database cash_bank_new bersifat READ-ONLY.
 * DILARANG menambahkan INSERT, UPDATE, atau DELETE apapun.
 */
final class CashBankReportService
{
    private const CONN = 'cash_bank_new';

    private const BULAN_LABEL = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Agu',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    // ─────────────────────────────────────────────────────
    // 1. RINGKASAN SALDO PER REKENING BANK
    // ─────────────────────────────────────────────────────

    /**
     * Saldo bersih per sumber dana (rekening bank).
     * Saldo = Total Masuk (bank_masuk.debet) – Total Keluar (bank_keluars.kredit)
     */
    public function getSaldoPerRekening(): array
    {
        try {
            $rows = DB::connection(self::CONN)
                ->table('sumber_dana')
                ->select('sumber_dana.id_sumber_dana', 'sumber_dana.nama_sumber_dana')
                ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_masuk')
                ->selectRaw('COALESCE((SELECT SUM(CAST(kredit AS DECIMAL(18,2))) FROM bank_keluars WHERE bank_keluars.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_keluar')
                ->orderBy('sumber_dana.id_sumber_dana')
                ->get()
                ->map(function ($sd) {
                    $sd->saldo_bersih = (float) $sd->total_masuk - (float) $sd->total_keluar;
                    // Ekstrak nama singkat rekening
                    $sd->nama_singkat = preg_replace('/\s*\*.*$/', '', $sd->nama_sumber_dana);
                    $sd->no_rekening  = '';
                    if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                        $sd->no_rekening = trim($m[1]);
                    }
                    return $sd;
                });

            return $rows->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Saldo per Virtual Account tujuan (kebun / unit kerja)
     */
    public function getSaldoPerVirtualAccount(): array
    {
        try {
            return DB::connection(self::CONN)
                ->table('bank_tujuan')
                ->select('bank_tujuan.id_bank_tujuan', 'bank_tujuan.nama_tujuan')
                ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_bank_tujuan = bank_tujuan.id_bank_tujuan), 0) as total_masuk')
                ->selectRaw('COALESCE((SELECT SUM(CAST(kredit AS DECIMAL(18,2))) FROM bank_keluars WHERE bank_keluars.id_bank_tujuan = bank_tujuan.id_bank_tujuan), 0) as total_keluar')
                ->orderBy('bank_tujuan.nama_tujuan')
                ->get()
                ->map(function ($va) {
                    $va->saldo = (float) $va->total_masuk - (float) $va->total_keluar;
                    return $va;
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────
    // 2. PENERIMAAN (uang masuk dari penjualan komoditas)
    // ─────────────────────────────────────────────────────

    /**
     * Total penerimaan per kategori (CPO, Kernel, TBS, Karet, Cangkang, dll.)
     * Filter: tahun + range bulan (1–12)
     */
    public function getPenerimaanPerKategori(int $tahun, int $bulanDari = 1, int $bulanSampai = 12): array
    {
        try {
            return DB::connection(self::CONN)
                ->table('penerimas')
                ->join('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'penerimas.id_kategori_kriteria')
                ->whereYear('penerimas.tanggal', $tahun)
                ->whereBetween(DB::raw('MONTH(penerimas.tanggal)'), [$bulanDari, $bulanSampai])
                ->groupBy('penerimas.id_kategori_kriteria', 'kategori_kriteria.nama_kriteria')
                ->select(
                    'kategori_kriteria.nama_kriteria',
                    DB::raw('SUM(penerimas.nilai_inc_ppn) as total'),
                    DB::raw('COUNT(*) as jumlah_transaksi')
                )
                ->orderByDesc('total')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Daftar transaksi penerimaan terbaru
     */
    public function getPenerimaanTerbaru(int $limit = 10): array
    {
        try {
            return DB::connection(self::CONN)
                ->table('penerimas')
                ->join('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'penerimas.id_kategori_kriteria')
                ->select(
                    'penerimas.kontrak',
                    'penerimas.pembeli',
                    'penerimas.tanggal',
                    'penerimas.volume',
                    'penerimas.harga',
                    'penerimas.nilai_inc_ppn',
                    'kategori_kriteria.nama_kriteria'
                )
                ->orderByDesc('penerimas.tanggal')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────
    // 3. RENCANA vs REALISASI PENGELUARAN
    // ─────────────────────────────────────────────────────

    /**
     * Perbandingan rencana (permintaan) vs realisasi (dropping) per bulan
     * Untuk chart tren bulanan
     */
    public function getTrenPermintaanVsDropping(int $tahun, int $bulanDari = 1, int $bulanSampai = 12): array
    {
        try {
            // Permintaan (rencana)
            $permintaan = DB::connection(self::CONN)
                ->table('permintaans')
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanDari, $bulanSampai])
                ->select('bulan', DB::raw('SUM(M1 + M2 + M3 + M4) as total'))
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            // Dropping (realisasi)
            $dropping = DB::connection(self::CONN)
                ->table('droppings')
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanDari, $bulanSampai])
                ->select('bulan', DB::raw('SUM(M1 + M2 + M3 + M4) as total'))
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            $result = [];
            for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                $result[] = [
                    'bulan'       => $b,
                    'label'       => self::BULAN_LABEL[$b] ?? "Bln $b",
                    'permintaan'  => (float) ($permintaan[$b] ?? 0),
                    'dropping'    => (float) ($dropping[$b] ?? 0),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Total permintaan vs dropping per kategori keluar (untuk donut/bar chart)
     */
    public function getPermintaanPerKategori(int $tahun, int $bulanDari = 1, int $bulanSampai = 12): array
    {
        try {
            return DB::connection(self::CONN)
                ->table('permintaans')
                ->join('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'permintaans.id_kategori_kriteria')
                ->where('permintaans.tahun', $tahun)
                ->whereBetween('permintaans.bulan', [$bulanDari, $bulanSampai])
                ->where('kategori_kriteria.tipe', 'Keluar')
                ->groupBy('permintaans.id_kategori_kriteria', 'kategori_kriteria.nama_kriteria')
                ->select(
                    'kategori_kriteria.nama_kriteria',
                    DB::raw('SUM(M1 + M2 + M3 + M4) as total_rencana')
                )
                ->orderByDesc('total_rencana')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────
    // 4. RINGKASAN TOTAL (untuk stat cards)
    // ─────────────────────────────────────────────────────

    public function getRingkasanUtama(int $tahun, int $bulanDari = 1, int $bulanSampai = 12): array
    {
        try {
            // Total penerimaan
            $totalPenerimaan = (float) DB::connection(self::CONN)
                ->table('penerimas')
                ->whereYear('tanggal', $tahun)
                ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
                ->sum('nilai_inc_ppn');

            // Total permintaan (rencana keluar)
            $totalPermintaan = (float) DB::connection(self::CONN)
                ->table('permintaans')
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanDari, $bulanSampai])
                ->selectRaw('SUM(M1 + M2 + M3 + M4) as total')
                ->value('total');

            // Total dropping (realisasi keluar)
            $totalDropping = (float) DB::connection(self::CONN)
                ->table('droppings')
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanDari, $bulanSampai])
                ->selectRaw('SUM(M1 + M2 + M3 + M4) as total')
                ->value('total');

            // Total saldo semua rekening
            $totalSaldo = (float) DB::connection(self::CONN)
                ->table('sumber_dana')
                ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk), 0) - COALESCE((SELECT SUM(CAST(kredit AS DECIMAL(18,2))) FROM bank_keluars), 0) as saldo')
                ->value('saldo');

            return [
                'total_penerimaan' => $totalPenerimaan,
                'total_permintaan' => $totalPermintaan,
                'total_dropping'   => $totalDropping,
                'total_saldo'      => $totalSaldo,
                'realisasi_pct'    => $totalPermintaan > 0
                    ? round(($totalDropping / $totalPermintaan) * 100, 1)
                    : 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_penerimaan' => 0,
                'total_permintaan' => 0,
                'total_dropping'   => 0,
                'total_saldo'      => 0,
                'realisasi_pct'    => 0,
            ];
        }
    }
}
