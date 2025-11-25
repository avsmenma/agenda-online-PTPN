<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class diagramController extends Controller
{
    public function index(){
        // Get filter year (default to current year)
        $selectedYear = request('year', date('Y'));
        
        // Get all documents created by IbuA for the selected year
        $allDokumens = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('nomor_agenda')
            ->whereYear('created_at', $selectedYear)
            ->get();
        
        // Chart 1: Statistik Jumlah Dokumen per Bulan
        $monthlyData = [];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            })->count();
        }
        
        // Chart 2: Statistik Keterlambatan Dokumen (based on deadline_at)
        $keterlambatanData = [];
        $ketepatanData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthDocs = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            });
            
            $terlambat = 0;
            $tepat = 0;
            $totalWithDeadline = 0;
            
            foreach ($monthDocs as $doc) {
                if ($doc->deadline_at) {
                    $totalWithDeadline++;
                    $now = now();
                    if ($doc->deadline_at->lt($now)) {
                        // Check if sent to IbuB (completed)
                        if ($doc->sent_to_ibub_at) {
                            if ($doc->sent_to_ibub_at->gt($doc->deadline_at)) {
                                $terlambat++;
                            } else {
                                $tepat++;
                            }
                        } else {
                            // Not sent yet, check if overdue
                            $terlambat++;
                        }
                    } else {
                        // Not yet overdue
                        $tepat++;
                    }
                }
            }
            
            $keterlambatanData[] = $totalWithDeadline > 0 ? round(($terlambat / $totalWithDeadline) * 100, 1) : 0;
            $ketepatanData[] = $totalWithDeadline > 0 ? round(($tepat / $totalWithDeadline) * 100, 1) : 0;
        }
        
        // Chart 3: Statistik Jumlah Dokumen Selesai per Bulan
        $selesaiData = [];
        $tidakSelesaiData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthDocs = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            });
            
            // Selesai = sudah dikirim ke IbuB
            $selesai = $monthDocs->filter(function($doc) {
                return $doc->sent_to_ibub_at !== null;
            })->count();
            
            $tidakSelesai = $monthDocs->filter(function($doc) {
                return $doc->sent_to_ibub_at === null;
            })->count();
            
            $selesaiData[] = $selesai;
            $tidakSelesaiData[] = $tidakSelesai;
        }
        
        // Get available years for filter
        $availableYears = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('nomor_agenda')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        $data = array(
            "title" => "Diagram",
            "module" => "IbuA",
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
        return view('IbuA.diagram',$data);
    }
}
