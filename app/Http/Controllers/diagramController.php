<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class diagramController extends Controller
{
    public function index(){
        // Get current year or from request
        $selectedYear = request('year', date('Y'));

        // Get available years for filter
        $availableYears = Dokumen::selectRaw('YEAR(tanggal_masuk) as year')
            ->whereNotNull('tanggal_masuk')
            ->where('created_by', 'ibuA')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // If no years found, use current year
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        // Initialize monthly data (1-12 for all months)
        $monthlyData = array_fill(0, 12, 0);
        $keterlambatanData = array_fill(0, 12, 0);
        $ketepatanData = array_fill(0, 12, 0);
        $selesaiData = array_fill(0, 12, 0);
        $tidakSelesaiData = array_fill(0, 12, 0);

        // Get monthly document statistics
        $monthlyStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month, COUNT(*) as count')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where('created_by', 'ibuA')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Get keterlambatan data
        $keterlambatanStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month,
            AVG(CASE WHEN DATEDIFF(COALESCE(updated_at, NOW()), tanggal_masuk) > 7
                THEN DATEDIFF(COALESCE(updated_at, NOW()), tanggal_masuk) - 7
                ELSE 0 END) as avg_keterlambatan')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where('created_by', 'ibuA')
            ->groupBy('month')
            ->pluck('avg_keterlambatan', 'month')
            ->toArray();

        // Get completion statistics
        $completionStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month,
            SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai_count,
            SUM(CASE WHEN status != "selesai" THEN 1 ELSE 0 END) as tidak_selesai_count')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where('created_by', 'ibuA')
            ->groupBy('month')
            ->get();

        // Fill the data arrays
        foreach ($monthlyStats as $month => $count) {
            $monthlyData[$month - 1] = $count;
        }

        foreach ($keterlambatanStats as $month => $keterlambatan) {
            $keterlambatanData[$month - 1] = min($keterlambatan, 100); // Cap at 100%
            $ketepatanData[$month - 1] = max(0, 100 - $keterlambatan); // Complement
        }

        foreach ($completionStats as $stat) {
            $selesaiData[$stat->month - 1] = $stat->selesai_count;
            $tidakSelesaiData[$stat->month - 1] = $stat->tidak_selesai_count;
        }

        // Indonesian month names
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $data = array(
            "title" => "Diagram Ibu A",
            "module" => "ibuA",
            "menuDiagram" => "Active",
            "menuDashboard" => "",
            'menuDokumen' => '',
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'monthlyData' => $monthlyData,
            'keterlambatanData' => $keterlambatanData,
            'ketepatanData' => $ketepatanData,
            'selesaiData' => $selesaiData,
            'tidakSelesaiData' => $tidakSelesaiData,
            'months' => $months,
        );
        
        return view('IbuA.diagram', $data);
    }
}
