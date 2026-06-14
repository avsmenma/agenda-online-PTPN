<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Dokumen;
use Carbon\Carbon;

/**
 * Membangun data dashboard untuk role workflow (Team Verifikasi, Perpajakan, Akutansi).
 *
 * Semua role ini memiliki konsep yang sama:
 *  - sekumpulan dokumen yang "ditangani" role (visibility set),
 *  - keterlambatan berdasarkan waktu diterima (received_at) di dokumen_role_data,
 *  - sebaran dokumen per bagian asal,
 *  - rekomendasi dokumen lama yang belum dikerjakan.
 *
 * Perbedaan antar role (status visibility, kartu ke-4, dsb) diatur lewat config.
 */
trait BuildsRoleDashboard
{
    /**
     * Konfigurasi per role.
     */
    protected function roleDashboardConfig(string $role): array
    {
        $configs = [
            'team_verifikasi' => [
                'roleCode'      => 'team_verifikasi',
                'handlers'      => ['team_verifikasi'],
                'label'         => 'Verifikasi',
                'icon'          => 'fas fa-clipboard-check',
                'routeName'     => 'dashboard.verifikasi',
                'docRouteName'  => 'documents.verifikasi.index',
                'fourth'        => 'dikembalikan',
                // Status yang menandakan dokumen masih "milik" verifikasi walau current_handler berpindah.
                'visibilityStatuses' => [
                    'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
                    'waiting_approval_perpajakan', 'waiting_approval_akuntansi', 'waiting_approval_pembayaran',
                    'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_pembayaran',
                    'menunggu_di_approve',
                ],
            ],
            'perpajakan' => [
                'roleCode'      => 'perpajakan',
                'handlers'      => ['perpajakan'],
                'label'         => 'Perpajakan',
                'icon'          => 'fas fa-file-invoice-dollar',
                'routeName'     => 'dashboard.perpajakan',
                'docRouteName'  => 'documents.perpajakan.index',
                'fourth'        => 'hari_ini',
                'visibilityStatuses' => ['sent_to_akutansi', 'sent_to_pembayaran'],
            ],
            'akutansi' => [
                'roleCode'      => 'akutansi',
                'handlers'      => ['akutansi'],
                'label'         => 'Akutansi',
                'icon'          => 'fas fa-calculator',
                'routeName'     => 'dashboard.akutansi',
                'docRouteName'  => 'documents.akutansi.index',
                'fourth'        => 'hari_ini',
                'visibilityStatuses' => ['sent_to_akutansi', 'sent_to_pembayaran'],
            ],
            'pembayaran' => [
                'roleCode'      => 'pembayaran',
                'handlers'      => ['pembayaran'],
                'label'         => 'Pembayaran',
                'icon'          => 'fas fa-money-bill-wave',
                'routeName'     => 'dashboard.pembayaran',
                'docRouteName'  => 'documents.pembayaran.index',
                'fourth'        => 'hari_ini',
                'visibilityStatuses' => ['sent_to_pembayaran'],
            ],
        ];

        return $configs[$role];
    }

    /**
     * Query dasar dokumen yang ditangani oleh role (visibility set), tanpa CSV import.
     */
    protected function roleVisibilityQuery(array $cfg)
    {
        return Dokumen::query()
            ->excludeCsvImports()
            ->where(function ($q) use ($cfg) {
                $q->whereIn('current_handler', $cfg['handlers'])
                    ->orWhereIn('status', $cfg['visibilityStatuses']);

                // Perpajakan: dokumen completed/selesai yang sempat diproses perpajakan tetap terhitung.
                if ($cfg['roleCode'] === 'perpajakan') {
                    $q->orWhere(function ($c) {
                        $c->whereIn('status', ['completed', 'selesai'])
                            ->whereNotNull('status_perpajakan');
                    });
                }
            });
    }

