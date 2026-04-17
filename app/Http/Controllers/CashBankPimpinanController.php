<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CashBankReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * CashBankPimpinanController
 * ──────────────────────────
 * Laporan Cash Bank khusus untuk role Owner / Admin.
 * Data dibaca langsung dari database cash_bank_new (READ-ONLY).
 */
final class CashBankPimpinanController extends Controller
{
    public function __construct(
        private readonly CashBankReportService $service
    ) {}

    /**
     * Halaman utama Laporan Cash Bank
     */
    public function index(Request $request): View
    {
        $tahun      = (int) ($request->get('tahun',      now()->year));
        $bulanDari  = (int) ($request->get('bulan_dari', 1));
        $bulanSampai = (int) ($request->get('bulan_sampai', now()->month));

        // Kumpulkan semua data
        $ringkasan         = $this->service->getRingkasanUtama($tahun, $bulanDari, $bulanSampai);
        $saldoRekening     = $this->service->getSaldoPerRekening();
        $saldoVA           = $this->service->getSaldoPerVirtualAccount();
        $penerimaanKategori= $this->service->getPenerimaanPerKategori($tahun, $bulanDari, $bulanSampai);
        $permintaanKategori= $this->service->getPermintaanPerKategori($tahun, $bulanDari, $bulanSampai);
        $penerimaanTerbaru = $this->service->getPenerimaanTerbaru(10);
        $trenBulanan       = $this->service->getTrenPermintaanVsDropping($tahun, $bulanDari, $bulanSampai);

        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];

        return view('owner.cashbank.index', compact(
            'ringkasan',
            'saldoRekening',
            'saldoVA',
            'penerimaanKategori',
            'permintaanKategori',
            'penerimaanTerbaru',
            'trenBulanan',
            'tahun',
            'bulanDari',
            'bulanSampai',
            'bulanList',
        ));
    }

    /**
     * AJAX endpoint: data chart tren per tahun
     */
    public function chartData(Request $request): JsonResponse
    {
        $tahun      = (int) ($request->get('tahun',       now()->year));
        $bulanDari  = (int) ($request->get('bulan_dari',  1));
        $bulanSampai = (int) ($request->get('bulan_sampai', 12));

        return response()->json([
            'tren'               => $this->service->getTrenPermintaanVsDropping($tahun, $bulanDari, $bulanSampai),
            'penerimaan_kategori'=> $this->service->getPenerimaanPerKategori($tahun, $bulanDari, $bulanSampai),
        ]);
    }
}
