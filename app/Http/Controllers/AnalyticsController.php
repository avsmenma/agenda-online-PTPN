<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Roles to analyze
     */
    private $roles = [
        'team_verifikasi' => ['name' => 'Team Verifikasi', 'color' => '#1a4d3e'],
        'perpajakan' => ['name' => 'Team Perpajakan', 'color' => '#2196F3'],
        'akutansi' => ['name' => 'Team Akuntansi', 'color' => '#9C27B0'],
        'pembayaran' => ['name' => 'Team Pembayaran', 'color' => '#FF9800'],
    ];

    /**
     * Display the analytics dashboard
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        // Get available years for filter
        $availableYears = Dokumen::selectRaw('DISTINCT YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        // Get analytics data
        $analyticsData = $this->calculateAnalytics($bulan, $tahun);

        return view('owner.analytics', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'availableYears' => $availableYears,
            'analyticsData' => $analyticsData,
            'roles' => $this->roles,
        ]);
    }

    /**
     * Get analytics data as JSON (for AJAX updates)
     */
    public function getAnalyticsData(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        $analyticsData = $this->calculateAnalytics($bulan, $tahun);

        return response()->json([
            'success' => true,
            'data' => $analyticsData,
        ]);
    }

    /**
     * Calculate analytics for all roles
     */
    private function calculateAnalytics($bulan, $tahun)
    {
        $result = [];

        foreach ($this->roles as $roleCode => $roleInfo) {
            $roleStats = $this->getRoleStatistics($roleCode, $bulan, $tahun);

            // Calculate performance score
            $performanceData = $this->calculatePerformanceScore(
                $roleStats['total'],
                $roleStats['aman'],
                $roleStats['peringatan'],
                $roleStats['terlambat']
            );

            $result[$roleCode] = [
                'name' => $roleInfo['name'],
                'color' => $roleInfo['color'],
                'stats' => $roleStats,
                'performance' => $performanceData,
            ];
        }

        return $result;
    }

    /**
     * Get statistics for a specific role
     */
    private function getRoleStatistics($roleCode, $bulan, $tahun)
    {
        $now = Carbon::now();

        // Query documents received by this role in the specified month/year
        $query = DokumenRoleData::where('role_code', $roleCode)
            ->whereNotNull('received_at')
            ->whereYear('received_at', $tahun)
            ->whereMonth('received_at', $bulan);

        $documents = $query->get();

        $total = $documents->count();
        $aman = 0;
        $peringatan = 0;
        $terlambat = 0;

        foreach ($documents as $doc) {
            // Calculate elapsed time
            // If processed, use processed_at; otherwise use current time
            $endTime = $doc->processed_at ? Carbon::parse($doc->processed_at) : $now;
            $startTime = Carbon::parse($doc->received_at);

            $diffHours = $startTime->diffInHours($endTime);

            // Categorize based on elapsed hours
            // < 24 hours = Aman (safe)
            // 24-72 hours = Peringatan (warning)
            // >= 72 hours = Terlambat (late)
            if ($diffHours < 24) {
                $aman++;
            } elseif ($diffHours < 72) {
                $peringatan++;
            } else {
                $terlambat++;
            }
        }

        return [
            'total' => $total,
            'aman' => $aman,
            'peringatan' => $peringatan,
            'terlambat' => $terlambat,
        ];
    }

    /**
     * Calculate performance score and grade
     * 
     * Formula:
     * - efficiency = (aman / total) * 100
     * - penalty = (terlambat / total) * 50 (heavy penalty for late documents)
     * - score = efficiency - penalty
     * - grade = A (>=90), B (>=75), C (>=60), D (<60)
     */
    private function calculatePerformanceScore($total, $aman, $peringatan, $terlambat)
    {
        if ($total == 0) {
            return [
                'score' => 0,
                'grade' => '-',
                'efficiency' => 0,
                'penalty' => 0,
                'color' => '#6c757d', // gray for no data
            ];
        }

        $efficiency = ($aman / $total) * 100;
        $penalty = ($terlambat / $total) * 50;
        $score = max(0, $efficiency - $penalty); // Don't go below 0

        // Determine grade
        if ($score >= 90) {
            $grade = 'A';
            $color = '#28a745'; // green
        } elseif ($score >= 75) {
            $grade = 'B';
            $color = '#17a2b8'; // blue
        } elseif ($score >= 60) {
            $grade = 'C';
            $color = '#ffc107'; // yellow
        } else {
            $grade = 'D';
            $color = '#dc3545'; // red
        }

        return [
            'score' => round($score, 1),
            'grade' => $grade,
            'efficiency' => round($efficiency, 1),
            'penalty' => round($penalty, 1),
            'color' => $color,
        ];
    }
}