    /**
     * Hitung seluruh data dashboard untuk sebuah role.
     */
    protected function buildRoleDashboardData(string $role): array
    {
        $cfg = $this->roleDashboardConfig($role);
        $now = Carbon::now();

        // 1. Total seluruh dokumen agenda (lintas role).
        $totalDokumenAgenda = Dokumen::excludeCsvImports()->count();

        // 2. Total dokumen yang ditangani role ini.
        $totalDokumenRole = $this->roleVisibilityQuery($cfg)->count();

        // 3. Total dokumen selesai (completed/selesai) yang pernah melewati role ini.
        $totalSelesai = Dokumen::excludeCsvImports()
            ->whereIn('status', ['completed', 'selesai'])
            ->whereHas('roleData', function ($q) use ($cfg) {
                $q->where('role_code', $cfg['roleCode']);
            })
            ->count();

        // 4. Kartu ke-4: Dikembalikan (verifikasi) atau Hari Ini (perpajakan/akutansi).
        if ($cfg['fourth'] === 'dikembalikan') {
            $fourthLabel = 'Dikembalikan';
            $fourthSub   = 'perlu perbaikan';
            $fourthIcon  = 'fas fa-undo';
            $fourthCount = Dokumen::excludeCsvImports()
                ->where(function ($q) {
                    $q->where('status', 'returned_to_verifikasi')
                        ->orWhere(function ($legacy) {
                            $legacy->where('status', 'returned_to_department')
                                ->where('current_handler', 'team_verifikasi')
                                ->whereIn('return_source', ['perpajakan', 'akutansi']);
                        });
                })
                ->count();
        } else {
            $fourthLabel = 'Hari Ini';
            $fourthSub   = 'masuk hari ini';
            $fourthIcon  = 'fas fa-calendar-day';
            $fourthCount = Dokumen::excludeCsvImports()
                ->whereHas('roleData', function ($q) use ($cfg, $now) {
                    $q->where('role_code', $cfg['roleCode'])
                        ->whereDate('received_at', $now->toDateString());
                })
                ->count();
        }

        // 5. Keterlambatan (aman/peringatan/terlambat) berdasarkan received_at role.
        $delayDocs = $this->roleVisibilityQuery($cfg)
            ->with(['roleData' => function ($q) use ($cfg) {
                $q->where('role_code', $cfg['roleCode']);
            }])
            ->get(['id', 'status', 'current_handler', 'bagian']);

        $aman = $peringatan = $terlambat = 0;
        $bagianCounter = [];

        foreach ($delayDocs as $doc) {
            // Sebaran per bagian asal.
            $bagianName = trim((string) $doc->bagian) !== '' ? $doc->bagian : 'Tanpa Bagian';
            $bagianCounter[$bagianName] = ($bagianCounter[$bagianName] ?? 0) + 1;

            // Bucket keterlambatan.
            $roleData = $doc->roleData->first();
            $isSent = !in_array($doc->current_handler, $cfg['handlers'], true);

            if ($roleData && $roleData->received_at) {
                $receivedAt = Carbon::parse($roleData->received_at);
                $hoursDiff = ($isSent && $roleData->processed_at)
                    ? $receivedAt->diffInHours(Carbon::parse($roleData->processed_at))
                    : $receivedAt->diffInHours($now);

                if ($hoursDiff < 24) {
                    $aman++;
                } elseif ($hoursDiff < 72) {
                    $peringatan++;
                } else {
                    $terlambat++;
                }
            } else {
                // Tanpa received_at: jika sudah diteruskan anggap aman, jika masih menggantung anggap terlambat.
                $isSent ? $aman++ : $terlambat++;
            }
        }

        // Urutkan sebaran bagian dari terbanyak.
        arsort($bagianCounter);
        $bagianLabels = array_keys($bagianCounter);
        $bagianCounts = array_values($bagianCounter);

        // 6. Rekomendasi: dokumen yang masih di role ini, belum dikerjakan, paling lama menunggu.
        $excludedStatuses = [
            'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
            'completed', 'selesai',
            'returned_to_bidang', 'returned_to_department', 'returned_to_verifikasi',
            'waiting_approval_perpajakan', 'waiting_approval_akuntansi', 'waiting_approval_pembayaran',
            'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_pembayaran',
            'menunggu_di_approve',
        ];

        $candidates = Dokumen::excludeCsvImports()
            ->whereIn('current_handler', $cfg['handlers'])
            ->whereNotIn('status', $excludedStatuses)
            ->with(['roleData' => function ($q) use ($cfg) {
                $q->where('role_code', $cfg['roleCode']);
            }])
            ->get();

        $recommendations = $candidates->map(function ($doc) use ($cfg, $now) {
            $roleData = $doc->roleData->first();
            $waitFrom = ($roleData && $roleData->received_at)
                ? Carbon::parse($roleData->received_at)
                : ($doc->tanggal_masuk ? Carbon::parse($doc->tanggal_masuk) : Carbon::parse($doc->created_at));

            $doc->wait_from = $waitFrom;
            $doc->wait_hours = $waitFrom->diffInHours($now);
            return $doc;
        })
            ->sortByDesc('wait_hours')
            ->take(8)
            ->values();

        return [
            'cfg'                => $cfg,
            'totalDokumenAgenda' => $totalDokumenAgenda,
            'totalDokumenRole'   => $totalDokumenRole,
            'totalSelesai'       => $totalSelesai,
            'fourthLabel'        => $fourthLabel,
            'fourthSub'          => $fourthSub,
            'fourthIcon'         => $fourthIcon,
            'fourthCount'        => $fourthCount,
            'aman'               => $aman,
            'peringatan'         => $peringatan,
            'terlambat'          => $terlambat,
            'bagianLabels'       => $bagianLabels,
            'bagianCounts'       => $bagianCounts,
            'recommendations'    => $recommendations,
        ];
    }
}
