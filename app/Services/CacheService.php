<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Dokumen;
use App\Models\DokumenRoleData;

class CacheService
{
    // Cache TTL in seconds
    const DASHBOARD_STATS_TTL = 300; // 5 minutes
    const FILTER_OPTIONS_TTL = 3600; // 1 hour
    const USER_COUNT_TTL = 600; // 10 minutes

    /**
     * Get cached dashboard statistics for a role
     */
    public function getDashboardStats(string $role, ?int $userId = null): array
    {
        $cacheKey = $this->getDashboardStatsKey($role, $userId);

        return Cache::remember($cacheKey, self::DASHBOARD_STATS_TTL, function () use ($role) {
            return $this->calculateDashboardStats($role);
        });
    }

    /**
     * Get cached filter options (vendors, bagian, kebun, etc.)
     */
    public function getFilterOptions(): array
    {
        return Cache::remember('filter_options', self::FILTER_OPTIONS_TTL, function () {
            return [
                'vendors' => Dokumen::whereNotNull('dibayar_kepada')
                    ->distinct()
                    ->pluck('dibayar_kepada')
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray(),

                'bagian' => Dokumen::whereNotNull('bagian')
                    ->distinct()
                    ->pluck('bagian')
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray(),

                'kebun' => Dokumen::whereNotNull('kebun')
                    ->distinct()
                    ->pluck('kebun')
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray(),

                'kriteria_cf' => \App\Models\KategoriKriteria::pluck('nama', 'id')->toArray(),
            ];
        });
    }

    /**
     * Get cached user count by role
     */
    public function getUserCount(string $role): int
    {
        $cacheKey = "user_count_{$role}";

        return Cache::remember($cacheKey, self::USER_COUNT_TTL, function () use ($role) {
            return \App\Models\User::where('role', $role)->count();
        });
    }

    /**
     * Clear dashboard cache for specific role
     */
    public function clearDashboardCache(string $role, ?int $userId = null): void
    {
        $cacheKey = $this->getDashboardStatsKey($role, $userId);
        Cache::forget($cacheKey);

        // Also clear global role cache
        Cache::forget("dashboard_stats_{$role}");
    }

    /**
     * Clear all dashboard caches
     */
    public function clearAllDashboardCache(): void
    {
        $roles = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran', 'owner'];

        foreach ($roles as $role) {
            Cache::forget("dashboard_stats_{$role}");
        }
    }

    /**
     * Clear filter options cache
     */
    public function clearFilterCache(): void
    {
        Cache::forget('filter_options');
    }

    /**
     * Clear all caches (use when document is created/updated/deleted)
     */
    public function clearAll(): void
    {
        $this->clearAllDashboardCache();
        $this->clearFilterCache();
        Cache::forget('user_count_*');
    }

    /**
     * Get cache key for dashboard stats
     */
    private function getDashboardStatsKey(string $role, ?int $userId): string
    {
        if ($userId) {
            return "dashboard_stats_{$role}_{$userId}";
        }
        return "dashboard_stats_{$role}";
    }

    /**
     * Calculate dashboard statistics (example implementation)
     * This should be customized based on actual dashboard requirements
     */
    private function calculateDashboardStats(string $role): array
    {
        // Default implementation - customize per role
        $stats = [
            'total_documents' => 0,
            'pending' => 0,
            'processed' => 0,
            'overdue' => 0,
        ];

        switch ($role) {
            case 'team_verifikasi':
            case 'perpajakan':
            case 'akutansi':
            case 'pembayaran':
                $stats['total_documents'] = DokumenRoleData::where('role_code', $role)
                    ->whereNotNull('received_at')
                    ->whereNull('processed_at')
                    ->count();
                break;

            case 'operator':
                $stats['total_documents'] = Dokumen::where('created_by', 'operator')->count();
                break;

            case 'owner':
                $stats['total_documents'] = Dokumen::count();
                break;
        }

        return $stats;
    }

    /**
     * Get cached recent activities
     */
    public function getRecentActivities(string $role, int $limit = 10): array
    {
        $cacheKey = "recent_activities_{$role}_{$limit}";

        return Cache::remember($cacheKey, 300, function () use ($role, $limit) {
            return \App\Models\DokumenActivityLog::where('stage', $role)
                ->with('dokumen:id,nomor_agenda,nomor_spp')
                ->latest('action_at')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Clear recent activities cache
     */
    public function clearRecentActivities(string $role): void
    {
        Cache::forget("recent_activities_{$role}_*");
    }
}
