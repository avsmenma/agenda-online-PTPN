<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Bagian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenRekapanController extends Controller
{

    /**
     * Display the rekapan page
     */
    public function index(Request $request): View
    {
        $query = Dokumen::where('created_by', 'operator')
            ->with(['dokumenPos', 'dokumenPrs']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            // Validate against database
            $validBagian = Bagian::active()->where('kode', $selectedBagian)->exists();
            if ($validBagian) {
                $query->where('bagian', $selectedBagian);
            }
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

        // Get bagian list from database
        $bagianList = Bagian::active()->ordered()->pluck('nama', 'kode')->toArray();

        $data = array(
            "title" => "Rekapan Dokumen",
            "module" => "Operator",
            "menuDokumen" => "active",
            "menuRekapan" => "active",
            "menuDaftarDokumen" => "",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "statistics" => $statistics,
            "bagianList" => $bagianList,
            "selectedBagian" => $selectedBagian,
        );

        return view('operator.dokumens.rekapan', $data);
    }

    /**
     * Get statistics for documents
     */
    private function getStatistics(string $filterBagian = ''): array
    {
        $query = Dokumen::where('created_by', 'operator');

        if ($filterBagian) {
            $validBagian = Bagian::active()->where('kode', $filterBagian)->exists();
            if ($validBagian) {
                $query->where('bagian', $filterBagian);
            }
        }

        $total = $query->count();

        $bagianStats = [];
        $bagianList = Bagian::active()->ordered()->get();
        foreach ($bagianList as $bagian) {
            $bagianQuery = Dokumen::where('created_by', 'operator')->where('bagian', $bagian->kode);
            $bagianStats[$bagian->kode] = [
                'name' => $bagian->nama,
                'total' => $bagianQuery->count()
            ];
        }

        return [
            'total_documents' => $total,
            'by_bagian' => $bagianStats,
            'by_status' => [
                'draft' => $query->where('status', 'draft')->count(),
                'sent_to_Team Verifikasi' => $query->where('status', 'sent_to_Team Verifikasi')->count(),
                'sedang diproses' => $query->where('status', 'sedang diproses')->count(),
                'selesai' => $query->where('status', 'selesai')->count(),
                'returned_to_Operator' => $query->where('status', 'returned_to_Operator')->count(),
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
        $baseQuery = Dokumen::where('created_by', 'operator')
            ->whereYear('tanggal_masuk', $selectedYear);

        // Filter by bagian if selected
        if ($selectedBagian) {
            $validBagian = Bagian::active()->where('kode', $selectedBagian)->exists();
            if ($validBagian) {
                $baseQuery->where('bagian', $selectedBagian);
            }
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
        $availableYears = Dokumen::where('created_by', 'operator')
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
            'module' => 'operator',
            'menuDokumen' => 'active',
            'menuRekapan' => 'active',
            'selectedYear' => (int)$selectedYear,
            'selectedBagian' => $selectedBagian,
            'selectedMonth' => $selectedMonth ? (int)$selectedMonth : null,
            'yearlySummary' => $yearlySummary,
            'monthlyStats' => $monthlyStats,
            'dokumens' => $tableDokumens,
            'availableYears' => $availableYears,
            'bagianList' => Bagian::active()->ordered()->pluck('nama', 'kode')->toArray(),
        ];

        return view('operator.dokumens.analytics', $data);
    }
}


