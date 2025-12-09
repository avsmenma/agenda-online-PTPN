<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenRekapanController extends Controller
{
    /**
     * Daftar bagian yang tersedia
     */
    private const BAGIAN_LIST = [
        'DPM' => 'DPM',
        'SKH' => 'SKH',
        'SDM' => 'SDM',
        'TEP' => 'TEP',
        'KPL' => 'KPL',
        'AKN' => 'AKN',
        'TAN' => 'TAN',
        'PMO' => 'PMO'
    ];

    /**
     * Display the rekapan page
     */
    public function index(Request $request): View
    {
        $query = Dokumen::where('created_by', 'ibuA')
            ->with(['dokumenPos', 'dokumenPrs']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian && in_array($selectedBagian, array_keys(self::BAGIAN_LIST))) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                  ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
            });
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        $perPage = $request->get('per_page', 10);
        $dokumens = $query->latest('tanggal_masuk')->paginate($perPage)->appends($request->query());

        // Get statistics
        $statistics = $this->getStatistics($selectedBagian);

        $data = array(
            "title" => "Rekapan Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuRekapan" => "active",
            "menuDaftarDokumen" => "",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "statistics" => $statistics,
            "bagianList" => self::BAGIAN_LIST,
            "selectedBagian" => $selectedBagian,
        );

        return view('IbuA.dokumens.rekapan', $data);
    }

    /**
     * Get statistics for documents
     */
    private function getStatistics(string $filterBagian = ''): array
    {
        $query = Dokumen::where('created_by', 'ibuA');

        if ($filterBagian && in_array($filterBagian, array_keys(self::BAGIAN_LIST))) {
            $query->where('bagian', $filterBagian);
        }

        $total = $query->count();

        $bagianStats = [];
        foreach (self::BAGIAN_LIST as $bagianCode => $bagianName) {
            $bagianQuery = Dokumen::where('created_by', 'ibuA')->where('bagian', $bagianCode);
            $bagianStats[$bagianCode] = [
                'name' => $bagianName,
                'total' => $bagianQuery->count()
            ];
        }

        return [
            'total_documents' => $total,
            'by_bagian' => $bagianStats,
            'by_status' => [
                'draft' => $query->where('status', 'draft')->count(),
                'sent_to_ibub' => $query->where('status', 'sent_to_ibub')->count(),
                'sedang diproses' => $query->where('status', 'sedang diproses')->count(),
                'selesai' => $query->where('status', 'selesai')->count(),
                'returned_to_ibua' => $query->where('status', 'returned_to_ibua')->count(),
            ]
        ];
    }

    /**
     * Display analytics page for Ibu Tarapul
     */
    public function analytics(Request $request): View
    {
        // Get selected year and bagian from request
        $selectedYear = $request->get('year', date('Y'));
        $selectedBagian = $request->get('bagian', '');
        $selectedMonth = $request->get('month', null);

        // Validate year
        if (!is_numeric($selectedYear) || $selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = date('Y');
        }

        // Base query for Ibu Tarapul documents
        $baseQuery = Dokumen::where('created_by', 'ibuA')
            ->whereYear('tanggal_masuk', $selectedYear);

        // Filter by bagian if selected
        if ($selectedBagian && in_array($selectedBagian, array_keys(self::BAGIAN_LIST))) {
            $baseQuery->where('bagian', $selectedBagian);
        }

        // Get yearly summary
        $yearlySummary = [
            'total_dokumen' => (clone $baseQuery)->count(),
            'total_nominal' => (clone $baseQuery)->sum('nilai_rupiah') ?? 0,
        ];

        // Get monthly statistics
        $monthlyStats = [];
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        for ($month = 1; $month <= 12; $month++) {
            $monthQuery = (clone $baseQuery)->whereMonth('tanggal_masuk', $month);
            $monthStats = [
                'name' => $monthNames[$month],
                'count' => $monthQuery->count(),
                'total_nominal' => $monthQuery->sum('nilai_rupiah') ?? 0,
            ];
            $monthlyStats[$month] = $monthStats;
        }

        // Get documents for table (filter by month if selected)
        $tableQuery = (clone $baseQuery);
        if ($selectedMonth && $selectedMonth >= 1 && $selectedMonth <= 12) {
            $tableQuery->whereMonth('tanggal_masuk', $selectedMonth);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $tableDokumens = $tableQuery->latest('tanggal_masuk')->paginate($perPage)->appends($request->query());

        // Get available years
        $availableYears = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('tanggal_masuk')
            ->selectRaw('DISTINCT YEAR(tanggal_masuk) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        $data = [
            'title' => 'Analitik Dokumen',
            'module' => 'IbuA',
            'menuDokumen' => 'active',
            'menuRekapan' => 'active',
            'selectedYear' => (int)$selectedYear,
            'selectedBagian' => $selectedBagian,
            'selectedMonth' => $selectedMonth ? (int)$selectedMonth : null,
            'yearlySummary' => $yearlySummary,
            'monthlyStats' => $monthlyStats,
            'dokumens' => $tableDokumens,
            'availableYears' => $availableYears,
            'bagianList' => self::BAGIAN_LIST,
        ];

        return view('IbuA.dokumens.analytics', $data);
    }
}