<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DocumentTracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OwnerDashboardController extends Controller
{
    /**
     * Display the owner dashboard with document list and tracking
     */
    public function index(Request $request)
    {
        // Get paginated documents with latest status and apply search filter
        $perPage = $request->get('per_page', session('owner_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['owner_per_page' => $perPage]);

        $documents = $this->getDocumentsWithTracking($request, $perPage);

        // AJAX request: return JSON for live filtering (no page refresh)
        if ($request->ajax() || $request->wantsJson()) {
            $response = [
                'success' => true,
                'documents' => array_values($documents->items()),
                'pagination' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total(),
                    'from' => $documents->firstItem(),
                    'to' => $documents->lastItem(),
                ],
            ];

            // Include bagian statistics when a bagian filter is active
            if ($request->has('filter_bagian') && !empty($request->filter_bagian)) {
                $bagian = $request->filter_bagian;
                $bagianQuery = Dokumen::where('bagian', $bagian);

                $bagianTotal = (clone $bagianQuery)->count();

                $bagianSudahDibayar = (clone $bagianQuery)->where(function ($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                        ->orWhereNotNull('tanggal_dibayar');
                })->count();

                $bagianSiapDibayar = (clone $bagianQuery)->where('status_pembayaran', 'siap_dibayar')
                    ->whereNull('tanggal_dibayar')
                    ->count();

                $bagianBelumSiapBayar = $bagianTotal - $bagianSudahDibayar - $bagianSiapDibayar;

                $response['bagian_stats'] = [
                    'bagian_name' => $bagian,
                    'total' => $bagianTotal,
                    'belum_siap_bayar' => $bagianBelumSiapBayar,
                    'siap_dibayar' => $bagianSiapDibayar,
                    'sudah_dibayar' => $bagianSudahDibayar,
                ];
            }

            return response()->json($response);
        }

        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        // Calculate dashboard statistics
        $totalDokumen = Dokumen::count();

        // Dokumen Selesai: status completed, status_pembayaran = sudah_dibayar, OR tanggal_dibayar exists
        $dokumenSelesai = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar')
                ->orWhereNotNull('tanggal_dibayar');
        })->count();

        // Dokumen Belum Siap Bayar: belum selesai dan belum dibayar
        $dokumenBelumSiapBayar = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->whereNull('tanggal_dibayar');
        })->count();

        // Dokumen Siap Bayar: dokumen dengan status_pembayaran = siap_dibayar, tapi belum punya tanggal_dibayar
        $dokumenSiapBayar = Dokumen::where('status_pembayaran', 'siap_dibayar')
            ->whereNull('tanggal_dibayar')
            ->count();

        // Total Nilai (Rp)
        $totalNilai = Dokumen::sum('nilai_rupiah') ?? 0;

        // Nilai Rupiah per Status
        $nilaiBelumSiapBayar = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->whereNull('tanggal_dibayar');
        })->sum('nilai_rupiah') ?? 0;

        $nilaiSiapBayar = Dokumen::where('status_pembayaran', 'siap_dibayar')
            ->whereNull('tanggal_dibayar')
            ->sum('nilai_rupiah') ?? 0;

        $nilaiSelesai = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar')
                ->orWhereNotNull('tanggal_dibayar');
        })->sum('nilai_rupiah') ?? 0;

        // Calculate trend indicators (compare with last week)
        $oneWeekAgo = Carbon::now()->subWeek();

        $totalDokumenLastWeek = Dokumen::where('created_at', '<=', $oneWeekAgo)->count();
        $totalDokumenTrend = $totalDokumenLastWeek > 0
            ? round((($totalDokumen - $totalDokumenLastWeek) / $totalDokumenLastWeek) * 100, 1)
            : 0;

        $dokumenSelesaiLastWeek = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar')
                ->orWhereNotNull('tanggal_dibayar');
        })->where('updated_at', '<=', $oneWeekAgo)->count();
        $dokumenSelesaiTrend = $dokumenSelesaiLastWeek > 0
            ? round((($dokumenSelesai - $dokumenSelesaiLastWeek) / $dokumenSelesaiLastWeek) * 100, 1)
            : ($dokumenSelesai > 0 ? 100 : 0);

        $dokumenProsesLastWeek = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        })->where('created_at', '<=', $oneWeekAgo)->count();
        $dokumenProsesTrend = $dokumenProsesLastWeek > 0
            ? round((($dokumenBelumSiapBayar - $dokumenProsesLastWeek) / $dokumenProsesLastWeek) * 100, 1)
            : ($dokumenBelumSiapBayar > 0 ? 100 : 0);

        $totalNilaiLastWeek = Dokumen::where('created_at', '<=', $oneWeekAgo)->sum('nilai_rupiah') ?? 0;
        $totalNilaiTrend = $totalNilaiLastWeek > 0
            ? round((($totalNilai - $totalNilaiLastWeek) / $totalNilaiLastWeek) * 100, 1)
            : ($totalNilai > 0 ? 100 : 0);

        // Calculate all document ages (in days) for age filter cards
        $allDokumenUmur = Dokumen::select('created_at', 'tanggal_masuk', 'tanggal_dibayar', 'status_pembayaran', 'status')
            ->get()
            ->map(function ($dok) {
                $startTime = $dok->created_at ?? $dok->tanggal_masuk;
                if (!$startTime)
                    return 0;

                $startTime = Carbon::parse($startTime);
                $isPaid = !empty($dok->tanggal_dibayar) ||
                    $dok->status_pembayaran === 'sudah_dibayar' ||
                    $dok->status === 'completed';

                $endTime = ($isPaid && !empty($dok->tanggal_dibayar))
                    ? Carbon::parse($dok->tanggal_dibayar)
                    : Carbon::now();

                return (int) floor($startTime->diffInHours($endTime) / 24);
            })
            ->toArray();

        return view('owner.dashboard', compact(
            'documents',
            'totalDokumen',
            'dokumenBelumSiapBayar',
            'dokumenSiapBayar',
            'dokumenSelesai',
            'totalNilai',
            'nilaiBelumSiapBayar',
            'nilaiSiapBayar',
            'nilaiSelesai',
            'totalDokumenTrend',
            'dokumenSelesaiTrend',
            'dokumenProsesTrend',
            'totalNilaiTrend',
            'filterData',
            'allDokumenUmur'
        ))
            ->with('title', 'Dashboard Kabag Keuangan - Dokumen')
            ->with('module', 'owner')
            ->with('menuHome', '')
            ->with('menuDokumen', 'active')
            ->with('menuRekapan', '')
            ->with('menuRekapanKeterlambatan', '')
            ->with('menuDaftarDokumen', '')
            ->with('menuEditDokumen', '')
            ->with('menuRekapKeterlambatan', '')
            ->with('menuDaftarDokumenDikembalikan', '')
            ->with('menuPengembalianKeBidang', '')
            ->with('menuTambahDokumen', '')
            ->with('dashboardUrl', '/owner/home')
            ->with('dokumenUrl', '/owner/dokumen')
            ->with('pengembalianUrl', '#')
            ->with('tambahDokumenUrl', '#')
            ->with('search', $request->get('search', ''));
    }

    /**
     * AJAX endpoint: return filtered documents as JSON (no page refresh)
     */
    public function filterDocuments(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', session('owner_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['owner_per_page' => $perPage]);

        $documents = $this->getDocumentsWithTracking($request, $perPage);

        return response()->json([
            'success' => true,
            'documents' => array_values($documents->items()),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
        ]);
    }

    /**
     * Display the owner home page with bagian statistics cards
     */
    public function home()
    {
        // === GREETING DINAMIS ===
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default    => 'Selamat Malam',
        };

        // === RINGKASAN HARI INI ===
        $hariIni = [
            'masuk'     => Dokumen::whereDate('created_at', today())->count(),
            'mendekati' => Dokumen::whereBetween('created_at', [now()->subDays(3), now()->subDay()])
                              ->whereNull('tanggal_dibayar')
                              ->where(function($q) {
                                  $q->whereNull('status_pembayaran')
                                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                              })->count(),
            'terlambat' => Dokumen::where('created_at', '<', now()->subDays(3))
                              ->whereNull('tanggal_dibayar')
                              ->where('current_handler', '!=', 'operator')
                              ->where(function($q) {
                                  $q->whereNull('status_pembayaran')
                                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                              })->count(),
        ];

        // === STATISTIK UTAMA ===
        $totalDokumen = Dokumen::count();

        $dokumenSelesai = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar')
                ->orWhereNotNull('tanggal_dibayar');
        })->count();

        // Dokumen Belum Siap Bayar (sama dengan query di index())
        $dokumenProses = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->whereNull('tanggal_dibayar');
        })->count();

        $dokumenSiapBayar = Dokumen::where('status_pembayaran', 'siap_dibayar')
            ->whereNull('tanggal_dibayar')
            ->count();

        $totalNilai = Dokumen::sum('nilai_rupiah') ?? 0;

        // === NILAI RUPIAH PER STATUS ===
        $nilaiBelumDibayar = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->whereNull('tanggal_dibayar');
        })->sum('nilai_rupiah') ?? 0;

        $nilaiSiapDibayar = Dokumen::where('status_pembayaran', 'siap_dibayar')
            ->whereNull('tanggal_dibayar')
            ->sum('nilai_rupiah') ?? 0;

        $nilaiSudahDibayar = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar')
                ->orWhereNotNull('tanggal_dibayar');
        })->sum('nilai_rupiah') ?? 0;

        // === PERSENTASE PERUBAHAN VS BULAN LALU ===
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $bulanLaluDate = now()->subMonth();

        $totalBulanIni  = Dokumen::whereMonth('created_at', $bulanIni)->whereYear('created_at', $tahunIni)->count();
        $totalBulanLalu = Dokumen::whereMonth('created_at', $bulanLaluDate->month)->whereYear('created_at', $bulanLaluDate->year)->count();
        $totalDokumenTrend = $totalBulanLalu > 0 ? round((($totalBulanIni - $totalBulanLalu) / $totalBulanLalu) * 100) : 0;

        $prosesBulanIni  = Dokumen::whereMonth('created_at', $bulanIni)->whereYear('created_at', $tahunIni)
            ->where(function($q) { $q->whereNull('status_pembayaran')->orWhere('status_pembayaran', '!=', 'sudah_dibayar'); })
            ->whereNull('tanggal_dibayar')->count();
        $prosesBulanLalu = Dokumen::whereMonth('created_at', $bulanLaluDate->month)->whereYear('created_at', $bulanLaluDate->year)
            ->where(function($q) { $q->whereNull('status_pembayaran')->orWhere('status_pembayaran', '!=', 'sudah_dibayar'); })
            ->whereNull('tanggal_dibayar')->count();
        $prosesTrend = $prosesBulanLalu > 0 ? round((($prosesBulanIni - $prosesBulanLalu) / $prosesBulanLalu) * 100) : 0;

        $siapBulanIni  = Dokumen::whereMonth('created_at', $bulanIni)->whereYear('created_at', $tahunIni)->where('status_pembayaran', 'siap_dibayar')->whereNull('tanggal_dibayar')->count();
        $siapBulanLalu = Dokumen::whereMonth('created_at', $bulanLaluDate->month)->whereYear('created_at', $bulanLaluDate->year)->where('status_pembayaran', 'siap_dibayar')->whereNull('tanggal_dibayar')->count();
        $siapTrend = $siapBulanLalu > 0 ? round((($siapBulanIni - $siapBulanLalu) / $siapBulanLalu) * 100) : 0;

        $selesaiBulanIni  = Dokumen::whereMonth('updated_at', $bulanIni)->whereYear('updated_at', $tahunIni)
            ->where(function($q) { $q->where('status_pembayaran', 'sudah_dibayar')->orWhereNotNull('tanggal_dibayar'); })->count();
        $selesaiBulanLalu = Dokumen::whereMonth('updated_at', $bulanLaluDate->month)->whereYear('updated_at', $bulanLaluDate->year)
            ->where(function($q) { $q->where('status_pembayaran', 'sudah_dibayar')->orWhereNotNull('tanggal_dibayar'); })->count();
        $selesaiTrend = $selesaiBulanLalu > 0 ? round((($selesaiBulanIni - $selesaiBulanLalu) / $selesaiBulanLalu) * 100) : 0;

        $nilaiBulanIni  = Dokumen::whereMonth('created_at', $bulanIni)->whereYear('created_at', $tahunIni)->sum('nilai_rupiah') ?? 0;
        $nilaiBulanLalu = Dokumen::whereMonth('created_at', $bulanLaluDate->month)->whereYear('created_at', $bulanLaluDate->year)->sum('nilai_rupiah') ?? 0;
        $nilaiTrend = $nilaiBulanLalu > 0 ? round((($nilaiBulanIni - $nilaiBulanLalu) / $nilaiBulanLalu) * 100) : 0;

        // === TREN 30 HARI (Area Chart) ===
        $trend = Dokumen::selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('tgl')->orderBy('tgl')->get();
        $chartLabels = $trend->pluck('tgl')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray();
        $chartData   = $trend->pluck('total')->toArray();

        // === BAGIAN STATS (Bar Chart + Progress) ===
        $bagianStats = $this->getBagianStatistics();
        $maxBagian = collect($bagianStats)->max('count') ?: 1;

        return view('owner.home', compact(
            'greeting',
            'hariIni',
            'bagianStats',
            'maxBagian',
            'totalDokumen',
            'dokumenProses',
            'dokumenSiapBayar',
            'dokumenSelesai',
            'totalNilai',
            'nilaiBelumDibayar',
            'nilaiSiapDibayar',
            'nilaiSudahDibayar',
            'totalDokumenTrend',
            'prosesTrend',
            'siapTrend',
            'selesaiTrend',
            'nilaiTrend',
            'chartLabels',
            'chartData'
        ))
            ->with('title', 'Dashboard Kabag Keuangan - Home')
            ->with('module', 'owner')
            ->with('menuHome', 'active')
            ->with('menuDokumen', '')
            ->with('menuRekapanKeterlambatan', '');
    }

    /**
     * Get document statistics per bagian (with terlambat count)
     */
    private function getBagianStatistics(): array
    {
        $bagianList = [
            'AKN' => ['icon' => 'fa-calculator', 'emoji' => '🧮', 'color' => '#7C3AED'],
            'DPM' => ['icon' => 'fa-chart-line', 'emoji' => '📈', 'color' => '#22c55e'],
            'KPL' => ['icon' => 'fa-crown', 'emoji' => '👑', 'color' => '#f59e0b'],
            'PMO' => ['icon' => 'fa-tasks', 'emoji' => '📋', 'color' => '#06b6d4'],
            'SDM' => ['icon' => 'fa-users', 'emoji' => '👥', 'color' => '#8b5cf6'],
            'SKH' => ['icon' => 'fa-building', 'emoji' => '🏢', 'color' => '#ec4899'],
            'TAN' => ['icon' => 'fa-seedling', 'emoji' => '🌿', 'color' => '#10b981'],
            'TEP' => ['icon' => 'fa-cogs', 'emoji' => '⚙️', 'color' => '#6366f1'],
            'PTI' => ['icon' => 'fa-laptop-code', 'emoji' => '🖥', 'color' => '#3B82F6'],
        ];

        $stats = [];
        foreach ($bagianList as $code => $info) {
            $baseQuery = Dokumen::where('bagian', $code);
            $count = (clone $baseQuery)->count();

            // Terlambat: dokumen > 3 hari dan belum selesai
            $terlambat = (clone $baseQuery)
                ->where('created_at', '<', now()->subDays(3))
                ->whereNull('tanggal_dibayar')
                ->where('current_handler', '!=', 'operator')
                ->where(function($q) {
                    $q->whereNull('status_pembayaran')
                      ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })->count();

            $stats[] = [
                'code'      => $code,
                'icon'      => $info['icon'],
                'emoji'     => $info['emoji'],
                'color'     => $info['color'],
                'count'     => $count,
                'terlambat' => $terlambat,
            ];
        }

        // Sort by count descending
        usort($stats, fn($a, $b) => $b['count'] - $a['count']);

        return $stats;
    }

    /**
     * Get API endpoint for document timeline
     */
    public function getDocumentTimeline($id): JsonResponse
    {
        $dokumen = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
            ->findOrFail($id);

        $timeline = $this->generateDocumentTimeline($dokumen);

        return response()->json([
            'success' => true,
            'dokumen' => [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'status' => $dokumen->status,
                'current_handler' => $dokumen->current_handler,
                'created_at' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '-',
                'progress_percentage' => $this->calculateProgress($dokumen),
            ],
            'timeline' => $timeline
        ]);
    }

    /**
     * Get documents with their latest tracking status
     */
    private function getDocumentsWithTracking(Request $request = null, $perPage = 10)
    {
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData', 'roleStatuses']);

        // Apply status filter if provided
        if ($request && $request->has('status') && !empty($request->status)) {
            $status = $request->status;

            // Semua Dokumen: no status filter, show everything
            if ($status === 'semua') {
                // Don't apply any status filter - show all documents
            }
            // Belum Siap Dibayar: dokumen dari operator sampai akutansi (belum di pembayaran)
            elseif ($status === 'belum_siap' || $status === '') {
                $query->where(function ($q) {
                    $q->whereIn('current_handler', ['operator', 'team_verifikasi', 'perpajakan', 'akutansi'])
                        ->orWhereIn('status', [
                            'draft',
                            'sedang diproses',
                            'menunggu_verifikasi',
                            'pending_approval_team_verifikasi',
                            'sent_to_team_verifikasi',
                            'sent_to_perpajakan',
                            'sent_to_akutansi',
                            'pending_approval_perpajakan',
                            'pending_approval_akutansi',
                        ]);
                })
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    })
                    ->where(function ($subQ) {
                        $subQ->where('current_handler', '!=', 'pembayaran')
                            ->orWhereNull('current_handler');
                    });
            }
            // Siap Dibayar: dokumen yang sudah di role pembayaran tapi belum dibayar
            elseif ($status === 'siap_dibayar') {
                $query->where(function ($q) {
                    $q->where('current_handler', 'pembayaran')
                        ->orWhere('status', 'sent_to_pembayaran')
                        ->orWhere('status_pembayaran', 'siap_dibayar')
                        ->orWhere('status_pembayaran', 'siap_bayar');
                })
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    })
                    ->whereNull('tanggal_dibayar');
            }
            // Sudah Dibayar: dokumen yang sudah dibayar
            elseif ($status === 'sudah_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                        ->orWhereNotNull('tanggal_dibayar')
                        ->orWhereNotNull('link_bukti_pembayaran');
                });
            }
        } else {
            // Default: show Belum Siap Dibayar (operator to akutansi)
            // BUT skip this filter if user is searching, so search can find all documents
            $hasSearch = $request && $request->has('search') && !empty($request->search) && trim((string) $request->search) !== '';

            if (!$hasSearch) {
                $query->where(function ($q) {
                    $q->whereIn('current_handler', ['operator', 'team_verifikasi', 'perpajakan', 'akutansi'])
                        ->orWhereIn('status', [
                            'draft',
                            'sedang diproses',
                            'menunggu_verifikasi',
                            'pending_approval_team_verifikasi',
                            'sent_to_team_verifikasi',
                            'sent_to_perpajakan',
                            'sent_to_akutansi',
                            'pending_approval_perpajakan',
                            'pending_approval_akutansi',
                        ]);
                })
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    })
                    ->where(function ($subQ) {
                        $subQ->where('current_handler', '!=', 'pembayaran')
                            ->orWhereNull('current_handler');
                    });
            }
        }

        // Apply advanced filters
        // Filter by bagian
        if ($request && $request->has('filter_bagian') && !empty($request->filter_bagian)) {
            $query->where('bagian', $request->filter_bagian);
        }

        // Filter by umur dokumen (terlambat) — exclude Operator role
        if ($request && $request->has('filter_umur') && !empty($request->filter_umur)) {
            $days = (int) $request->filter_umur;
            $query->where('created_at', '<', now()->subDays($days))
                  ->where('current_handler', '!=', 'operator')
                  ->whereNull('tanggal_dibayar')
                  ->where(function ($q) {
                      $q->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                  });
        }

        // Filter by vendor/dibayar_kepada
        if ($request && $request->has('filter_vendor') && !empty($request->filter_vendor)) {
            $query->where(function ($q) use ($request) {
                $q->where('dibayar_kepada', $request->filter_vendor)
                    ->orWhereHas('dibayarKepadas', function ($subQ) use ($request) {
                        $subQ->where('nama_penerima', $request->filter_vendor);
                    });
            });
        }

        // Filter by kriteria CF (by ID or name)
        if ($request && $request->has('filter_kriteria_cf') && !empty($request->filter_kriteria_cf)) {
            try {
                $kriteriaCf = \App\Models\KategoriKriteria::on('cash_bank')
                    ->where('id_kategori_kriteria', $request->filter_kriteria_cf)
                    ->orWhere('nama_kriteria', $request->filter_kriteria_cf)
                    ->first();
                if ($kriteriaCf) {
                    $query->where('kategori', $kriteriaCf->nama_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by kriteria CF: ' . $e->getMessage());
            }
        }

        // Filter by sub kriteria (by ID or name)
        if ($request && $request->has('filter_sub_kriteria') && !empty($request->filter_sub_kriteria)) {
            try {
                $subKriteria = \App\Models\SubKriteria::on('cash_bank')
                    ->where('id_sub_kriteria', $request->filter_sub_kriteria)
                    ->orWhere('nama_sub_kriteria', $request->filter_sub_kriteria)
                    ->first();
                if ($subKriteria) {
                    $query->where('jenis_dokumen', $subKriteria->nama_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by sub kriteria: ' . $e->getMessage());
            }
        }

        // Filter by item sub kriteria (by ID or name)
        if ($request && $request->has('filter_item_sub_kriteria') && !empty($request->filter_item_sub_kriteria)) {
            try {
                $itemSubKriteria = \App\Models\ItemSubKriteria::on('cash_bank')
                    ->where('id_item_sub_kriteria', $request->filter_item_sub_kriteria)
                    ->orWhere('nama_item_sub_kriteria', $request->filter_item_sub_kriteria)
                    ->first();
                if ($itemSubKriteria) {
                    $query->where('jenis_sub_pekerjaan', $itemSubKriteria->nama_item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by item sub kriteria: ' . $e->getMessage());
            }
        }

        // Filter by kebun
        if ($request && $request->has('filter_kebun') && !empty($request->filter_kebun)) {
            $query->where('kebun', $request->filter_kebun);
        }

        // Filter by status pembayaran
        if ($request && $request->has('filter_status_pembayaran') && !empty($request->filter_status_pembayaran)) {
            $statusPembayaran = $request->filter_status_pembayaran;
            if ($statusPembayaran === 'belum_dibayar') {
                $query->where(function ($q) {
                    $q->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar')
                        ->orWhere('status_pembayaran', 'pending');
                })
                    ->whereNull('tanggal_dibayar')
                    ->whereNull('link_bukti_pembayaran');
            } elseif ($statusPembayaran === 'siap_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'siap_dibayar')
                        ->orWhere('status_pembayaran', 'siap_bayar')
                        ->orWhere(function ($subQ) {
                            $subQ->where('current_handler', 'pembayaran')
                                ->where('status', 'sent_to_pembayaran')
                                ->whereNull('tanggal_dibayar')
                                ->whereNull('link_bukti_pembayaran');
                        });
                });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                // Dokumen sudah dibayar - hanya cek tanggal_dibayar
                $query->whereNotNull('tanggal_dibayar');
            }
        }

        // Apply search filter if provided
        if ($request && $request->has('search') && !empty($request->search) && trim((string) $request->search) !== '') {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                // Text fields
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                    ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                    ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                    ->orWhere('nama_pengirim', 'like', '%' . $search . '%')
                    ->orWhere('bagian', 'like', '%' . $search . '%')
                    ->orWhere('kategori', 'like', '%' . $search . '%')
                    ->orWhere('jenis_dokumen', 'like', '%' . $search . '%')
                    ->orWhere('no_berita_acara', 'like', '%' . $search . '%')
                    ->orWhere('no_spk', 'like', '%' . $search . '%')
                    ->orWhere('nomor_miro', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%')
                    ->orWhere('dibayar_kepada', 'like', '%' . $search . '%')
                    ->orWhere('npwp', 'like', '%' . $search . '%')
                    ->orWhere('no_faktur', 'like', '%' . $search . '%')
                    ->orWhere('jenis_pph', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('current_handler', 'like', '%' . $search . '%');

                // Search in nilai_rupiah - handle various formats
                $numericSearch = preg_replace('/[^0-9]/', '', $search);
                if (is_numeric($numericSearch) && $numericSearch > 0) {
                    $q->orWhereRaw('CAST(nilai_rupiah AS CHAR) LIKE ?', ['%' . $numericSearch . '%']);
                }

                // Search in related tables
                $q->orWhereHas('dibayarKepadas', function ($subQ) use ($search) {
                    $subQ->where('nama_penerima', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('dokumenPos', function ($subQ) use ($search) {
                        $subQ->where('nomor_po', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('dokumenPrs', function ($subQ) use ($search) {
                        $subQ->where('nomor_pr', 'like', '%' . $search . '%');
                    });
            });
        }

        // Paginate the query
        $paginatedDocuments = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString(); // Preserve query parameters (search, status, etc.)

        // Map the paginated collection
        $paginatedDocuments->getCollection()->transform(function ($dokumen) {
            return [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'status' => $dokumen->status,
                'status_display' => $this->getStatusDisplayName($dokumen->status),
                'current_handler' => $dokumen->current_handler,
                'current_handler_display' => $this->getRoleDisplayName($dokumen->current_handler),
                'created_at' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '-',
                'tanggal_masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d M Y') : ($dokumen->created_at ? $dokumen->created_at->format('d M Y') : '-'),
                'progress_percentage' => $this->calculateProgress($dokumen),
                'status_badge_color' => $this->getStatusBadgeColor($dokumen->status),
                'progress_color' => $this->getProgressColor($dokumen->status),
                'is_overdue' => $this->isDocumentOverdue($dokumen),
                'is_paid' => $dokumen->status_pembayaran === 'sudah_dibayar' || $dokumen->status === 'completed' || !empty($dokumen->tanggal_dibayar),
                'tanggal_dibayar' => $dokumen->tanggal_dibayar,
                'deadline_info' => $this->getDeadlineInfo($dokumen),
                'step_deadline_levels' => $this->getStepDeadlineLevels($dokumen),
                'workflow_timeline' => $this->getWorkflowTimeline($dokumen),
                'umur_dokumen' => $this->calculateDocumentAge($dokumen),
                'kebun' => $dokumen->kebun ?? '-',
                'vendor' => $dokumen->dibayar_kepada ?? ($dokumen->dibayarKepadas->first()->nama_penerima ?? '-'),
                // Urgency alert fields
                'urgency_active'       => (bool) ($dokumen->urgency_active ?? false),
                'urgency_sent_at_human'=> $dokumen->urgency_sent_at ? $dokumen->urgency_sent_at->diffForHumans() : null,
                'urgency_sent_at'      => $dokumen->urgency_sent_at ? $dokumen->urgency_sent_at->toISOString() : null,
            ];
        });

        return $paginatedDocuments;
    }

    /**
     * Generate timeline events for a document
     */
    private function generateDocumentTimeline($dokumen)
    {
        $events = [];
        $previousTime = null;

        // Event 1: Dokumen DOperatort
        if ($dokumen->created_at) {
            $events[] = [
                'type' => 'created',
                'icon' => '✅',
                'title' => 'Dokumen DOperatort',
                'timestamp' => $dokumen->created_at->format('d M Y H:i'),
                'info' => [
                    'DOperatort oleh' => $this->getRoleDisplayName($dokumen->created_by),
                    'Nomor Agenda' => $dokumen->nomor_agenda,
                    'Nomor SPP' => $dokumen->nomor_spp,
                    'Nilai' => 'Rp. ' . number_format($dokumen->nilai_rupiah, 0, ',', '.'),
                    'Uraian' => $dokumen->uraian_spp,
                ]
            ];
            $previousTime = $dokumen->created_at;
        }

        // Event 2: Dikirim ke Ibu Yuni
        $teamVerifikasiData = $dokumen->getDataForRole('team_verifikasi');
        if ($teamVerifikasiData && $teamVerifikasiData->received_at) {
            $receivedAt = $teamVerifikasiData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_team_verifikasi',
                'icon' => '🚀',
                'title' => 'Dikirim ke Ibu Yuni',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => 'Operator',
                    'Penerima' => 'Ibu Yuni',
                    'Durasi dari dOperatort' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 3: Deadline Ditetapkan
        if ($dokumen->deadline_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->deadline_at) : null;
            $events[] = [
                'type' => 'deadline_set',
                'icon' => '⏰',
                'title' => 'Deadline Ditetapkan',
                'timestamp' => $dokumen->deadline_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Durasi deadline' => $dokumen->deadline_days . ' hari',
                    'Catatan' => $dokumen->deadline_note,
                ]
            ];
        }

        // Event 4: Diproses Ibu Yuni
        if ($dokumen->processed_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->processed_at) : null;
            $events[] = [
                'type' => 'processed_Team Verifikasi',
                'icon' => '⚡',
                'title' => 'Diproses Ibu Yuni',
                'timestamp' => $dokumen->processed_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Handler' => 'Ibu Yuni',
                    'Durasi proses' => $duration,
                ]
            ];
            $previousTime = $dokumen->processed_at;
        }

        // Event 5: Dikirim ke Perpajakan
        $perpajakanData = $dokumen->getDataForRole('perpajakan');
        if ($perpajakanData && $perpajakanData->received_at) {
            $receivedAt = $perpajakanData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_perpajakan',
                'icon' => '🚀',
                'title' => 'Dikirim ke Team Perpajakan',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => $this->getRoleDisplayName($dokumen->current_handler),
                    'Penerima' => 'Team Perpajakan',
                    'Durasi dari step sebelumnya' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 6: Diproses Perpajakan
        if ($dokumen->processed_perpajakan_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->processed_perpajakan_at) : null;
            $events[] = [
                'type' => 'processed_perpajakan',
                'icon' => '⚡',
                'title' => 'Diproses Team Perpajakan',
                'timestamp' => $dokumen->processed_perpajakan_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Handler' => 'Team Perpajakan',
                    'Status Team Perpajakan' => $dokumen->status_perpajakan,
                    'Durasi proses' => $duration,
                ]
            ];
            $previousTime = $dokumen->processed_perpajakan_at;
        }

        // Event 7: Dikirim ke Akutansi
        $akutansiData = $dokumen->getDataForRole('akutansi');
        if ($akutansiData && $akutansiData->received_at) {
            $receivedAt = $akutansiData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_akutansi',
                'icon' => '🚀',
                'title' => 'Dikirim ke Team Akutansi',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => 'Team Perpajakan',
                    'Penerima' => 'Team Akutansi',
                    'Durasi dari step sebelumnya' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 8: Dikembalikan
        if ($dokumen->returned_to_Operator_at || $dokumen->returned_at) {
            $returnTime = $dokumen->returned_to_Operator_at ?? $dokumen->returned_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $returnTime) : null;

            $events[] = [
                'type' => 'returned',
                'icon' => '↩️',
                'title' => 'Dikembalikan',
                'timestamp' => $returnTime->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Dikembalikan oleh' => $this->getRoleDisplayName($dokumen->current_handler),
                    'Dikembalikan ke' => $this->getReturnDestination($dokumen),
                    'Alasan' => $dokumen->return_reason,
                ]
            ];
            $previousTime = $returnTime;
        }

        // Event 9: Deadline Selesai (jika ada)
        if ($dokumen->deadline_completed_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->deadline_completed_at) : null;
            $events[] = [
                'type' => 'deadline_completed',
                'icon' => '✅',
                'title' => 'Deadline Selesai',
                'timestamp' => $dokumen->deadline_completed_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Status' => 'Deadline terpenuhi',
                    'Durasi' => $duration,
                ]
            ];
        }

        // Event 10: Dokumen Selesai
        if (in_array($dokumen->status, ['approved_data_sudah_terkirim', 'selesai'])) {
            $completedTime = $dokumen->updated_at;
            $totalDuration = $dokumen->created_at ? $this->calculateDuration($dokumen->created_at, $completedTime) : null;

            $events[] = [
                'type' => 'completed',
                'icon' => '🎉',
                'title' => 'Dokumen Selesai',
                'timestamp' => $completedTime->format('d M Y H:i'),
                'total_duration' => $totalDuration,
                'info' => [
                    'Status Akhir' => $dokumen->status === 'approved_data_sudah_terkirim' ? 'Approved Data Sudah Terkirim' : 'Selesai',
                    'Total waktu proses' => $totalDuration,
                    'Diselesaikan oleh' => $this->getRoleDisplayName($dokumen->current_handler),
                ]
            ];
        }

        return $events;
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $now = Carbon::now();

        return [
            'total_documents' => Dokumen::count(),
            'active_processing' => Dokumen::whereNotIn('status', ['approved_data_sudah_terkirim', 'rejected_data_tidak_lengkap'])->count(),
            'completed_today' => Dokumen::where('status', 'approved_data_sudah_terkirim')
                ->whereDate('updated_at', $now->toDateString())
                ->count(),
            'overdue_documents' => Dokumen::whereNotNull('deadline_at')
                ->where('deadline_at', '<', $now)
                ->whereNotIn('status', [
                    'approved_data_sudah_terkirim',
                    'rejected_data_tidak_lengkap',
                    'selesai',
                    'completed',
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                ])
                ->count(),
            'avg_processing_time' => $this->calculateAverageProcessingTime(),
            'fastest_department' => $this->getFastestDepartment(),
            'slowest_department' => $this->getSlowestDepartment(),
        ];
    }

    /**
     * Calculate progress percentage based on status
     */
    private function calculateProgress($dokumen)
    {
        // Calculate progress based on current_handler and status
        $handler = $dokumen->current_handler ?? 'operator';
        $status = $dokumen->status ?? 'draft';

        // Get status_pembayaran from model or database
        $statusPembayaran = null;
        if (isset($dokumen->status_pembayaran)) {
            $statusPembayaran = $dokumen->status_pembayaran;
        } elseif (isset($dokumen->id)) {
            // Try to get from database directly only if id is available
            $statusPembayaran = \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
        }

        // If document is completed or payment is completed
        if (in_array($status, ['selesai', 'approved_data_sudah_terkirim', 'completed']) || $statusPembayaran === 'sudah_dibayar') {
            return 100;
        }

        // Normalize handler name to canonical form
        $handler = $this->normalizeHandlerName($handler);

        // Calculate progress based on handler position in workflow
        $handlerProgress = [
            'operator' => 20,          // Ibu Tarapul (step 1)
            'team_verifikasi' => 40,          // Team Verifikasi (step 2)
            'perpajakan' => 60,    // Team Perpajakan (step 3)
            'akutansi' => 80,      // Team Akutansi (step 4)
            'pembayaran' => 100,   // Pembayaran (step 5)
        ];

        $baseProgress = $handlerProgress[$handler] ?? 20;

        // Adjust based on status within handler
        if ($status == 'draft' && $handler == 'operator') {
            return 0;
        }

        if ($status == 'sedang diproses') {
            // Don't add bonus when processing to prevent step jumping
            // 40 + 10 = 50 would make step 3 active instead of step 2
            return $baseProgress;
        }

        if (strpos($status, 'sent_to_') === 0) {
            // Document sent to next handler - use base progress
            return $baseProgress;
        }

        if (strpos($status, 'pending_approval') === 0) {
            // Pending approval - slightly less than base
            return max($baseProgress - 5, 0);
        }

        if (strpos($status, 'returned') === 0) {
            // Returned - go back to previous handler progress
            return max($baseProgress - 20, 0);
        }

        // Default: use base progress
        return $baseProgress;
    }

    /**
     * Normalize handler name to canonical form
     * Handles various handler name variants stored in database
     */
    private function normalizeHandlerName($handler)
    {
        // Convert to lowercase for consistent comparison
        $handlerLower = strtolower($handler ?? '');

        // Map all possible handler name variants to canonical names
        $handlerAliases = [
            // Operator variants
            'operator' => 'operator',
            'ibu_a' => 'operator',
            'operator' => 'operator',
            'ibu_tarapul' => 'operator',
            'Operator' => 'operator',
            'tarapul' => 'operator',

            // Team Verifikasi variants (Team Verifikasi)
            'team_verifikasi' => 'team_verifikasi',
            'ibu_b' => 'team_verifikasi',
            'ibuyuni' => 'team_verifikasi',
            'ibu_yuni' => 'team_verifikasi',
            'ibu yuni' => 'team_verifikasi',
            'team_verifikasi' => 'team_verifikasi',
            'team_verifikasi' => 'team_verifikasi',
            'team verifikasi' => 'team_verifikasi',
            'teamverifikasi' => 'team_verifikasi',

            // Perpajakan variants
            'perpajakan' => 'perpajakan',
            'team_perpajakan' => 'perpajakan',
            'team perpajakan' => 'perpajakan',
            'teamperpajakan' => 'perpajakan',
            'pajak' => 'perpajakan',

            // Akutansi variants
            'akutansi' => 'akutansi',
            'team_akutansi' => 'akutansi',
            'team akutansi' => 'akutansi',
            'teamakutansi' => 'akutansi',
            'akuntansi' => 'akutansi',

            // Pembayaran variants
            'pembayaran' => 'pembayaran',
            'team_pembayaran' => 'pembayaran',
            'team pembayaran' => 'pembayaran',
            'teampembayaran' => 'pembayaran',
            'payment' => 'pembayaran',
        ];

        return $handlerAliases[$handlerLower] ?? 'operator';
    }

    /**
     * Get status badge color
     */
    private function getStatusBadgeColor($status)
    {
        $colorMap = [
            'draft' => '#6c757d', // Gray
            'sedang diproses' => '#083E40', // Green (theme)
            'menunggu_verifikasi' => '#083E40', // Green (theme)
            'pending_approval_team_verifikasi' => '#ffc107', // Yellow
            'sent_to_team_verifikasi' => '#0a4f52', // Dark green
            'proses_Team Verifikasi' => '#ffc107', // Yellow
            'sent_to_perpajakan' => '#0a4f52', // Dark green
            'proses_perpajakan' => '#0a4f52', // Dark green
            'sent_to_akutansi' => '#6f42c1', // Purple
            'proses_akutansi' => '#6f42c1', // Purple
            'menunggu_approved_pengiriman' => '#fd7e14', // Orange
            'proses_pembayaran' => '#6f42c1', // Purple
            'approved_data_sudah_terkirim' => '#889717', // Green (theme)
            'rejected_data_tidak_lengkap' => '#dc3545', // Red
            'selesai' => '#889717', // Green (theme)
        ];

        return $colorMap[$status] ?? '#6c757d';
    }

    /**
     * Get progress bar color
     */
    private function getProgressColor($status)
    {
        $progress = $this->calculateProgress((object) ['status' => $status]);

        if ($progress <= 30)
            return '#dc3545'; // Red
        if ($progress <= 60)
            return '#ffc107'; // Yellow
        if ($progress <= 90)
            return '#083E40'; // Green (theme)
        return '#889717'; // Green (theme)
    }

    /**
     * Check if document is overdue
     */
    private function isDocumentOverdue($dokumen)
    {
        if (!$dokumen->deadline_at)
            return false;

        // Dokumen yang sudah terkirim ke perpajakan/akutansi/pembayaran tidak dianggap terlambat
        // karena deadline Ibu Yuni sudah tidak berlaku lagi setelah dokumen terkirim
        $excludedStatuses = [
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'selesai',
            'completed',
            'sent_to_perpajakan',
            'sent_to_akutansi',
            'sent_to_pembayaran',
            'pending_approval_perpajakan',
            'pending_approval_akutansi',
            'pending_approval_pembayaran',
        ];

        return Carbon::now()->greaterThan($dokumen->deadline_at) &&
            !in_array($dokumen->status, $excludedStatuses);
    }

    /**
     * Calculate document age (umur dokumen)
     * From document creation/import until payment declares it paid
     * Returns formatted string like "X hari Y jam" or "X jam" if less than a day
     */
    private function calculateDocumentAge($dokumen)
    {
        // Start time: when document was created or imported
        $startTime = $dokumen->created_at ?? $dokumen->tanggal_masuk;
        if (!$startTime) {
            return null;
        }

        $startTime = Carbon::parse($startTime);

        // End time: payment date if paid, otherwise current time
        $isPaid = !empty($dokumen->tanggal_dibayar) ||
            $dokumen->status_pembayaran === 'sudah_dibayar' ||
            $dokumen->status === 'completed';

        if ($isPaid && !empty($dokumen->tanggal_dibayar)) {
            $endTime = Carbon::parse($dokumen->tanggal_dibayar);
        } else {
            $endTime = Carbon::now();
        }

        // Calculate total difference in hours first, then derive days
        $totalHours = (int) $startTime->diffInHours($endTime);
        $diffDays = (int) floor($totalHours / 24);
        $diffHours = $totalHours % 24;

        // Format output
        if ($diffDays > 0) {
            $text = $diffDays . ' hari';
            if ($diffHours > 0) {
                $text .= ' ' . $diffHours . ' jam';
            }
        } else {
            $text = $diffHours . ' jam';
            if ($diffHours == 0) {
                // Less than 1 hour, show minutes
                $diffMinutes = (int) $startTime->diffInMinutes($endTime);
                $text = $diffMinutes . ' menit';
            }
        }

        return [
            'text' => $text,
            'days' => $diffDays,
            'hours' => $diffHours,
            'is_paid' => $isPaid,
            'start_date' => $startTime->format('d M Y'),
            'end_date' => $endTime->format('d M Y'),
        ];
    }

    /**
     * Get deadline information based on elapsed time since document approval
     * Color coding:
     * - Green (aman): < 1 day elapsed
     * - Yellow (warning): 1-2 days elapsed  
     * - Red (danger): >= 2 days elapsed
     */
    private function getDeadlineInfo($dokumen)
    {
        // Get the approval timestamp - when the document started being processed
        // This is when the "timer" begins
        $startTime = null;

        // Priority: processed_at (when Team Verifikasi approved) -> received_at from roleData -> deadline_at
        if ($dokumen->processed_at) {
            $startTime = Carbon::parse($dokumen->processed_at);
        } else {
            // Try to get from roleData - when received by Team Verifikasi (approval from inbox)
            $teamVerifikasiData = null;
            if (method_exists($dokumen, 'getDataForRole')) {
                $teamVerifikasiData = $dokumen->getDataForRole('team_verifikasi');
            } elseif (isset($dokumen->roleData)) {
                $teamVerifikasiData = $dokumen->roleData->where('role_code', 'team_verifikasi')->first();
            }

            if ($teamVerifikasiData && isset($teamVerifikasiData->processed_at) && $teamVerifikasiData->processed_at) {
                $startTime = Carbon::parse($teamVerifikasiData->processed_at);
            } elseif ($teamVerifikasiData && isset($teamVerifikasiData->received_at) && $teamVerifikasiData->received_at) {
                $startTime = Carbon::parse($teamVerifikasiData->received_at);
            }
        }

        // If no approval time found, check deadline_at as fallback for display
        if (!$startTime) {
            if (!$dokumen->deadline_at) {
                return null;
            }
            // Show deadline date if no start time
            return [
                'date' => Carbon::parse($dokumen->deadline_at)->format('d M Y, H:i'),
                'text' => 'Belum diproses',
                'elapsed' => '',
                'class' => 'safe',
                'color' => 'success'
            ];
        }

        // For paid documents, use tanggal_dibayar as end time (freeze the timer)
        // Otherwise use current time
        $endTime = Carbon::now();
        $isPaid = !empty($dokumen->tanggal_dibayar) || $dokumen->status_pembayaran === 'sudah_dibayar' || $dokumen->status === 'completed';

        if ($isPaid && !empty($dokumen->tanggal_dibayar)) {
            $endTime = Carbon::parse($dokumen->tanggal_dibayar);
        }

        // Calculate elapsed time since approval
        $diff = $startTime->diff($endTime);
        $totalHours = ($diff->days * 24) + $diff->h;
        $totalMinutes = $totalHours * 60 + $diff->i;

        // Format elapsed time as "X hari Y jam Z menit"
        $elapsedParts = [];
        if ($diff->days > 0) {
            $elapsedParts[] = $diff->days . ' hari';
        }
        if ($diff->h > 0) {
            $elapsedParts[] = $diff->h . ' jam';
        }
        if ($diff->i > 0 || empty($elapsedParts)) {
            $elapsedParts[] = $diff->i . ' menit';
        }
        $elapsedText = implode(' ', $elapsedParts);

        // Determine color based on elapsed time (in hours)
        // < 24 hours = green (aman)
        // 24-72 hours = yellow (warning)
        // >= 72 hours = red (danger)
        $class = 'safe';
        $color = 'success';
        $statusText = 'AMAN';

        if ($totalHours >= 72) {
            $class = 'danger';
            $color = 'danger';
            $statusText = 'TERLAMBAT';
        } elseif ($totalHours >= 24) {
            $class = 'warning';
            $color = 'warning';
            $statusText = 'PERINGATAN';
        }

        return [
            'date' => $startTime->format('d M Y, H:i'),
            'text' => $statusText,
            'elapsed' => $elapsedText,
            'class' => $class,
            'color' => $color
        ];
    }

    /**
     * Get per-step deadline levels for the stepper component
     * Returns array indexed by step number (1-5) with deadline level for each
     */
    private function getStepDeadlineLevels($dokumen)
    {
        $levels = [1 => 'aman']; // Step 1 (Operator) is always aman
        $roles = [
            2 => 'team_verifikasi',
            3 => 'perpajakan',
            4 => 'akutansi',
            5 => 'pembayaran',
        ];

        foreach ($roles as $step => $roleCode) {
            $roleData = null;
            if (method_exists($dokumen, 'getDataForRole')) {
                $roleData = $dokumen->getDataForRole($roleCode);
            }

            if ($roleData && $roleData->received_at) {
                $endTime = $roleData->processed_at ?? now();
                $hoursElapsed = $roleData->received_at->diffInHours($endTime);

                if ($hoursElapsed >= 72) {
                    $levels[$step] = 'terlambat';
                } elseif ($hoursElapsed >= 24) {
                    $levels[$step] = 'peringatan';
                } else {
                    $levels[$step] = 'aman';
                }
            }
        }

        return $levels;
    }

    /**
     * Calculate duration between two dates
     * Returns an array with minutes and display keys for workflow stages
     */
    private function calculateDuration($from, $to)
    {
        if (!$from || !$to) {
            return null;
        }

        try {
            $from = Carbon::parse($from);
            $to = Carbon::parse($to);
            $diffMinutes = abs($from->diffInMinutes($to));

            // Format display string
            $diff = $from->diff($to);
            $parts = [];
            if ($diff->y > 0)
                $parts[] = $diff->y . ' tahun';
            if ($diff->m > 0)
                $parts[] = $diff->m . ' bulan';
            if ($diff->d > 0)
                $parts[] = $diff->d . ' hari';
            if ($diff->h > 0)
                $parts[] = $diff->h . ' jam';
            if ($diff->i > 0)
                $parts[] = $diff->i . ' menit';

            $display = empty($parts) ? '< 1 menit' : implode(' ', $parts);

            return [
                'minutes' => $diffMinutes,
                'display' => $display,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * Get display name for role
     */
    private function getRoleDisplayName($role)
    {
        // Normalize handler name first to handle all variants
        $normalizedRole = $this->normalizeHandlerName($role);

        $roleMap = [
            'operator' => 'Operator',
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Pembayaran',
        ];

        return $roleMap[$normalizedRole] ?? $role;
    }

    /**
     * Get workflow timeline with inbox step for transparency
     * Returns timeline data showing when document was sent, received in inbox, and processed by each role
     */
    private function getWorkflowTimeline($dokumen): array
    {
        $roleOrder = [
            'operator' => ['label' => 'Bagian', 'icon' => '📋'],
            'team_verifikasi' => ['label' => 'Verif', 'icon' => '✓'],
            'perpajakan' => ['label' => 'Perpajakan', 'icon' => '💰'],
            'akutansi' => ['label' => 'Akutansi', 'icon' => '📊'],
            'pembayaran' => ['label' => 'Pembayaran', 'icon' => '💳'],
        ];

        $currentHandler = $dokumen->current_handler ?? 'operator';

        // Normalize handler to match roleOrder keys
        $normalizedHandler = strtolower($currentHandler);
        if ($normalizedHandler === 'team_verifikasi' || $normalizedHandler === 'team_verifikasi') {
            $normalizedHandler = 'team_verifikasi';
        }

        $status = $dokumen->status ?? 'draft';
        $roleKeys = array_keys($roleOrder);
        $currentIndex = array_search($normalizedHandler, $roleKeys);

        // Determine if document is in inbox (sent but not yet approved/processed by current role)
        $isInInbox = false;
        if ($currentHandler !== 'operator') {
            // Check roleStatuses to see if current role has approved the document
            $hasApproved = false;
            if (method_exists($dokumen, 'roleStatuses') && $dokumen->roleStatuses) {
                $roleStatus = $dokumen->roleStatuses->where('role_code', $currentHandler)->first();
                if (!$roleStatus) {
                    // Try with lowercase Team Verifikasi for verifikasi
                    $roleStatus = $dokumen->roleStatuses->where('role_code', 'team_verifikasi')->first();
                }
                if ($roleStatus && $roleStatus->status === \App\Models\DokumenStatus::STATUS_APPROVED) {
                    $hasApproved = true;
                }
            }

            // Document is in inbox if current role hasn't approved it yet
            // But we also need to check if document is actually in inbox (sent_to status)
            $isInboxStatus = in_array($status, [
                'sent_to_team_verifikasi',
                'menunggu_verifikasi',
                'pending_approval_team_verifikasi',
                'sent_to_perpajakan',
                'pending_approval_perpajakan',
                'sent_to_akutansi',
                'pending_approval_akutansi',
                'sent_to_pembayaran',
                'pending_approval_pembayaran',
            ]);

            // If role has already approved, document is NOT in inbox (it's in daftar dokumen)
            $isInInbox = !$hasApproved && $isInboxStatus;
        }

        $timeline = [];
        foreach ($roleKeys as $index => $role) {
            $roleInfo = $roleOrder[$role];
            $roleData = null;

            if (method_exists($dokumen, 'getDataForRole')) {
                $roleData = $dokumen->getDataForRole($role);
            } elseif (isset($dokumen->roleData)) {
                $roleData = $dokumen->roleData->where('role_code', $role)->first();
            }

            // Determine step status
            $stepStatus = 'pending';
            $inboxStatus = 'pending';

            if ($index < $currentIndex) {
                $stepStatus = 'completed';
                $inboxStatus = 'completed';
            } elseif ($index === $currentIndex) {
                if ($isInInbox) {
                    $stepStatus = 'waiting'; // In inbox, waiting to be processed
                    $inboxStatus = 'active';
                } else {
                    $stepStatus = 'active'; // Being processed
                    $inboxStatus = 'completed';
                }
            }

            // Get timestamps
            $sentAt = null;
            $receivedAt = null;
            $processedAt = null;
            $elapsedInRole = null;

            if ($roleData) {
                $receivedAt = $roleData->received_at;
                $processedAt = $roleData->processed_at;

                if ($receivedAt) {
                    $now = Carbon::now();
                    $elapsedInRole = Carbon::parse($receivedAt)->diffForHumans($now, true);
                }
            }

            // Get sent_at from previous role or dokumen timestamps
            if ($role === 'team_verifikasi' && $dokumen->sent_to_team_verifikasi_at) {
                $sentAt = $dokumen->sent_to_team_verifikasi_at;
            } elseif ($index > 0) {
                $prevRole = $roleKeys[$index - 1];
                $prevRoleData = null;
                if (method_exists($dokumen, 'getDataForRole')) {
                    $prevRoleData = $dokumen->getDataForRole($prevRole);
                } elseif (isset($dokumen->roleData)) {
                    $prevRoleData = $dokumen->roleData->where('role_code', $prevRole)->first();
                }
                if ($prevRoleData && $prevRoleData->processed_at) {
                    $sentAt = $prevRoleData->processed_at;
                }
            }

            $timeline[] = [
                'role' => $role,
                'label' => $roleInfo['label'],
                'icon' => $roleInfo['icon'],
                'status' => $stepStatus,
                'inbox_status' => $inboxStatus,
                'is_current' => $index === $currentIndex,
                'in_inbox' => $index === $currentIndex && $isInInbox,
                'sent_at' => $sentAt ? Carbon::parse($sentAt)->format('d M Y, H:i') : null,
                'received_at' => $receivedAt ? Carbon::parse($receivedAt)->format('d M Y, H:i') : null,
                'processed_at' => $processedAt ? Carbon::parse($processedAt)->format('d M Y, H:i') : null,
                'elapsed' => $elapsedInRole,
            ];
        }

        return [
            'steps' => $timeline,
            'current_handler' => $currentHandler,
            'current_handler_display' => $this->getRoleDisplayName($currentHandler),
            'is_in_inbox' => $isInInbox,
            'total_steps' => count($roleKeys),
            'current_step' => $currentIndex + 1,
        ];
    }

    /**
     * Get return destination
     */
    private function getReturnDestination($dokumen)
    {
        if ($dokumen->returned_to_Operator_at)
            return 'Operator';
        if ($dokumen->return_source === 'perpajakan' || $dokumen->return_source === 'akutansi' || $dokumen->return_source === 'pembayaran')
            return 'Ibu Tarapul (Department)';
        if ($dokumen->returned_at)
            return 'Team Verifikasi';
        return 'Tidak Diketahui';
    }

    /**
     * Get status display name in Indonesian
     */
    private function getStatusDisplayName($status)
    {
        $statusMap = [
            'draft' => 'Draft',
            'sedang diproses' => 'Sedang Diproses',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pending_approval_team_verifikasi' => 'Menunggu Persetujuan Team Verifikasi',
            'sent_to_team_verifikasi' => 'Terkirim ke Team Verifikasi',
            'proses_Team Verifikasi' => 'Diproses Team Verifikasi',
            'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
            'proses_perpajakan' => 'Diproses Team Perpajakan',
            'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
            'proses_akutansi' => 'Diproses Team Akutansi',
            'menunggu_approved_pengiriman' => 'Menunggu Persetujuan Pengiriman',
            'proses_pembayaran' => 'Diproses Team Pembayaran',
            'sent_to_pembayaran' => 'Terkirim ke Team Pembayaran',
            'approved_data_sudah_terkirim' => 'Data Sudah Terkirim',
            'rejected_data_tidak_lengkap' => 'Ditolak - Data Tidak Lengkap',
            'selesai' => 'Selesai',
            'returned_to_Operator' => 'Dikembalikan ke Ibu Tarapul',
            'returned_to_department' => 'Dikembalikan ke Department',
            'returned_to_bidang' => 'Dikembalikan ke Bidang',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime()
    {
        $completedDocs = Dokumen::where('status', 'approved_data_sudah_terkirim')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($completedDocs->isEmpty()) {
            return '0 hari';
        }

        $totalHours = $completedDocs->sum(function ($doc) {
            return $doc->created_at->diffInHours($doc->updated_at);
        });

        $avgHours = $totalHours / $completedDocs->count();

        if ($avgHours < 24) {
            return round($avgHours, 1) . ' jam';
        } else {
            return round($avgHours / 24, 1) . ' hari';
        }
    }

    /**
     * Get fastest processing department
     */
    private function getFastestDepartment()
    {
        $departments = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $avgTimes = [];

        foreach ($departments as $dept) {
            $avgTimes[$dept] = $this->getDepartmentAvgProcessingTime($dept);
        }

        return empty($avgTimes) ? '-' : array_keys($avgTimes, min($avgTimes))[0];
    }

    /**
     * Get slowest processing department
     */
    private function getSlowestDepartment()
    {
        $departments = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        $avgTimes = [];

        foreach ($departments as $dept) {
            $avgTimes[$dept] = $this->getDepartmentAvgProcessingTime($dept);
        }

        return empty($avgTimes) ? '-' : array_keys($avgTimes, max($avgTimes))[0];
    }

    /**
     * Get department average processing time
     */
    private function getDepartmentAvgProcessingTime($dept)
    {
        $completedDocs = Dokumen::where('status', 'approved_data_sudah_terkirim')
            ->where('created_by', $dept)
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($completedDocs->isEmpty()) {
            return 999; // High number for departments with no completed docs
        }

        $totalHours = $completedDocs->sum(function ($doc) {
            return $doc->created_at->diffInHours($doc->updated_at);
        });

        return $totalHours / $completedDocs->count();
    }

    /**
     * Display tracking dokumen page for all roles (without header and statistics)
     */
    public function trackingDokumen(Request $request)
    {
        // Get paginated documents with latest status and apply search filter
        $perPage = $request->get('per_page', session('owner_tracking_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['owner_tracking_per_page' => $perPage]);

        $documents = $this->getDocumentsWithTracking($request, $perPage);

        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        // Determine module based on user role
        $user = auth()->user();
        $module = 'operator'; // default
        if ($user) {
            $role = strtolower($user->role ?? '');
            if (in_array($role, ['team_verifikasi', 'pembayaran', 'akutansi', 'perpajakan'])) {
                $module = $role;
            }
        }

        return view('tracking.dokumen', [
            'documents' => $documents,
            'filterData' => $filterData,
            'search' => $request->get('search', ''),
            'module' => $module,
            'title' => 'Tracking Dokumen',
            'menuDashboard' => '',
            'menuDokumen' => '',
            'menuDaftarDokumen' => '',
            'menuRekapanDokumen' => '',
        ]);
    }

    /**
     * Show workflow tracking page for a document
     */
    public function showWorkflow(Request $request, $id)
    {
        $dokumen = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
            ->findOrFail($id);

        $workflowStages = $this->generateWorkflowStages($dokumen);

        // Load activity logs for each stage
        try {
            $dokumen->load('activityLogs');
            $activityLogsByStage = $dokumen->activityLogs->groupBy('stage');
        } catch (\Exception $e) {
            // If table doesn't exist yet, use empty collection
            $activityLogsByStage = collect();
        }

        // Detect user role to set appropriate module and dashboard URL
        $user = auth()->user();
        $userRole = strtolower($user->role ?? $user->name ?? 'owner');
        $module = 'owner';
        $dashboardUrl = '/tracking-dokumen'; // Default to tracking dokumen for all roles

        // Set module and dashboard URL based on user role
        if (in_array($userRole, ['operator', 'ibutara', 'Operator'])) {
            $module = 'operator';
            $dashboardUrl = '/dashboard';
        } elseif (in_array($userRole, ['team_verifikasi', 'ibuyuni', 'ibu b', 'team verifikasi'])) {
            $module = 'team_verifikasi';
            $dashboardUrl = '/dashboardB';
        } elseif (in_array($userRole, ['pembayaran', 'team pembayaran'])) {
            $module = 'pembayaran';
            $dashboardUrl = '/dashboardPembayaran';
        } elseif (in_array($userRole, ['akutansi', 'team akutansi'])) {
            $module = 'akutansi';
            $dashboardUrl = '/dashboardAkutansi';
        } elseif (in_array($userRole, ['perpajakan', 'team perpajakan'])) {
            $module = 'perpajakan';
            $dashboardUrl = '/dashboardPerpajakan';
        } elseif (str_starts_with($userRole, 'bagian') || preg_match('/^[A-Z]{3}$/', $user->role ?? '')) {
            // Bagian users have role codes like SDM, KEU, etc or starts with 'bagian'
            $module = 'bagian';
            $dashboardUrl = '/bagian/tracking';
        } elseif (in_array($userRole, ['admin', 'owner'])) {
            $module = 'owner';
            $dashboardUrl = '/owner/dokumen';
        }

        // If a return_url was passed (e.g., from Rekapan Keterlambatan with filters), use it
        if ($request->has('return_url') && $request->return_url) {
            $dashboardUrl = $request->return_url;
        }

        return view('owner.workflow', compact('dokumen', 'workflowStages', 'activityLogsByStage'))
            ->with('title', 'Workflow Tracking - ' . $dokumen->nomor_agenda)
            ->with('module', $module)
            ->with('menuDashboard', '')
            ->with('menuDokumen', '') // Default value - prevents undefined variable error
            ->with('menuDaftarDokumen', '') // Default value
            ->with('menuRekapanDokumen', '') // Default value
            ->with('menuRekapKeterlambatan', '') // Default value
            ->with('dashboardUrl', $dashboardUrl);
    }

    /**
     * Generate workflow stages for visualization
     */
    private function generateWorkflowStages($dokumen)
    {
        $stages = [];
        $currentTime = now();
        $returnEvents = $this->getReturnEvents($dokumen);

        // Stage 1: ibutara (Ibu Tarapul) - Always completed
        $stages[] = [
            'id' => 'sender',
            'name' => 'Operator',
            'label' => 'ibutara',
            'status' => 'completed',
            'timestamp' => $dokumen->created_at,
            'icon' => 'fa-user',
            'color' => '#10b981',
            'description' => 'Dokumen DOperatort',
            'details' => [
                'DOperatort oleh' => 'Operator',
                'Nomor Agenda' => $dokumen->nomor_agenda,
                'Nomor SPP' => $dokumen->nomor_spp,
                'Nilai' => 'Rp. ' . number_format($dokumen->nilai_rupiah, 0, ',', '.'),
            ],
            'hasReturn' => false,
            'returnInfo' => null
        ];

        // Stage 2: REVIEWER (Ibu Yuni)
        $reviewerStatus = 'pending';
        $reviewerTimestamp = null;
        $reviewerDescription = 'Menunggu';
        $reviewerReturnInfo = $this->getReturnInfoForStage($dokumen, 'reviewer', $returnEvents);
        $reviewerCycleInfo = $this->getCycleInfo($dokumen, 'reviewer');

        // Get role data for Team Verifikasi
        $reviewerRoleData = $dokumen->getDataForRole('team_verifikasi');

        // Check if document was sent to next role (perpajakan or akutansi)
        $sentToNextRole = ($dokumen->getDataForRole('perpajakan')?->received_at || $dokumen->getDataForRole('akutansi')?->received_at);

        if ($reviewerRoleData?->received_at) {
            // Document is received at Team Verifikasi
            if ($sentToNextRole || $reviewerRoleData->processed_at) {
                // Document has been processed and sent to next role
                $reviewerStatus = 'completed';
                $reviewerTimestamp = $reviewerRoleData->processed_at ?? $reviewerRoleData->received_at;
                $reviewerDescription = 'Diproses Ibu Yuni';
            } else {
                // Document is still being processed at Team Verifikasi
                $reviewerStatus = 'processing';
                $reviewerTimestamp = $reviewerRoleData->received_at;
                $reviewerDescription = 'Sedang diproses Ibu Yuni';
            }

            // Check if this is a re-send after return
            if ($reviewerCycleInfo && $reviewerCycleInfo['isResend']) {
                $reviewerDescription .= ' (Attempt ' . $reviewerCycleInfo['attemptCount'] . ')';
            }
        }

        // Check if returned from this stage
        if ($reviewerReturnInfo && !$reviewerCycleInfo['isResend']) {
            $reviewerStatus = 'returned';
            if (!$reviewerTimestamp) {
                $reviewerTimestamp = $reviewerRoleData?->received_at ?? $dokumen->created_at;
            }
        }

        // Calculate deadline level based on received_at (count up from when document was received)
        $reviewerRoleData = $dokumen->getDataForRole('team_verifikasi');
        $reviewerIsOverdue = false;
        $reviewerDeadlineInfo = null;
        $reviewerDeadlineLevel = null;

        // Calculate for both active (vs now) and completed (vs processed_at) stages
        if ($reviewerRoleData && $reviewerRoleData->received_at) {
            // For completed: use processed_at as endpoint; for active: use now()
            $endTime = $reviewerRoleData->processed_at ?? now();
            $hoursElapsed = $reviewerRoleData->received_at->diffInHours($endTime);
            $daysElapsed = floor($hoursElapsed / 24);

            if ($hoursElapsed >= 72) {
                if (!$reviewerRoleData->processed_at) $reviewerIsOverdue = true;
                $reviewerDeadlineLevel = 'terlambat';

                $hoursOverdue = $hoursElapsed - 72;
                $daysOverdue = floor($hoursOverdue / 24);
                $remainingHours = $hoursOverdue % 24;

                $reviewerDeadlineInfo = [
                    'received_at' => $reviewerRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'days_overdue' => $daysOverdue,
                    'hours_overdue' => $remainingHours,
                    'deadline_at' => $reviewerRoleData->received_at->copy()->addHours(72),
                    'is_historical' => $reviewerRoleData->processed_at ? true : false,
                ];
            } elseif ($hoursElapsed >= 24) {
                $reviewerDeadlineLevel = 'peringatan';
                $reviewerDeadlineInfo = [
                    'received_at' => $reviewerRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'days_overdue' => 0,
                    'hours_overdue' => 0,
                    'deadline_at' => $reviewerRoleData->received_at->copy()->addHours(72),
                    'is_historical' => $reviewerRoleData->processed_at ? true : false,
                ];
            } else {
                $reviewerDeadlineLevel = 'aman';
                $reviewerDeadlineInfo = [
                    'received_at' => $reviewerRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_overdue' => 0,
                    'hours_overdue' => 0,
                    'is_historical' => $reviewerRoleData->processed_at ? true : false,
                ];
            }
        }

        // Calculate duration for Team Verifikasi
        $reviewerDuration = null;
        if ($reviewerRoleData && $reviewerRoleData->received_at) {
            // If still processing, calculate from received_at to now
            // If completed, calculate from received_at to processed_at
            if ($reviewerStatus === 'processing') {
                $reviewerDuration = $this->calculateDuration($reviewerRoleData->received_at, now());
            } elseif ($reviewerStatus === 'completed' && $reviewerRoleData->processed_at) {
                $reviewerDuration = $this->calculateDuration($reviewerRoleData->received_at, $reviewerRoleData->processed_at);
            } elseif ($sentToNextRole) {
                // If sent to next role but no processed_at, use received_at of next role
                $nextRoleReceivedAt = $dokumen->getDataForRole('perpajakan')?->received_at ?? $dokumen->getDataForRole('akutansi')?->received_at;
                if ($nextRoleReceivedAt) {
                    $reviewerDuration = $this->calculateDuration($reviewerRoleData->received_at, $nextRoleReceivedAt);
                }
            }
        }

        $stages[] = [
            'id' => 'reviewer',
            'name' => 'Team Verifikasi',
            'label' => 'teamverifikasi',
            'status' => $reviewerStatus,
            'timestamp' => $reviewerTimestamp,
            'icon' => 'fa-user-check',
            'color' => $reviewerStatus === 'completed' ? '#10b981' : ($reviewerStatus === 'processing' ? '#3b82f6' : ($reviewerStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $reviewerDescription,
            'duration' => $reviewerDuration,
            'details' => $reviewerTimestamp ? [
                'Dikirim pada' => $dokumen->getDataForRole('team_verifikasi')?->received_at ? $dokumen->getDataForRole('team_verifikasi')->received_at->format('d M Y H:i') : '-',
                'Diproses pada' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '-',
            ] : [],
            'hasReturn' => $reviewerReturnInfo !== null,
            'returnInfo' => $reviewerReturnInfo,
            'hasCycle' => $reviewerCycleInfo['hasCycle'],
            'cycleInfo' => $reviewerCycleInfo,
            'isOverdue' => $reviewerIsOverdue,
            'deadlineInfo' => $reviewerDeadlineInfo,
            'deadlineLevel' => $reviewerDeadlineLevel
        ];

        // Stage 3: TAX (Team Perpajakan)
        $taxStatus = 'pending';
        $taxTimestamp = null;
        $taxDescription = 'Menunggu';
        $taxReturnInfo = $this->getReturnInfoForStage($dokumen, 'tax', $returnEvents);
        $taxCycleInfo = $this->getCycleInfo($dokumen, 'tax');

        if ($dokumen->getDataForRole('perpajakan')?->received_at) {
            $taxStatus = 'processing';
            $taxTimestamp = $dokumen->getDataForRole('perpajakan')->received_at;
            $taxDescription = 'Dikirim ke Team Perpajakan';

            // Check if this is a re-send after return
            if ($taxCycleInfo && $taxCycleInfo['isResend']) {
                $taxDescription = 'Dikirim kembali ke Team Perpajakan (Attempt ' . $taxCycleInfo['attemptCount'] . ')';
            }
        }

        if ($dokumen->processed_perpajakan_at) {
            $taxStatus = 'completed';
            $taxTimestamp = $dokumen->processed_perpajakan_at;
            $taxDescription = 'Diproses Team Perpajakan';

            // Check if completed after return
            if ($taxCycleInfo && $taxCycleInfo['isResend']) {
                $taxDescription = 'Diproses Team Perpajakan (Attempt ' . $taxCycleInfo['attemptCount'] . ')';
            }
        }

        // Check if returned from this stage (only if not re-sent)
        if ($taxReturnInfo && !$taxCycleInfo['isResend']) {
            // Only show as returned if not yet re-sent
            $perpajakanReceivedAt = $dokumen->getDataForRole('perpajakan')?->received_at;
            if (
                !$perpajakanReceivedAt ||
                ($dokumen->returned_from_perpajakan_at &&
                    $perpajakanReceivedAt->lte($dokumen->returned_from_perpajakan_at))
            ) {
                $taxStatus = 'returned';
                if (!$taxTimestamp) {
                    $taxTimestamp = $perpajakanReceivedAt ?? $dokumen->processed_at;
                }
            }
        }

        // Calculate deadline level based on received_at (count up from when document was received)
        $taxRoleData = $dokumen->getDataForRole('perpajakan');
        $taxIsOverdue = false;
        $taxDeadlineInfo = null;
        $taxDeadlineLevel = null;

        // Calculate for both active (vs now) and completed (vs processed_at) stages
        if ($taxRoleData && $taxRoleData->received_at) {
            $endTime = $taxRoleData->processed_at ?? now();
            $hoursElapsed = $taxRoleData->received_at->diffInHours($endTime);
            $daysElapsed = floor($hoursElapsed / 24);

            if ($hoursElapsed >= 72) {
                if (!$taxRoleData->processed_at) $taxIsOverdue = true;
                $taxDeadlineLevel = 'terlambat';
                $taxDeadlineInfo = [
                    'received_at' => $taxRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $taxRoleData->processed_at ? true : false,
                ];
            } elseif ($hoursElapsed >= 24) {
                $taxDeadlineLevel = 'peringatan';
                $taxDeadlineInfo = [
                    'received_at' => $taxRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $taxRoleData->processed_at ? true : false,
                ];
            } else {
                $taxDeadlineLevel = 'aman';
                $taxDeadlineInfo = [
                    'received_at' => $taxRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'is_historical' => $taxRoleData->processed_at ? true : false,
                ];
            }
        }

        $stages[] = [
            'id' => 'tax',
            'name' => 'Team Perpajakan',
            'label' => 'team perpajakan',
            'status' => $taxStatus,
            'timestamp' => $taxTimestamp,
            'icon' => 'fa-file-invoice',
            'color' => $taxStatus === 'completed' ? '#10b981' : ($taxStatus === 'processing' ? '#3b82f6' : ($taxStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $taxDescription,
            'details' => $taxTimestamp ? [
                'Dikirim pada' => $dokumen->getDataForRole('perpajakan')?->received_at ? $dokumen->getDataForRole('perpajakan')->received_at->format('d M Y H:i') : '-',
                'Diproses pada' => $dokumen->processed_perpajakan_at ? $dokumen->processed_perpajakan_at->format('d M Y H:i') : '-',
                'Status' => $dokumen->status_perpajakan ?? '-',
            ] : [],
            'hasReturn' => $taxReturnInfo !== null,
            'returnInfo' => $taxReturnInfo,
            'hasCycle' => $taxCycleInfo['hasCycle'],
            'cycleInfo' => $taxCycleInfo,
            'isOverdue' => $taxIsOverdue,
            'deadlineInfo' => $taxDeadlineInfo,
            'deadlineLevel' => $taxDeadlineLevel
        ];

        // Stage 4: ACCOUNTING (Team Akutansi)
        $accountingStatus = 'pending';
        $accountingTimestamp = null;
        $accountingDescription = 'Menunggu';
        $accountingReturnInfo = $this->getReturnInfoForStage($dokumen, 'accounting', $returnEvents);

        // Check if sent to akutansi (using status or sent_to_akutansi_at if exists)
        if ($dokumen->status === 'sent_to_akutansi' || $dokumen->status === 'processed_by_akutansi') {
            $accountingStatus = 'processing';
            $accountingTimestamp = $dokumen->updated_at;
            $accountingDescription = 'Dikirim ke Team Akutansi';
        }

        // Try to get sent_to_akutansi_at from database (might not exist in model fillable)
        $sentToAkutansiAt = null;
        try {
            $sentToAkutansiAt = $dokumen->getAttribute('sent_to_akutansi_at') ??
                (\DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_akutansi_at') ?
                    \Carbon\Carbon::parse(\DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_akutansi_at')) : null);
            if ($sentToAkutansiAt) {
                $accountingStatus = 'processing';
                $accountingTimestamp = $sentToAkutansiAt;
                $accountingDescription = 'Dikirim ke Team Akutansi';
            }
        } catch (\Exception $e) {
            // Field might not exist, use status
        }

        if ($dokumen->status === 'processed_by_akutansi' || $dokumen->nomor_miro) {
            $accountingStatus = 'completed';
            $accountingTimestamp = $dokumen->updated_at;
            $accountingDescription = 'Diproses Team Akutansi';
        }

        // Check if returned from this stage
        if ($accountingReturnInfo) {
            $accountingStatus = 'returned';
            if (!$accountingTimestamp) {
                $accountingTimestamp = $sentToAkutansiAt ?? $dokumen->processed_perpajakan_at;
            }
        }

        // Calculate deadline level based on received_at (count up from when document was received)
        $accountingRoleData = $dokumen->getDataForRole('akutansi');
        $accountingIsOverdue = false;
        $accountingDeadlineInfo = null;
        $accountingDeadlineLevel = null;

        // Calculate for both active (vs now) and completed (vs processed_at) stages
        if ($accountingRoleData && $accountingRoleData->received_at) {
            $endTime = $accountingRoleData->processed_at ?? now();
            $hoursElapsed = $accountingRoleData->received_at->diffInHours($endTime);
            $daysElapsed = floor($hoursElapsed / 24);

            if ($hoursElapsed >= 72) {
                if (!$accountingRoleData->processed_at) $accountingIsOverdue = true;
                $accountingDeadlineLevel = 'terlambat';
                $accountingDeadlineInfo = [
                    'received_at' => $accountingRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $accountingRoleData->processed_at ? true : false,
                ];
            } elseif ($hoursElapsed >= 24) {
                $accountingDeadlineLevel = 'peringatan';
                $accountingDeadlineInfo = [
                    'received_at' => $accountingRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $accountingRoleData->processed_at ? true : false,
                ];
            } else {
                $accountingDeadlineLevel = 'aman';
                $accountingDeadlineInfo = [
                    'received_at' => $accountingRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'is_historical' => $accountingRoleData->processed_at ? true : false,
                ];
            }
        }

        $stages[] = [
            'id' => 'accounting',
            'name' => 'Team Akutansi',
            'label' => 'team akutansi',
            'status' => $accountingStatus,
            'timestamp' => $accountingTimestamp,
            'icon' => 'fa-calculator',
            'color' => $accountingStatus === 'completed' ? '#10b981' : ($accountingStatus === 'processing' ? '#3b82f6' : ($accountingStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $accountingDescription,
            'details' => $accountingTimestamp ? [
                'Nomor MIRO' => $dokumen->nomor_miro ?? '-',
                'Status' => $dokumen->status,
            ] : [],
            'hasReturn' => $accountingReturnInfo !== null,
            'returnInfo' => $accountingReturnInfo,
            'isOverdue' => $accountingIsOverdue,
            'deadlineInfo' => $accountingDeadlineInfo,
            'deadlineLevel' => $accountingDeadlineLevel
        ];

        // Stage 5: PAYMENT (Pembayaran)
        $paymentStatus = 'pending';
        $paymentTimestamp = null;
        $paymentDescription = 'Menunggu';

        // Get sent_to_pembayaran_at from model or database
        $sentToPembayaranAt = null;
        if (isset($dokumen->sent_to_pembayaran_at)) {
            $sentToPembayaranAt = $dokumen->sent_to_pembayaran_at;
        } else {
            try {
                $sentToPembayaranAt = \DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_pembayaran_at');
                if ($sentToPembayaranAt) {
                    $sentToPembayaranAt = \Carbon\Carbon::parse($sentToPembayaranAt);
                }
            } catch (\Exception $e) {
                // Field might not exist
            }
        }

        // Check if sent to pembayaran
        if ($dokumen->status === 'sent_to_pembayaran' || $dokumen->current_handler === 'pembayaran' || $sentToPembayaranAt) {
            $paymentStatus = 'processing';
            $paymentTimestamp = $sentToPembayaranAt ?? $dokumen->updated_at;
            $paymentDescription = 'Dikirim ke Pembayaran';
        }

        // Get status_pembayaran from model or database
        $statusPembayaran = null;
        if (isset($dokumen->status_pembayaran)) {
            $statusPembayaran = $dokumen->status_pembayaran;
        } else {
            try {
                $statusPembayaran = \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
            } catch (\Exception $e) {
                // Field might not exist
            }
        }

        // Check if payment is completed
        if ($statusPembayaran === 'sudah_dibayar' || $dokumen->status === 'selesai' || $dokumen->status === 'approved_data_sudah_terkirim' || $dokumen->status === 'completed') {
            $paymentStatus = 'completed';
            if (!$paymentTimestamp) {
                $paymentTimestamp = $sentToPembayaranAt ?? $dokumen->updated_at;
            }
            $paymentDescription = 'Selesai Dibayar';
        }

        // Calculate deadline level for pembayaran based on received_at (count up)
        $paymentRoleData = $dokumen->getDataForRole('pembayaran');
        $paymentIsOverdue = false;
        $paymentDeadlineInfo = null;
        $paymentDeadlineLevel = null;

        // Calculate for both active (vs now) and completed (vs processed_at/tanggal_dibayar) stages
        if ($paymentRoleData && $paymentRoleData->received_at) {
            $isPaymentCompleted = $paymentRoleData->processed_at || $paymentStatus === 'completed';
            $endTime = $paymentRoleData->processed_at ?? ($paymentStatus === 'completed' ? ($dokumen->tanggal_dibayar ?? now()) : now());
            $hoursElapsed = $paymentRoleData->received_at->diffInHours($endTime);
            $daysElapsed = floor($hoursElapsed / 24);

            if ($hoursElapsed >= 72) {
                if (!$isPaymentCompleted) $paymentIsOverdue = true;
                $paymentDeadlineLevel = 'terlambat';
                $paymentDeadlineInfo = [
                    'received_at' => $paymentRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $isPaymentCompleted,
                ];
            } elseif ($hoursElapsed >= 24) {
                $paymentDeadlineLevel = 'peringatan';
                $paymentDeadlineInfo = [
                    'received_at' => $paymentRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'days_elapsed' => $daysElapsed,
                    'is_historical' => $isPaymentCompleted,
                ];
            } else {
                $paymentDeadlineLevel = 'aman';
                $paymentDeadlineInfo = [
                    'received_at' => $paymentRoleData->received_at,
                    'hours_elapsed' => $hoursElapsed,
                    'is_historical' => $isPaymentCompleted,
                ];
            }
        }

        $stages[] = [
            'id' => 'payment',
            'name' => 'Pembayaran',
            'label' => 'pembayaran',
            'status' => $paymentStatus,
            'timestamp' => $paymentTimestamp,
            'icon' => 'fa-money-bill-wave',
            'color' => $paymentStatus === 'completed' ? '#10b981' : ($paymentStatus === 'processing' ? '#3b82f6' : ($paymentStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $paymentDescription,
            'details' => $paymentTimestamp ? [
                'Status Pembayaran' => $statusPembayaran ?? '-',
                'Status' => $dokumen->status,
            ] : [],
            'hasReturn' => false,
            'returnInfo' => null,
            'isOverdue' => $paymentIsOverdue,
            'deadlineInfo' => $paymentDeadlineInfo,
            'deadlineLevel' => $paymentDeadlineLevel
        ];

        // Calculate durations between stages
        for ($i = 0; $i < count($stages); $i++) {
            if ($i > 0 && $stages[$i]['timestamp'] && $stages[$i - 1]['timestamp']) {
                $stages[$i]['duration'] = $this->calculateDuration($stages[$i - 1]['timestamp'], $stages[$i]['timestamp']);
            }
        }

        return $stages;
    }

    /**
     * Get all return events for a document
     */
    private function getReturnEvents($dokumen)
    {
        $returns = [];

        // Return from Perpajakan to Ibu Yuni
        if ($dokumen->returned_from_perpajakan_at) {
            $returns[] = [
                'from' => 'tax',
                'to' => 'reviewer',
                'timestamp' => $dokumen->returned_from_perpajakan_at,
                'reason' => $dokumen->return_reason ?? 'Tidak ada alasan',
                'returned_by' => 'Team Perpajakan',
                'returned_to' => 'Ibu Yuni'
            ];
        }

        // Return from Ibu Yuni to Bidang
        if ($dokumen->status === 'returned_to_bidang' && $dokumen->returned_at) {
            $returns[] = [
                'from' => 'reviewer',
                'to' => 'bidang',
                'timestamp' => $dokumen->returned_at,
                'reason' => $dokumen->return_reason ?? 'Tidak ada alasan',
                'returned_by' => 'Ibu Yuni',
                'returned_to' => 'Bidang: ' . ($dokumen->return_source ?? 'Tidak diketahui')
            ];
        }

        // Return to Department
        if ($dokumen->status === 'returned_to_department' && $dokumen->returned_at) {
            $returns[] = [
                'from' => $dokumen->current_handler === 'perpajakan' ? 'tax' : ($dokumen->current_handler === 'akutansi' ? 'accounting' : 'reviewer'),
                'to' => 'department',
                'timestamp' => $dokumen->returned_at,
                'reason' => $dokumen->return_reason ?? 'Tidak ada alasan',
                'returned_by' => $this->getRoleDisplayName($dokumen->current_handler),
                'returned_to' => 'Department'
            ];
        }

        // Return to Ibu A
        if ($dokumen->returned_to_Operator_at) {
            $returns[] = [
                'from' => 'reviewer',
                'to' => 'sender',
                'timestamp' => $dokumen->returned_to_Operator_at,
                'reason' => $dokumen->return_reason ?? 'Tidak ada alasan',
                'returned_by' => 'Ibu Yuni',
                'returned_to' => 'Operator'
            ];
        }

        return $returns;
    }

    /**
     * Get return info for a specific stage
     */
    private function getReturnInfoForStage($dokumen, $stageId, $returnEvents)
    {
        foreach ($returnEvents as $return) {
            if ($return['from'] === $stageId) {
                return $return;
            }
        }
        return null;
    }

    /**
     * Get cycle/loop information for a stage (return and re-send)
     */
    private function getCycleInfo($dokumen, $stageId)
    {
        $hasCycle = false;
        $isResend = false;
        $attemptCount = 1;
        $returnTimestamp = null;
        $resendTimestamp = null;

        if ($stageId === 'tax') {
            // Check if returned from perpajakan
            if ($dokumen->returned_from_perpajakan_at) {
                $hasCycle = true;
                $returnTimestamp = $dokumen->returned_from_perpajakan_at;
                $attemptCount = 1;

                // Check if sent back after return
                $perpajakanReceivedAt = $dokumen->getDataForRole('perpajakan')?->received_at;
                if (
                    $perpajakanReceivedAt &&
                    $perpajakanReceivedAt->gt($dokumen->returned_from_perpajakan_at)
                ) {
                    $isResend = true;
                    $resendTimestamp = $perpajakanReceivedAt;
                    $attemptCount = 2;

                    // Check if there's a fixed_at timestamp (indicates re-send after fix)
                    if ($dokumen->getAttribute('returned_from_perpajakan_fixed_at')) {
                        $resendTimestamp = $dokumen->getAttribute('returned_from_perpajakan_fixed_at');
                    }
                }
            }
        } elseif ($stageId === 'reviewer') {
            // Check if returned to bidang and sent back
            if ($dokumen->returned_at && $dokumen->status === 'returned_to_bidang') {
                $hasCycle = true;
                $returnTimestamp = $dokumen->returned_at;
                $attemptCount = 1;

                // Check if processed again after return from bidang
                if (
                    $dokumen->processed_at &&
                    $dokumen->processed_at->gt($dokumen->returned_at)
                ) {
                    $isResend = true;
                    $resendTimestamp = $dokumen->processed_at;
                    $attemptCount = 2;
                }
            }

            // Check if returned to Ibu A and sent back
            if ($dokumen->returned_to_Operator_at) {
                $hasCycle = true;
                if (!$returnTimestamp || $dokumen->returned_to_Operator_at->gt($returnTimestamp)) {
                    $returnTimestamp = $dokumen->returned_to_Operator_at;
                }

                // Check if sent to Ibu B again after return
                $teamVerifikasiReceivedAt = $dokumen->getDataForRole('team_verifikasi')?->received_at;
                if (
                    $teamVerifikasiReceivedAt &&
                    $teamVerifikasiReceivedAt->gt($dokumen->returned_to_Operator_at)
                ) {
                    $isResend = true;
                    if (!$resendTimestamp || $teamVerifikasiReceivedAt->gt($resendTimestamp)) {
                        $resendTimestamp = $teamVerifikasiReceivedAt;
                    }
                    $attemptCount = max($attemptCount, 2);
                }
            }
        }

        return [
            'hasCycle' => $hasCycle,
            'isResend' => $isResend,
            'attemptCount' => $attemptCount,
            'returnTimestamp' => $returnTimestamp,
            'resendTimestamp' => $resendTimestamp
        ];
    }

    /**
     * Display rekapan dokumen for owner (all documents from all roles)
     */
    public function rekapan(Request $request)
    {
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            // Dokumen selesai: status completed atau status_pembayaran = sudah_dibayar
            $query->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            // Dokumen belum selesai: tidak termasuk status selesai
            $query->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        $dokumens = $query->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get statistics
        $statistics = $this->getRekapanStatistics($selectedBagian);

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list with document counts
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        // Get document counts per bagian
        $bagianCounts = [];
        foreach ($bagianList as $code => $name) {
            $countQuery = Dokumen::where('bagian', $code);

            // Apply same filters as main query (year filter)
            if ($request->has('year') && $request->year) {
                $countQuery->where('tahun', $request->year);
            }

            // Apply search filter if exists
            if ($request->has('search') && $request->search) {
                $search = trim((string) $request->search);
                if (!empty($search)) {
                    $countQuery->where(function ($q) use ($search) {
                        $q->where('nomor_agenda', 'like', '%' . $search . '%')
                            ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                            ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                            ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                    });
                }
            }

            // Apply completion status filter if exists
            if ($completionFilter === 'selesai') {
                $countQuery->where(function ($q) {
                    $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            } elseif ($completionFilter === 'belum_selesai') {
                $countQuery->where(function ($q) {
                    $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->where(function ($subQ) {
                            $subQ->whereNull('status_pembayaran')
                                ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                        });
                });
            }

            $bagianCounts[$code] = $countQuery->count();
        }

        $completionFilter = $request->get('completion_status', '');

        return view('owner.rekapan', compact('dokumens', 'statistics', 'availableYears', 'bagianList', 'bagianCounts', 'selectedBagian', 'completionFilter'))
            ->with('title', 'Rekapan Dokumen - Owner')
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display rekapan dokumen by handler (Ibu Tarapul, Ibu Yuni, Team Perpajakan, Team Akutansi)
     */
    public function rekapanByHandler(Request $request, $handler)
    {
        // Validate handler
        $validHandlers = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi'];
        if (!in_array($handler, $validHandlers)) {
            abort(404, 'Handler tidak valid');
        }

        $handlerNames = [
            'operator' => 'Operator',
            'team_verifikasi' => 'Ibu Yuni',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi'
        ];

        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Filter by handler
        if ($handler === 'operator') {
            // Ibu Tarapul: dokumen dengan current_handler = 'operator' atau status draft
            $query->where(function ($q) {
                $q->where('current_handler', 'operator')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'draft')
                            ->where(function ($subSubQ) {
                                $subSubQ->whereNull('current_handler')
                                    ->orWhere('current_handler', 'operator');
                            });
                    });
            });
        } elseif ($handler === 'team_verifikasi') {
            // Ibu Yuni: dokumen dengan current_handler = 'team_verifikasi' atau status sent_to_team_verifikasi
            $query->where(function ($q) {
                $q->where('current_handler', 'team_verifikasi')
                    ->orWhere('status', 'sent_to_team_verifikasi')
                    ->orWhere('status', 'pending_approval_team_verifikasi')
                    ->orWhere('status', 'proses_Team Verifikasi');
            });
        } elseif ($handler === 'perpajakan') {
            // Team Perpajakan: dokumen dengan current_handler = 'perpajakan' atau status sent_to_perpajakan
            $query->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_perpajakan');
            });
        } elseif ($handler === 'akutansi') {
            // Team Akutansi: dokumen dengan current_handler = 'akutansi' atau status sent_to_akutansi
            $query->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi');
            });
        } elseif ($handler === 'pembayaran') {
            // Team Pembayaran: dokumen dengan current_handler = 'pembayaran' atau status sent_to_pembayaran atau CSV imported
            $query->where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere(function ($csvQ) {
                        // Include CSV imported documents (exclusive to Pembayaran)
                        $csvQ->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                            $query->where('imported_from_csv', true);
                        });
                    });
            });
        }

        // Exclude CSV imported documents from all handlers except Pembayaran
        if ($handler !== 'pembayaran') {
            $query->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                        ->orWhereNull('imported_from_csv');
                });
            });
        }

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            $query->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            $query->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        $dokumens = $query->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get statistics (with handler filter)
        $statistics = $this->getRekapanStatistics($selectedBagian);

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list with document counts
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        // Get document counts per bagian (with handler filter)
        $bagianCounts = [];
        foreach ($bagianList as $code => $name) {
            $countQuery = Dokumen::where('bagian', $code);

            // Apply handler filter
            if ($handler === 'operator') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'operator')
                        ->orWhere(function ($subQ) {
                            $subQ->where('status', 'draft')
                                ->where(function ($subSubQ) {
                                    $subSubQ->whereNull('current_handler')
                                        ->orWhere('current_handler', 'operator');
                                });
                        });
                });
            } elseif ($handler === 'team_verifikasi') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'team_verifikasi')
                        ->orWhere('status', 'sent_to_team_verifikasi')
                        ->orWhere('status', 'pending_approval_team_verifikasi')
                        ->orWhere('status', 'proses_Team Verifikasi');
                });
            } elseif ($handler === 'perpajakan') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'perpajakan')
                        ->orWhere('status', 'sent_to_perpajakan');
                });
            } elseif ($handler === 'akutansi') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'akutansi')
                        ->orWhere('status', 'sent_to_akutansi');
                });
            }

            // Apply same filters as main query
            if ($request->has('year') && $request->year) {
                $countQuery->where('tahun', $request->year);
            }

            if ($request->has('search') && $request->search) {
                $search = trim((string) $request->search);
                if (!empty($search)) {
                    $countQuery->where(function ($q) use ($search) {
                        $q->where('nomor_agenda', 'like', '%' . $search . '%')
                            ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                            ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                            ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                    });
                }
            }

            if ($completionFilter === 'selesai') {
                $countQuery->where(function ($q) {
                    $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            } elseif ($completionFilter === 'belum_selesai') {
                $countQuery->where(function ($q) {
                    $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->where(function ($subQ) {
                            $subQ->whereNull('status_pembayaran')
                                ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                        });
                });
            }

            $bagianCounts[$code] = $countQuery->count();
        }

        return view('owner.rekapan', compact('dokumens', 'statistics', 'availableYears', 'bagianList', 'bagianCounts', 'selectedBagian', 'completionFilter', 'handler', 'handlerNames'))
            ->with('title', 'Rekapan Dokumen - ' . $handlerNames[$handler])
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display detail rekapan dengan 4 statistik (total, selesai, proses, terlambat)
     */
    public function rekapanDetail(Request $request, $type)
    {
        // Validate type
        $validTypes = ['total', 'selesai', 'operator', 'team_verifikasi', 'perpajakan', 'akutansi'];
        if (!in_array($type, $validTypes)) {
            abort(404, 'Type tidak valid');
        }

        $typeNames = [
            'total' => 'Total Dokumen',
            'selesai' => 'Dokumen Selesai',
            'operator' => 'Dokumen Ibu Tarapul',
            'team_verifikasi' => 'Dokumen Ibu Yuni',
            'perpajakan' => 'Dokumen Team Perpajakan',
            'akutansi' => 'Dokumen Team Akutansi'
        ];

        $now = Carbon::now();
        $baseQuery = Dokumen::query();

        // Apply filter based on type
        if ($type === 'total') {
            // All documents
        } elseif ($type === 'selesai') {
            // Completed documents
            $baseQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($type === 'operator') {
            // Ibu Tarapul documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'operator')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'draft')
                            ->where(function ($subSubQ) {
                                $subSubQ->whereNull('current_handler')
                                    ->orWhere('current_handler', 'operator');
                            });
                    });
            });
        } elseif ($type === 'team_verifikasi') {
            // Ibu Yuni documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'team_verifikasi')
                    ->orWhere('status', 'sent_to_team_verifikasi')
                    ->orWhere('status', 'pending_approval_team_verifikasi')
                    ->orWhere('status', 'proses_Team Verifikasi');
            });
        } elseif ($type === 'perpajakan') {
            // Team Perpajakan documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_perpajakan');
            });
        } elseif ($type === 'akutansi') {
            // Team Akutansi documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi');
            });
        }

        // Calculate 4 statistics
        // 1. Total Dokumen
        $totalDokumen = (clone $baseQuery)->count();

        // 2. Total Dokumen Selesai
        $totalSelesai = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar');
        })->count();

        // 3. Total Dokumen Proses (sedang diproses)
        $totalProses = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'sedang diproses')
                ->orWhere('status', 'sent_to_team_verifikasi')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'sent_to_pembayaran')
                ->orWhere('status', 'proses_Team Verifikasi')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'pending_approval_team_verifikasi');
        })->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
            ->where(function ($subQ) {
                $subQ->whereNull('status_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
            })->count();

        // 4. Total Dokumen Terlambat (memiliki deadline dan sudah lewat deadline, belum selesai)
        $totalTerlambat = (clone $baseQuery)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
            ->where(function ($subQ) {
                $subQ->whereNull('status_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
            })->count();

        // Get documents list with pagination
        $documentsQuery = (clone $baseQuery)->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $documentsQuery->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $documentsQuery->where('tahun', $request->year);
        }

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $documentsQuery->where('bagian', $selectedBagian);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        // Filter by statistic card (total, selesai, proses, terlambat)
        $statFilter = $request->get('stat_filter', '');
        if ($statFilter === 'selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($statFilter === 'proses') {
            $documentsQuery->where(function ($q) {
                $q->where('status', 'sedang diproses')
                    ->orWhere('status', 'sent_to_team_verifikasi')
                    ->orWhere('status', 'sent_to_perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere('status', 'proses_Team Verifikasi')
                    ->orWhere('status', 'sent_to_perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'pending_approval_team_verifikasi');
            })->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        } elseif ($statFilter === 'terlambat') {
            // Dokumen terlambat: memiliki deadline yang sudah lewat, belum selesai, dan belum terkirim ke perpajakan/akutansi
            $documentsQuery->whereNotNull('deadline_at')
                ->where('deadline_at', '<', $now)
                ->whereNotIn('status', [
                    'selesai',
                    'approved_data_sudah_terkirim',
                    'completed',
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                ])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        }
        // If stat_filter is 'total' or empty, show all documents (no additional filter)

        $dokumens = $documentsQuery->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        $statFilter = $request->get('stat_filter', '');

        return view('owner.rekapanDetail', compact('type', 'typeNames', 'totalDokumen', 'totalSelesai', 'totalProses', 'totalTerlambat', 'dokumens', 'availableYears', 'bagianList', 'selectedBagian', 'completionFilter', 'statFilter'))
            ->with('title', 'Detail ' . $typeNames[$type])
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display rekapan keterlambatan for owner
     * Updated to use dokumen_role_data table for per-role deadline tracking
     */
    public function rekapanKeterlambatan(Request $request)
    {
        // Redirect langsung ke submenu Team Verifikasi dengan mempertahankan query parameters
        $queryParams = $request->query();
        return redirect()->route('owner.rekapan-keterlambatan.role', [
            'roleCode' => 'team_verifikasi'
        ] + $queryParams);
    }

    /**
     * Export rekapan keterlambatan per role ke Excel (XML Spreadsheet format)
     * Supports multiple worksheets - one per month when "Semua Bulan" is selected
     * Supports status filter: aman, peringatan, terlambat, or all (empty)
     */
    public function exportRekapanKeterlambatan(Request $request, $roleCode)
    {
        // Validate roleCode
        $validRoles = ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        if (!in_array($roleCode, $validRoles)) {
            abort(404, 'Role tidak ditemukan');
        }

        $year = $request->get('year');
        $month = $request->get('month');
        $statusFilter = $request->get('status'); // aman, peringatan, terlambat, or empty for all

        $roleNames = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
        ];

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $roleName = $roleNames[$roleCode] ?? $roleCode;
        $statusSuffix = $statusFilter ? '_' . strtoupper($statusFilter) : '';
        $filename = 'Rekapan_Keterlambatan_' . str_replace(' ', '_', $roleName) . $statusSuffix . '_' . now()->format('Y-m-d_H-i') . '.xls';

        // Deadline thresholds per role (in HOURS - must match rekapan page logic)
        // Non-pembayaran: AMAN < 24h, PERINGATAN 24-72h, TERLAMBAT >= 72h
        // Pembayaran: AMAN < 168h (1 week), PERINGATAN 168-504h (1-3 weeks), TERLAMBAT >= 504h
        $deadlineThresholds = [
            'team_verifikasi' => [24, 72],    // hours
            'perpajakan' => [24, 72],         // hours
            'akutansi' => [24, 72],           // hours
            'pembayaran' => [168, 504],       // hours (weekly)
        ];

        $thresholds = $deadlineThresholds[$roleCode] ?? [24, 72];
        $isWeekly = $roleCode === 'pembayaran';
        $now = \Carbon\Carbon::now();

        // If specific month is selected, export single sheet (old behavior)
        if ($month) {
            return $this->exportSingleSheetRekapan($roleCode, $roleName, $year, $month, $monthNames, $thresholds, $isWeekly, $now, $filename, $statusFilter);
        }

        // Multi-sheet export: one sheet per month
        $displayYear = $year ?? now()->year;

        // Get all data for the year first, then group by month
        $query = DokumenRoleData::where('role_code', $roleCode)
            ->whereNotNull('received_at')
            ->with(['dokumen']);

        if ($year) {
            $query->whereYear('received_at', $year);
        }

        $allRoleData = $query->orderBy('received_at', 'asc')->get();

        // Group data by month
        $dataByMonth = [];
        foreach ($allRoleData as $roleData) {
            $receivedMonth = \Carbon\Carbon::parse($roleData->received_at)->month;
            if (!isset($dataByMonth[$receivedMonth])) {
                $dataByMonth[$receivedMonth] = [];
            }
            $dataByMonth[$receivedMonth][] = $roleData;
        }

        // Build Excel XML with multiple worksheets
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10"/>
  </Style>
  <Style ss:ID="Title">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="14" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#2E7D32" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="Header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#1976D2" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Cell">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="CellCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="CellRight">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusAman">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#4CAF50" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusPeringatan">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#000000"/>
   <Interior ss:Color="#FFC107" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="StatusTerlambat">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#F44336" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Summary">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
   <Interior ss:Color="#E3F2FD" ss:Pattern="Solid"/>
  </Style>
 </Styles>
';

        // Create a worksheet for each month (1-12)
        for ($m = 1; $m <= 12; $m++) {
            $sheetName = $monthNames[$m] . ' ' . $displayYear;
            $monthData = $dataByMonth[$m] ?? [];

            $xml .= ' <Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">
  <Table ss:DefaultColumnWidth="100">
   <Column ss:Width="40"/>
   <Column ss:Width="120"/>
   <Column ss:Width="80"/>
   <Column ss:Width="60"/>
   <Column ss:Width="80"/>
   <Column ss:Width="150"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="180"/>
   <Column ss:Width="250"/>
   <Column ss:Width="120"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="130"/>
   <Row ss:Height="30">
    <Cell ss:StyleID="Title" ss:MergeAcross="14"><Data ss:Type="String">REKAPAN KETERLAMBATAN - ' . strtoupper($roleName) . ' (' . strtoupper($sheetName) . ')</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nomor Agenda</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Bulan</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Tahun</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Bagian</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nomor SPP</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal SPP</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Masuk</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Dibayar Kepada</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Uraian SPP</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nilai Rupiah</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Durasi</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Status Deadline</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Status Proses</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Selesai</Data></Cell>
   </Row>
';

            if (count($monthData) > 0) {
                $no = 1;
                foreach ($monthData as $roleData) {
                    $dokumen = $roleData->dokumen;
                    if (!$dokumen)
                        continue;

                    $receivedAt = \Carbon\Carbon::parse($roleData->received_at);
                    $diffInMinutes = $receivedAt->diffInMinutes($now);
                    $ageHours = $diffInMinutes / 60; // Convert to hours for threshold comparison

                    // Calculate duration for display
                    $days = floor($diffInMinutes / (60 * 24));
                    $hours = floor(($diffInMinutes % (60 * 24)) / 60);
                    $minutes = $diffInMinutes % 60;
                    $durationParts = [];
                    if ($days > 0)
                        $durationParts[] = $days . ' hari';
                    if ($hours > 0)
                        $durationParts[] = $hours . ' jam';
                    if ($minutes > 0 || empty($durationParts))
                        $durationParts[] = $minutes . ' menit';
                    $duration = implode(' ', $durationParts);

                    // Determine status using hours (thresholds are in hours)
                    if ($ageHours < $thresholds[0]) {
                        $status = 'AMAN';
                        $statusStyle = 'StatusAman';
                    } elseif ($ageHours < $thresholds[1]) {
                        $status = 'PERINGATAN';
                        $statusStyle = 'StatusPeringatan';
                    } else {
                        $status = 'TERLAMBAT';
                        $statusStyle = 'StatusTerlambat';
                    }

                    // Filter by status if specified
                    if ($statusFilter) {
                        $statusLower = strtolower($status);
                        if ($statusLower !== strtolower($statusFilter)) {
                            continue; // Skip this document
                        }
                    }

                    $completedAt = $roleData->processed_at;
                    $isCompleted = !is_null($completedAt);
                    $prosesStatus = $isCompleted ? 'Selesai' : 'Sedang Diproses';
                    $completedDate = $isCompleted ? \Carbon\Carbon::parse($completedAt)->format('d/m/Y H:i') : '-';

                    // Derive bulan/tahun from tanggal_masuk or created_at
                    $docDate = $dokumen->tanggal_masuk ?? $dokumen->created_at;
                    $bulanDoc = $docDate ? \Carbon\Carbon::parse($docDate)->format('F') : '-';
                    $tahunDoc = $docDate ? \Carbon\Carbon::parse($docDate)->format('Y') : '-';
                    $tglSpp = $dokumen->tanggal_spp ? \Carbon\Carbon::parse($dokumen->tanggal_spp)->format('d/m/Y') : '-';
                    $tglMasuk = $docDate ? \Carbon\Carbon::parse($docDate)->format('d/m/Y') : '-';

                    $xml .= '   <Row>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $no++ . '</Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String">' . htmlspecialchars($dokumen->nomor_agenda ?? '-') . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $bulanDoc . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $tahunDoc . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . htmlspecialchars($dokumen->bagian ?? '-') . '</Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String">' . htmlspecialchars($dokumen->nomor_spp ?? '-') . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $tglSpp . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $tglMasuk . '</Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String">' . htmlspecialchars($dokumen->dibayar_kepada ?? '-') . '</Data></Cell>
    <Cell ss:StyleID="Cell"><Data ss:Type="String">' . htmlspecialchars($dokumen->uraian_spp ?? '-') . '</Data></Cell>
    <Cell ss:StyleID="CellRight"><Data ss:Type="String">' . ($dokumen->nilai_rupiah ? 'Rp ' . number_format($dokumen->nilai_rupiah, 0, ',', '.') : '-') . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $duration . '</Data></Cell>
    <Cell ss:StyleID="' . $statusStyle . '"><Data ss:Type="String">' . $status . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $prosesStatus . '</Data></Cell>
    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $completedDate . '</Data></Cell>
   </Row>
';
                }

                // Summary row
                $xml .= '   <Row>
    <Cell ss:StyleID="Summary" ss:MergeAcross="4"><Data ss:Type="String">Total Dokumen: ' . ($no - 1) . '</Data></Cell>
    <Cell ss:StyleID="Summary" ss:MergeAcross="9"><Data ss:Type="String"></Data></Cell>
   </Row>
';
            } else {
                // No data message
                $xml .= '   <Row>
    <Cell ss:StyleID="Cell" ss:MergeAcross="14"><Data ss:Type="String">Tidak ada data untuk bulan ini</Data></Cell>
   </Row>
';
            }

            $xml .= '  </Table>
 </Worksheet>
';
        }

        $xml .= '</Workbook>';

        return response($xml)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Helper: Export single sheet rekapan (when specific month is selected)
     */
    private function exportSingleSheetRekapan($roleCode, $roleName, $year, $month, $monthNames, $thresholds, $isWeekly, $now, $filename, $statusFilter = null)
    {
        $query = DokumenRoleData::where('role_code', $roleCode)
            ->whereNotNull('received_at')
            ->with(['dokumen']);

        if ($year) {
            $query->whereYear('received_at', $year);
        }
        if ($month) {
            $query->whereMonth('received_at', $month);
        }

        $roleDataList = $query->orderBy('received_at', 'asc')->get();

        // Build HTML table that Excel can read
        $statusLabel = $statusFilter ? ' - ' . strtoupper($statusFilter) : '';
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        .title { background-color: #2E7D32; color: white; font-size: 18px; font-weight: bold; text-align: center; }
        .subtitle { background-color: #E8F5E9; color: #666; font-size: 12px; text-align: center; font-style: italic; }
        .header { background-color: #1976D2; color: white; font-weight: bold; text-align: center; }
        .row-even { background-color: #F5F5F5; }
        .status-aman { background-color: #4CAF50; color: white; font-weight: bold; text-align: center; }
        .status-peringatan { background-color: #FFC107; color: black; font-weight: bold; text-align: center; }
        .status-terlambat { background-color: #F44336; color: white; font-weight: bold; text-align: center; }
        .proses-selesai { background-color: #4CAF50; color: white; text-align: center; }
        .proses-pending { background-color: #2196F3; color: white; text-align: center; }
        .summary { background-color: #E3F2FD; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>
<table>
    <tr>
        <td colspan="15" class="title">REKAPAN KETERLAMBATAN - ' . strtoupper($roleName) . $statusLabel . '</td>
    </tr>
    <tr>
        <td colspan="15" class="subtitle">Diekspor: ' . now()->format('d/m/Y H:i') . ($year ? ' | Tahun: ' . $year : '') . ($month ? ' | Bulan: ' . ($monthNames[$month] ?? $month) : '') . ($statusFilter ? ' | Status: ' . strtoupper($statusFilter) : '') . '</td>
    </tr>
    <tr><td colspan="15"></td></tr>
    <tr class="header">
        <th>No</th>
        <th>Nomor Agenda</th>
        <th>Bulan</th>
        <th>Tahun</th>
        <th>Bagian</th>
        <th>Nomor SPP</th>
        <th>Tanggal SPP</th>
        <th>Tanggal Masuk</th>
        <th>Dibayar Kepada</th>
        <th>Uraian SPP</th>
        <th>Nilai Rupiah</th>
        <th>Durasi</th>
        <th>Status Deadline</th>
        <th>Status Proses</th>
        <th>Tanggal Selesai</th>
    </tr>';

        $no = 1;
        foreach ($roleDataList as $roleData) {
            $dokumen = $roleData->dokumen;
            if (!$dokumen)
                continue;

            $receivedAt = \Carbon\Carbon::parse($roleData->received_at);

            // Determine end time based on completion status
            // Match display logic: use processed_at for completed documents to create "permanent" age
            // This ensures consistency between display and export
            $processedAt = $roleData->processed_at;
            $endTime = $processedAt ? \Carbon\Carbon::parse($processedAt) : $now;

            // Calculate age using correct end time (processed_at for completed, now for active)
            $diffInMinutes = $receivedAt->diffInMinutes($endTime);
            $ageHours = $diffInMinutes / 60; // Convert to hours for threshold comparison

            // Calculate duration for display
            $days = floor($diffInMinutes / (60 * 24));
            $hours = floor(($diffInMinutes % (60 * 24)) / 60);
            $minutes = $diffInMinutes % 60;
            $durationParts = [];
            if ($days > 0)
                $durationParts[] = $days . ' hari';
            if ($hours > 0)
                $durationParts[] = $hours . ' jam';
            if ($minutes > 0 || empty($durationParts))
                $durationParts[] = $minutes . ' menit';
            $duration = implode(' ', $durationParts);

            // Determine status using hours (thresholds are in hours)
            if ($ageHours < $thresholds[0]) {
                $status = 'AMAN';
                $statusClass = 'status-aman';
            } elseif ($ageHours < $thresholds[1]) {
                $status = 'PERINGATAN';
                $statusClass = 'status-peringatan';
            } else {
                $status = 'TERLAMBAT';
                $statusClass = 'status-terlambat';
            }

            // Filter by status if specified
            if ($statusFilter) {
                $statusLower = strtolower($status);
                if ($statusLower !== strtolower($statusFilter)) {
                    continue; // Skip this document
                }
            }

            $completedAt = $roleData->processed_at;
            $isCompleted = !is_null($completedAt);
            $prosesClass = $isCompleted ? 'proses-selesai' : 'proses-pending';
            $rowClass = ($no % 2 == 0) ? 'row-even' : '';

            // Derive bulan/tahun from tanggal_masuk or created_at
            $docDate = $dokumen->tanggal_masuk ?? $dokumen->created_at;
            $bulanDoc = $docDate ? \Carbon\Carbon::parse($docDate)->format('F') : '-';
            $tahunDoc = $docDate ? \Carbon\Carbon::parse($docDate)->format('Y') : '-';
            $tglSpp = $dokumen->tanggal_spp ? \Carbon\Carbon::parse($dokumen->tanggal_spp)->format('d/m/Y') : '-';
            $tglMasuk = $docDate ? \Carbon\Carbon::parse($docDate)->format('d/m/Y') : '-';

            $html .= '
    <tr class="' . $rowClass . '">
        <td class="center">' . $no++ . '</td>
        <td>' . htmlspecialchars($dokumen->nomor_agenda ?? '-') . '</td>
        <td class="center">' . $bulanDoc . '</td>
        <td class="center">' . $tahunDoc . '</td>
        <td class="center">' . htmlspecialchars($dokumen->bagian ?? '-') . '</td>
        <td>' . htmlspecialchars($dokumen->nomor_spp ?? '-') . '</td>
        <td class="center">' . $tglSpp . '</td>
        <td class="center">' . $tglMasuk . '</td>
        <td>' . htmlspecialchars($dokumen->dibayar_kepada ?? '-') . '</td>
        <td>' . htmlspecialchars($dokumen->uraian_spp ?? '-') . '</td>
        <td class="right">' . ($dokumen->nilai_rupiah ? 'Rp ' . number_format($dokumen->nilai_rupiah, 0, ',', '.') : '-') . '</td>
        <td class="center">' . $duration . '</td>
        <td class="' . $statusClass . '">' . $status . '</td>
        <td class="' . $prosesClass . '">' . ($isCompleted ? 'Selesai' : 'Sedang Diproses') . '</td>
        <td class="center">' . ($isCompleted ? \Carbon\Carbon::parse($completedAt)->format('d/m/Y H:i') : '-') . '</td>
    </tr>';
        }

        $html .= '
    <tr><td colspan="15"></td></tr>
    <tr class="summary">
        <td colspan="5">Total Dokumen: ' . ($no - 1) . '</td>
        <td colspan="10"></td>
    </tr>
</table>
</body>
</html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Get statistics for rekapan
     */
    private function getRekapanStatistics($filterBagian = '')
    {
        $query = Dokumen::query();

        if ($filterBagian) {
            $query->where('bagian', $filterBagian);
        }

        $total = $query->count();

        // Count completed documents (status = 'selesai' or 'approved_data_sudah_terkirim' or current_handler = 'pembayaran')
        $completedQuery = Dokumen::query();
        if ($filterBagian) {
            $completedQuery->where('bagian', $filterBagian);
        }
        $completedCount = $completedQuery->where(function ($q) {
            $q->where('status', 'selesai')
                ->orWhere('status', 'approved_data_sudah_terkirim')
                ->orWhere('current_handler', 'pembayaran');
        })->count();

        // Count documents by handler
        $operatorQuery = Dokumen::query();
        $ibuYuniQuery = Dokumen::query();
        $perpajakanQuery = Dokumen::query();
        $akutansiQuery = Dokumen::query();

        if ($filterBagian) {
            $operatorQuery->where('bagian', $filterBagian);
            $ibuYuniQuery->where('bagian', $filterBagian);
            $perpajakanQuery->where('bagian', $filterBagian);
            $akutansiQuery->where('bagian', $filterBagian);
        }

        // Ibu Tarapul: dokumen dengan current_handler = 'operator' atau status draft
        $operatorCount = $operatorQuery->where(function ($q) {
            $q->where('current_handler', 'operator')
                ->orWhere(function ($subQ) {
                    $subQ->where('status', 'draft')
                        ->where(function ($subSubQ) {
                            $subSubQ->whereNull('current_handler')
                                ->orWhere('current_handler', 'operator');
                        });
                });
        })->count();

        // Ibu Yuni: dokumen dengan current_handler = 'team_verifikasi' atau status sent_to_team_verifikasi
        $ibuYuniCount = $ibuYuniQuery->where(function ($q) {
            $q->where('current_handler', 'team_verifikasi')
                ->orWhere('status', 'sent_to_team_verifikasi')
                ->orWhere('status', 'pending_approval_team_verifikasi')
                ->orWhere('status', 'proses_Team Verifikasi');
        })->count();

        // Team Perpajakan: dokumen dengan current_handler = 'perpajakan' atau status sent_to_perpajakan
        $perpajakanCount = $perpajakanQuery->where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'proses_perpajakan');
        })->count();

        // Team Akutansi: dokumen dengan current_handler = 'akutansi' atau status sent_to_akutansi
        $akutansiCount = $akutansiQuery->where(function ($q) {
            $q->where('current_handler', 'akutansi')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'proses_akutansi');
        })->count();

        return [
            'total_documents' => $total,
            'completed_documents' => $completedCount,
            'ibu_tarapul' => $operatorCount,
            'ibu_yuni' => $ibuYuniCount,
            'perpajakan' => $perpajakanCount,
            'akutansi' => $akutansiCount
        ];
    }

    /**
     * Get document detail for owner recap page
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Load required relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return HTML partial for detail view
        $html = $this->generateDocumentDetailHtml($dokumen);

        return response($html);
    }

    /**
     * Generate HTML for document detail with all data (initial, perpajakan, akutansi)
     */
    private function generateDocumentDetailHtml($dokumen)
    {
        $html = '<div class="detail-grid">';

        // Document Information Section (Basic Data - Data Awal)
        $detailItems = [
            'Tanggal Masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
            'Bulan' => $dokumen->bulan ?? '-',
            'Tahun' => $dokumen->tahun ?? '-',
            'No SPP' => $dokumen->nomor_spp ?? '-',
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah ?? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
            'Kategori' => $dokumen->kategori ?? '-',
            'Jenis Dokumen' => $dokumen->jenis_dokumen ?? '-',
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
            'Kebun' => $dokumen->kebun ?? '-',
            'Bagian' => $dokumen->bagian ?? '-',
            'Dibayar Kepada' => $dokumen->dibayarKepadas->count() > 0
                ? htmlspecialchars($dokumen->dibayarKepadas->pluck('nama_penerima')->join(', '))
                : ($dokumen->dibayar_kepada ?? '-'),
            'No Berita Acara' => $dokumen->no_berita_acara ?? '-',
            'Tanggal Berita Acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-',
            'No SPK' => $dokumen->no_spk ?? '-',
            'Tanggal SPK' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-',
            'Tanggal Akhir SPK' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-',
            'No PO' => $dokumen->dokumenPos->count() > 0 ? htmlspecialchars($dokumen->dokumenPos->pluck('nomor_po')->join(', ')) : '-',
            'No PR' => $dokumen->dokumenPrs->count() > 0 ? htmlspecialchars($dokumen->dokumenPrs->pluck('nomor_pr')->join(', ')) : '-',
            'Nomor Miro' => $dokumen->nomor_miro ?? '-',
            'Status' => $this->getStatusDisplayName($dokumen->status),
            'Current Handler' => $this->getRoleDisplayName($dokumen->current_handler),
        ];

        foreach ($detailItems as $label => $value) {
            $html .= sprintf('
                <div class="detail-item">
                    <span class="detail-label">%s</span>
                    <span class="detail-value">%s</span>
                </div>',
                htmlspecialchars($label),
                $value
            );
        }

        $html .= '</div>';

        // Check if document has perpajakan data
        $hasPerpajakanData = !empty($dokumen->npwp) || !empty($dokumen->no_faktur) ||
            !empty($dokumen->tanggal_faktur) || !empty($dokumen->jenis_pph) ||
            !empty($dokumen->dpp_pph) || !empty($dokumen->ppn_terhutang) ||
            !empty($dokumen->link_dokumen_pajak) || !empty($dokumen->status_perpajakan);

        if ($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi' || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'akutansi' || $dokumen->current_handler == 'pembayaran') {
            // Visual Separator for Perpajakan Data
            $html .= '<div class="detail-section-separator">
                <div class="separator-content">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Data Perpajakan</span>
                    <span class="tax-badge">DITAMBAHKAN OLEH TEAM PERPAJAKAN</span>
                </div>
            </div>';

            // Perpajakan Information Section
            $html .= '<div class="detail-grid tax-section">';

            $taxFields = [
                'NPWP' => $dokumen->npwp ?: '<span class="empty-field">Belum diisi</span>',
                'Status Perpajakan' => $this->formatTaxStatus($dokumen->status_perpajakan),
                'No Faktur' => $dokumen->no_faktur ?: '<span class="empty-field">Belum diisi</span>',
                'Tanggal Faktur' => $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '<span class="empty-field">Belum diisi</span>',
                'Tanggal Selesai Verifikasi Pajak' => $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d/m/Y') : '<span class="empty-field">Belum diisi</span>',
                'Jenis PPh' => $dokumen->jenis_pph ?: '<span class="empty-field">Belum diisi</span>',
                'DPP PPh' => $dokumen->dpp_pph ? 'Rp ' . number_format($dokumen->dpp_pph, 0, ',', '.') : '<span class="empty-field">Belum diisi</span>',
                'PPN Terhutang' => $dokumen->ppn_terhutang ? 'Rp ' . number_format($dokumen->ppn_terhutang, 0, ',', '.') : '<span class="empty-field">Belum diisi</span>',
                'Link Dokumen Pajak' => $this->formatTaxDocumentLink($dokumen->link_dokumen_pajak),
            ];

            foreach ($taxFields as $label => $value) {
                $html .= sprintf('
                    <div class="detail-item tax-field">
                        <span class="detail-label">%s</span>
                        <span class="detail-value">%s</span>
                    </div>',
                    htmlspecialchars($label),
                    $value
                );
            }

            $html .= '</div>';
        }

        // Data Akutansi Section - Show if document has akutansi data or sent to pembayaran
        $hasAkutansiData = !empty($dokumen->nomor_miro);
        if ($hasAkutansiData || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'pembayaran') {
            $html .= '<div class="detail-section-separator">
                <div class="separator-content">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Data Akutansi</span>
                    <span class="tax-badge" style="background: rgba(255, 255, 255, 0.2);">DITAMBAHKAN OLEH TEAM AKUTANSI</span>
                </div>
            </div>';

            // Akutansi Information Section
            $html .= '<div class="detail-grid tax-section">';

            $akutansiFields = [
                'Nomor MIRO' => $dokumen->nomor_miro ?: '<span class="empty-field">Belum diisi</span>',
            ];

            foreach ($akutansiFields as $label => $value) {
                $html .= sprintf('
                    <div class="detail-item tax-field">
                        <span class="detail-label">%s</span>
                        <span class="detail-value">%s</span>
                    </div>',
                    htmlspecialchars($label),
                    $value
                );
            }

            $html .= '</div>';
        }

        // Data Pembayaran Section - Show if document has payment data or status_pembayaran is set
        $hasPembayaranData = !empty($dokumen->link_bukti_pembayaran) || !empty($dokumen->status_pembayaran);
        if ($hasPembayaranData || $dokumen->current_handler == 'pembayaran' || $dokumen->status == 'sent_to_pembayaran') {
            // Get status_pembayaran and link_bukti_pembayaran from database if not in model
            $statusPembayaran = $dokumen->status_pembayaran ?? \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
            $linkBuktiPembayaran = $dokumen->link_bukti_pembayaran ?? \DB::table('dokumens')->where('id', $dokumen->id)->value('link_bukti_pembayaran');

            $html .= '<div class="detail-section-separator">
                <div class="separator-content" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745;">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Data Pembayaran</span>
                    <span class="tax-badge" style="background: rgba(255, 255, 255, 0.2);">DITAMBAHKAN OLEH TEAM PEMBAYARAN</span>
                </div>
            </div>';

            // Pembayaran Information Section
            $html .= '<div class="detail-grid tax-section">';

            $pembayaranFields = [
                'Status Pembayaran' => $statusPembayaran ? ucfirst(str_replace('_', ' ', $statusPembayaran)) : '<span class="empty-field">Belum diisi</span>',
                'Link Bukti Pembayaran' => $linkBuktiPembayaran
                    ? sprintf(
                        '<a href="%s" target="_blank" class="tax-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
                        htmlspecialchars($linkBuktiPembayaran),
                        htmlspecialchars($linkBuktiPembayaran)
                    )
                    : '<span class="empty-field">Belum diisi</span>',
            ];

            foreach ($pembayaranFields as $label => $value) {
                $html .= sprintf('
                    <div class="detail-item tax-field">
                        <span class="detail-label">%s</span>
                        <span class="detail-value">%s</span>
                    </div>',
                    htmlspecialchars($label),
                    $value
                );
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Format tax status with badge
     */
    private function formatTaxStatus($status)
    {
        if (!$status) {
            return '<span class="empty-field">Belum diisi</span>';
        }

        $statusLabel = $status == 'selesai' ? 'Selesai' : 'Sedang Diproses';
        $badgeClass = $status == 'selesai' ? 'badge-selesai' : 'badge-proses';

        return sprintf('<span class="badge %s">%s</span>', $badgeClass, $statusLabel);
    }

    /**
     * Format tax document link
     */
    private function formatTaxDocumentLink($link)
    {
        if (!$link) {
            return '<span class="empty-field">Belum diisi</span>';
        }

        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return sprintf(
                '<a href="%s" target="_blank" class="tax-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
                htmlspecialchars($link),
                htmlspecialchars($link)
            );
        }

        return htmlspecialchars($link);
    }

    /**
     * Get filter data for dropdowns
     */
    private function getFilterData(): array
    {
        // Get distinct bagian values
        $bagianList = Dokumen::whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->distinct()
            ->orderBy('bagian')
            ->pluck('bagian', 'bagian')
            ->toArray();

        // Get distinct vendor/dibayar_kepada values
        $vendorList = [];
        try {
            // From dibayar_kepadas table
            $vendorList = DB::table('dibayar_kepadas')
                ->whereNotNull('nama_penerima')
                ->where('nama_penerima', '!=', '')
                ->distinct()
                ->orderBy('nama_penerima')
                ->pluck('nama_penerima', 'nama_penerima')
                ->toArray();

            // Also include from dokumens.dibayar_kepada column (legacy)
            $legacyVendors = Dokumen::whereNotNull('dibayar_kepada')
                ->where('dibayar_kepada', '!=', '')
                ->distinct()
                ->pluck('dibayar_kepada', 'dibayar_kepada')
                ->toArray();

            $vendorList = array_merge($vendorList, $legacyVendors);
            $vendorList = array_unique($vendorList);
            asort($vendorList);
        } catch (\Exception $e) {
            \Log::error('Error fetching vendor list: ' . $e->getMessage());
        }

        // Get distinct kebun values
        $kebunList = Dokumen::whereNotNull('kebun')
            ->where('kebun', '!=', '')
            ->distinct()
            ->orderBy('kebun')
            ->pluck('kebun', 'kebun')
            ->toArray();

        // Get Kriteria CF, Sub Kriteria, Item Sub Kriteria from cash_bank database
        $kriteriaCfList = [];
        $subKriteriaList = [];
        $itemSubKriteriaList = [];

        try {
            $kriteriaCfList = \App\Models\KategoriKriteria::on('cash_bank')
                ->where('tipe', 'Keluar')
                ->orderBy('nama_kriteria')
                ->pluck('nama_kriteria', 'id_kategori_kriteria')
                ->toArray();

            $subKriteriaList = \App\Models\SubKriteria::on('cash_bank')
                ->orderBy('nama_sub_kriteria')
                ->pluck('nama_sub_kriteria', 'id_sub_kriteria')
                ->toArray();

            $itemSubKriteriaList = \App\Models\ItemSubKriteria::on('cash_bank')
                ->orderBy('nama_item_sub_kriteria')
                ->pluck('nama_item_sub_kriteria', 'id_item_sub_kriteria')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error fetching kriteria data from cash_bank: ' . $e->getMessage());
        }

        return [
            'bagian' => $bagianList,
            'vendor' => $vendorList,
            'kriteria_cf' => $kriteriaCfList,
            'sub_kriteria' => $subKriteriaList,
            'item_sub_kriteria' => $itemSubKriteriaList,
            'kebun' => $kebunList,
        ];
    }

    /**
     * Rekapan keterlambatan per role
     */
    public function rekapanKeterlambatanByRole(Request $request, $roleCode)
    {
        $now = Carbon::now();

        // Validasi role
        $validRoles = ['operator', 'team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'];
        if (!in_array($roleCode, $validRoles)) {
            abort(404, 'Role tidak ditemukan');
        }

        // Access control: Check if user can access this role's recap
        // Admin/Owner can view any role. Regular users can only view their own role.
        $user = auth()->user();
        $userRole = strtolower($user->role ?? '');
        $isAdminOrOwner = in_array($userRole, ['admin', 'owner']);

        if (!$isAdminOrOwner) {
            // Map user role to roleCode format
            $userRoleMapping = [
                'operator' => 'operator',
                'team_verifikasi' => 'team_verifikasi',
                'team_verifikasi' => 'team_verifikasi',
                'perpajakan' => 'perpajakan',
                'akutansi' => 'akutansi',
                'pembayaran' => 'pembayaran',
            ];
            $userRoleCode = $userRoleMapping[$userRole] ?? null;

            // Check if user is trying to access their own role's recap
            if ($userRoleCode !== $roleCode) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        }


        // Role configuration
        $roleConfig = [
            'operator' => ['name' => 'Ibu Tara', 'code' => 'operator'],
            'team_verifikasi' => ['name' => 'Team Verifikasi', 'code' => 'team_verifikasi'],
            'perpajakan' => ['name' => 'Team Perpajakan', 'code' => 'perpajakan'],
            'akutansi' => ['name' => 'Team Akutansi', 'code' => 'akutansi'],
            'pembayaran' => ['name' => 'Pembayaran', 'code' => 'pembayaran'],
        ];


        // Query dokumen berdasarkan role dengan umur dokumen sejak received_at
        // Untuk pembayaran, gunakan sama seperti role lainnya dengan dokumen_role_data
        if ($roleCode === 'pembayaran') {
            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
                ->leftJoin('dokumen_role_data', function ($join) use ($roleCode) {
                    $join->on('dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                        ->where('dokumen_role_data.role_code', '=', $roleCode);
                })
                // Show ALL documents that have been received by pembayaran (including sudah_dibayar)
                ->whereNotNull('dokumen_role_data.received_at')
                ->select(
                    'dokumens.*',
                    'dokumen_role_data.role_code as delay_role_code',
                    'dokumen_role_data.received_at as delay_received_at',
                    'dokumen_role_data.processed_at as delay_processed_at',
                    'dokumen_role_data.deadline_at as delay_deadline_at'
                );
        } else {
            // Query ALL documents that have ever been in this role (permanent tracking)
            // This shows both active documents AND completed documents (already sent to next role)
            // Active = current_handler is still this role
            // Completed = current_handler has moved to another role

            // Filter by status: 'active', 'completed', or 'all' (default)
            $statusFilter = $request->get('status_filter', 'all');

            // Map role code to expected handler
            $roleHandlerMapping = [
                'team_verifikasi' => 'team_verifikasi',
                'perpajakan' => 'perpajakan',
                'akutansi' => 'akutansi',
            ];
            $expectedHandler = $roleHandlerMapping[$roleCode] ?? $roleCode;

            // Query based on dokumen_role_data.received_at (permanent record)
            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
                ->join('dokumen_role_data', 'dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                ->whereRaw('LOWER(dokumen_role_data.role_code) = ?', [strtolower($roleCode)])
                ->whereNotNull('dokumen_role_data.received_at');

            // Apply status filter based on current_handler (not processed_at)
            // Active = document is still being handled by this role
            // Completed = document has moved to another role
            if ($statusFilter === 'active') {
                $query->whereRaw('LOWER(dokumens.current_handler) = ?', [strtolower($expectedHandler)]);
            } elseif ($statusFilter === 'completed') {
                $query->whereRaw('LOWER(dokumens.current_handler) != ?', [strtolower($expectedHandler)]);
            }
            // 'all' = no additional filter

            $query->select(
                'dokumens.*',
                'dokumen_role_data.role_code as delay_role_code',
                'dokumen_role_data.received_at as delay_received_at',
                'dokumen_role_data.processed_at as delay_processed_at',
                'dokumen_role_data.deadline_at as delay_deadline_at'
            );
        }




        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('dokumens.nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('dokumens.nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('dokumens.uraian_spp', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('dokumens.tahun', $request->year);
        }

        // Filter by month from received_at
        if ($request->has('month') && $request->month) {
            $query->whereMonth('dokumen_role_data.received_at', $request->month);
        }

        // Apply advanced filters (similar to owner dashboard)
        if ($request->has('filter_bagian') && $request->filter_bagian) {
            $query->where('dokumens.bagian', $request->filter_bagian);
        }

        if ($request->has('filter_vendor') && $request->filter_vendor) {
            $vendorSearch = $request->filter_vendor;
            $query->where(function ($q) use ($vendorSearch) {
                // Check legacy dibayar_kepada column on dokumens table
                $q->where('dokumens.dibayar_kepada', 'like', '%' . $vendorSearch . '%')
                    // Check related dibayar_kepadas table (new structure)
                    ->orWhereExists(function ($subQ) use ($vendorSearch) {
                        $subQ->select(\DB::raw(1))
                            ->from('dibayar_kepadas')
                            ->whereColumn('dibayar_kepadas.dokumen_id', 'dokumens.id')
                            ->where('dibayar_kepadas.nama_penerima', 'like', '%' . $vendorSearch . '%');
                    });
            });
        }

        if ($request->has('filter_kriteria_cf') && $request->filter_kriteria_cf) {
            try {
                $kriteria = \App\Models\KategoriKriteria::on('cash_bank')->find($request->filter_kriteria_cf);
                if ($kriteria) {
                    $query->where('dokumens.kategori', $kriteria->nama_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_kriteria_cf: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_sub_kriteria') && $request->filter_sub_kriteria) {
            try {
                $subKriteria = \App\Models\SubKriteria::on('cash_bank')->find($request->filter_sub_kriteria);
                if ($subKriteria) {
                    $query->where('dokumens.jenis_dokumen', $subKriteria->nama_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_sub_kriteria: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_item_sub_kriteria') && $request->filter_item_sub_kriteria) {
            try {
                $itemSubKriteria = \App\Models\ItemSubKriteria::on('cash_bank')->find($request->filter_item_sub_kriteria);
                if ($itemSubKriteria) {
                    $query->where('dokumens.jenis_sub_pekerjaan', $itemSubKriteria->nama_item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_item_sub_kriteria: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_kebun') && $request->filter_kebun) {
            $query->where('dokumens.kebun', $request->filter_kebun);
        }

        // Order by received_at
        // Sort by nomor_agenda descending (highest first), then by received_at descending
        $query->orderByRaw("CASE 
            WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
            ELSE 0
        END DESC")
            ->orderBy('dokumens.nomor_agenda', 'DESC')
            ->orderBy('dokumen_role_data.received_at', 'desc');

        // Debug: Log SQL query and count before get()
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, SQL Query: " . $query->toSql());
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Query Bindings: " . json_encode($query->getBindings()));
        $countBeforeGet = $query->count();
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Count before get(): {$countBeforeGet}");

        // Get all documents first to calculate age and filter by age
        $allDokumens = $query->get();

        // Debug: Log count of documents retrieved
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Documents retrieved: " . $allDokumens->count());

        // Calculate age for each document and filter by age if needed
        $filterAge = $request->get('filter_age');
        $filteredDokumens = $allDokumens->map(function ($dokumen) use ($now, $roleCode) {
            // Get received_at and processed_at from the joined dokumen_role_data
            $receivedAt = null;
            $processedAt = null;

            // Check if processed_at exists (completed document)
            if (isset($dokumen->delay_processed_at) && $dokumen->delay_processed_at) {
                $processedAt = Carbon::parse($dokumen->delay_processed_at);
            }

            // Get received_at
            if (isset($dokumen->delay_received_at) && $dokumen->delay_received_at) {
                $receivedAt = Carbon::parse($dokumen->delay_received_at);
            }
            // Load roleData relationship as fallback
            else {
                if (!$dokumen->relationLoaded('roleData')) {
                    $dokumen->load('roleData');
                }
                $roleCodeLower = strtolower($roleCode);
                $roleData = $dokumen->roleData->first(function ($rd) use ($roleCodeLower) {
                    return strtolower($rd->role_code) === $roleCodeLower;
                });
                if ($roleData) {
                    if ($roleData->received_at) {
                        $receivedAt = Carbon::parse($roleData->received_at);
                    }
                    if ($roleData->processed_at) {
                        $processedAt = Carbon::parse($roleData->processed_at);
                    }
                }
            }

            // If still no received_at, try direct query
            if (!$receivedAt) {
                $roleDataDirect = \App\Models\DokumenRoleData::where('dokumen_id', $dokumen->id)
                    ->whereRaw('LOWER(role_code) = ?', [strtolower($roleCode)])
                    ->whereNotNull('received_at')
                    ->first();
                if ($roleDataDirect) {
                    $receivedAt = Carbon::parse($roleDataDirect->received_at);
                    if ($roleDataDirect->processed_at) {
                        $processedAt = Carbon::parse($roleDataDirect->processed_at);
                    }
                }
            }

            // IMPORTANT: Determine completion status correctly
            // For pembayaran role: a document is completed if status_pembayaran = 'sudah_dibayar'
            // For other roles: document is completed if processed_at is set AND current_handler has moved
            $roleHandlerMapping = [
                'team_verifikasi' => 'team_verifikasi',
                'perpajakan' => 'perpajakan',
                'akutansi' => 'akutansi',
            ];
            $expectedHandler = $roleHandlerMapping[$roleCode] ?? $roleCode;
            $currentHandler = $dokumen->current_handler ?? '';

            // Special handling for pembayaran role
            if ($roleCode === 'pembayaran') {
                // Document is completed if payment status is 'sudah_dibayar'
                $isCompleted = ($dokumen->status_pembayaran === 'sudah_dibayar');
            } else {
                // Document is completed if processed_at exists AND current_handler has moved on
                $isCompleted = ($processedAt !== null) && (strtolower($currentHandler) !== strtolower($expectedHandler));
            }

            // Calculate age based on completion status
            // - Active documents: compare received_at to NOW (time keeps running)
            // - Completed documents: compare received_at to processed_at (PERMANENT time)
            // Note: For pembayaran with sudah_dibayar status, processedAt may be null - use now()
            $endTime = ($isCompleted && $processedAt) ? $processedAt : $now;

            $ageHours = $receivedAt ? $receivedAt->diffInHours($endTime) : 0;
            $ageMinutes = $receivedAt ? $receivedAt->diffInMinutes($endTime) : 0;
            $ageDays = ($receivedAt && $endTime) ? $endTime->diffInDays($receivedAt, false) : 0;
            $ageDays = max(0, $ageDays);

            $dokumen->is_completed = $isCompleted;
            $dokumen->age_days = $ageDays;
            $dokumen->age_hours = $ageHours;
            $dokumen->age_formatted = $this->formatAge($ageDays, $ageHours, $ageMinutes);
            $dokumen->effective_received_at = $receivedAt ? $receivedAt->format('Y-m-d H:i:s') : null;
            $dokumen->effective_processed_at = $processedAt ? $processedAt->format('Y-m-d H:i:s') : null;

            return $dokumen;
        });


        // Filter by age if filter_age is set (using hours to match dashboard)
        // Pembayaran uses weekly thresholds, others use daily
        if ($filterAge) {
            $filteredDokumens = $filteredDokumens->filter(function ($dokumen) use ($filterAge, $roleCode) {
                $ageHours = $dokumen->age_hours ?? 0;

                // Pembayaran: weekly thresholds (1 week = 168 hours, 3 weeks = 504 hours)
                if ($roleCode === 'pembayaran') {
                    if ($filterAge === '1') {
                        // AMAN: < 1 week (168 hours)
                        return $ageHours < 168;
                    } elseif ($filterAge === '2') {
                        // PERINGATAN: 1-3 weeks (168-504 hours)
                        return $ageHours >= 168 && $ageHours < 504;
                    } elseif ($filterAge === '3+') {
                        // TERLAMBAT: > 3 weeks (504+ hours)
                        return $ageHours >= 504;
                    }
                } else {
                    // Other roles: daily thresholds (24 hours, 72 hours)
                    if ($filterAge === '1') {
                        // AMAN: < 24 hours
                        return $ageHours < 24;
                    } elseif ($filterAge === '2') {
                        // PERINGATAN: 24-72 hours
                        return $ageHours >= 24 && $ageHours < 72;
                    } elseif ($filterAge === '3+') {
                        // TERLAMBAT: > 72 hours
                        return $ageHours >= 72;
                    }
                }
                return true;
            });
        }


        // Paginate the filtered results
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        $currentPage = $request->get('page', 1);
        $currentItems = $filteredDokumens->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $dokumens = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredDokumens->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Calculate card statistics berdasarkan umur dokumen
        // Include ALL documents (active + completed) and use permanent time for completed
        $cardStats = [];

        // Calculate card statistics hanya untuk role yang memerlukan card
        if (in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi'])) {
            $roleCodeLower = strtolower($roleCode);

            // Get ALL documents that have ever been in this role
            $allRoleDocsQuery = DokumenRoleData::where('dokumen_role_data.role_code', $roleCode)
                ->whereNotNull('dokumen_role_data.received_at')
                ->join('dokumens', 'dokumen_role_data.dokumen_id', '=', 'dokumens.id');

            // Apply year filter to card stats if present
            if ($request->has('year') && $request->year) {
                $allRoleDocsQuery->where('dokumens.tahun', $request->year);
            }

            // Apply month filter to card stats if present
            if ($request->has('month') && $request->month) {
                $allRoleDocsQuery->whereMonth('dokumen_role_data.received_at', $request->month);
            }

            $allRoleDocs = $allRoleDocsQuery->select('dokumen_role_data.*')->get();

            // Card 1: Dokumen dengan umur < 24 jam (hijau) - AMAN
            $card1Count = 0;
            // Card 2: Dokumen dengan umur 24-72 jam (kuning) - PERINGATAN
            $card2Count = 0;
            // Card 3: Dokumen dengan umur > 72 jam (merah) - TERLAMBAT
            $card3Count = 0;

            foreach ($allRoleDocs as $doc) {
                $receivedAt = Carbon::parse($doc->received_at);

                // Use processed_at as end time for completed documents (PERMANENT)
                // Use now for active documents (time keeps running)
                $endTime = $doc->processed_at ? Carbon::parse($doc->processed_at) : $now;
                $hoursDiff = $receivedAt->diffInHours($endTime);

                if ($hoursDiff < 24) {
                    $card1Count++;
                } elseif ($hoursDiff < 72) {
                    $card2Count++;
                } else {
                    $card3Count++;
                }
            }
        } elseif ($roleCode === 'pembayaran') {
            // Pembayaran: weekly thresholds (1 week = 168 hours, 3 weeks = 504 hours)
            // Include ALL documents that have been received by pembayaran (including sudah_dibayar)
            $allRoleDocsQuery = DokumenRoleData::where('dokumen_role_data.role_code', $roleCode)
                ->whereNotNull('dokumen_role_data.received_at')
                ->join('dokumens', 'dokumen_role_data.dokumen_id', '=', 'dokumens.id');

            // Apply year filter to card stats if present
            if ($request->has('year') && $request->year) {
                $allRoleDocsQuery->where('dokumens.tahun', $request->year);
            }

            // Apply month filter to card stats if present
            if ($request->has('month') && $request->month) {
                $allRoleDocsQuery->whereMonth('dokumen_role_data.received_at', $request->month);
            }

            $allRoleDocs = $allRoleDocsQuery->select('dokumen_role_data.*', 'dokumens.status_pembayaran')->get();

            // Card 1: < 1 minggu (< 168 jam) - AMAN
            $card1Count = 0;
            // Card 2: 1-3 minggu (168-504 jam) - PERINGATAN
            $card2Count = 0;
            // Card 3: > 3 minggu (> 504 jam) - TERLAMBAT
            $card3Count = 0;

            foreach ($allRoleDocs as $doc) {
                $receivedAt = Carbon::parse($doc->received_at);

                // For paid documents, use processed_at as end time (PERMANENT) if available
                // For active documents, use now (time keeps running)
                if ($doc->status_pembayaran === 'sudah_dibayar' && $doc->processed_at) {
                    // Document is paid with processed_at - use permanent time
                    $endTime = Carbon::parse($doc->processed_at);
                } else {
                    $endTime = $now;
                }

                $hoursDiff = $receivedAt->diffInHours($endTime);

                if ($hoursDiff < 168) {
                    $card1Count++;
                } elseif ($hoursDiff < 504) {
                    $card2Count++;
                } else {
                    $card3Count++;
                }
            }
        } else {
            // Untuk role lain (Operator), set default values
            $card1Count = 0;
            $card2Count = 0;
            $card3Count = 0;
        }



        // Only set cardStats for roles that need cards
        if (in_array($roleCode, ['team_verifikasi', 'perpajakan', 'akutansi'])) {
            $cardStats = [
                'card1' => [
                    'count' => $card1Count,
                    'label' => '1 Hari',
                    'color' => 'green',
                ],
                'card2' => [
                    'count' => $card2Count,
                    'label' => '2 Hari',
                    'color' => 'yellow',
                ],
                'card3' => [
                    'count' => $card3Count,
                    'label' => '3+ Hari',
                    'color' => 'red',
                ],
            ];
        } elseif ($roleCode === 'pembayaran') {
            $cardStats = [
                'card1' => [
                    'count' => $card1Count,
                    'label' => '< 1 Minggu',
                    'color' => 'green',
                ],
                'card2' => [
                    'count' => $card2Count,
                    'label' => '1-3 Minggu',
                    'color' => 'yellow',
                ],
                'card3' => [
                    'count' => $card3Count,
                    'label' => '> 3 Minggu',
                    'color' => 'red',
                ],
            ];
        } else {
            // Empty cardStats for roles that don't need cards
            $cardStats = [
                'card1' => ['count' => 0, 'label' => '-', 'color' => 'green'],
                'card2' => ['count' => 0, 'label' => '-', 'color' => 'yellow'],
                'card3' => ['count' => 0, 'label' => '-', 'color' => 'red'],
            ];
        }

        // Calculate total statistics
        if ($roleCode === 'pembayaran') {
            $totalDocuments = Dokumen::where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere('status', 'proses_pembayaran');
            })
                ->where(function ($q) {
                    $q->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->count();
        } else {
            $totalDocuments = DokumenRoleData::where('role_code', $roleCode)
                ->whereNotNull('received_at')
                ->whereNull('processed_at')
                ->count();
        }

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Get available months (1-12)
        $availableMonths = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];


        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        return view('owner.rekapanKeterlambatanByRole', compact(
            'dokumens',
            'cardStats',
            'totalDocuments',
            'availableYears',
            'availableMonths',
            'roleConfig',
            'roleCode',
            'filterData'
        ))
            ->with('title', 'Rekapan Keterlambatan - ' . $roleConfig[$roleCode]['name'])
            ->with('module', $isAdminOrOwner ? 'owner' : ($roleCode === 'team_verifikasi' ? 'team_verifikasi' : ($roleCode === 'operator' ? 'operator' : $roleCode)))
            ->with('menuDashboard', '')
            ->with('menuRekapan', '')
            ->with('menuRekapKeterlambatan', 'active')
            ->with('dashboardUrl', $isAdminOrOwner ? '/owner/dashboard' : ($roleCode === 'team_verifikasi' ? '/dashboard/verifikasi' : '/dashboard'));
    }


    /**
     * Format age in days and hours to readable format
     * Uses total hours to calculate days for more accurate display
     */
    private function formatAge($days, $hours = 0, $totalMinutes = 0)
    {
        // Calculate days and remaining hours from total hours
        // This is more reliable than using separate $days parameter
        $calculatedDays = floor($hours / 24);
        $remainingHours = $hours % 24;

        // Use the calculated days from hours for consistency
        $actualDays = max($days, $calculatedDays);

        if ($actualDays == 0 && $remainingHours == 0) {
            // Show minutes when less than 1 hour
            $remainingMinutes = $totalMinutes % 60;
            if ($remainingMinutes > 0) {
                return $remainingMinutes . ' menit';
            }
            return 'Baru';
        } elseif ($actualDays == 0) {
            return $remainingHours . ' jam';
        } elseif ($actualDays == 1 && $remainingHours == 0) {
            return '1 hari';
        } elseif ($actualDays == 1) {
            return '1 hari ' . $remainingHours . ' jam';
        } elseif ($actualDays < 30) {
            if ($remainingHours > 0) {
                return $actualDays . ' hari ' . $remainingHours . ' jam';
            }
            return $actualDays . ' hari';
        } elseif ($actualDays == 30) {
            return '1 bulan';
        } elseif ($actualDays % 30 == 0) {
            return ($actualDays / 30) . ' bulan';
        } else {
            $months = floor($actualDays / 30);
            $remainingDays = $actualDays % 30;
            if ($months > 0 && $remainingDays > 0) {
                return $months . ' bulan ' . $remainingDays . ' hari';
            } elseif ($months > 0) {
                return $months . ' bulan';
            } else {
                return $actualDays . ' hari';
            }
        }
    }

    // =========================================================================
    //  URGENCY ALERT METHODS
    // =========================================================================

    /**
     * POST /owner/dokumen/{id}/urgency
     * Send an urgency alert for a document. Only admin/owner can call this.
     */
    public function sendUrgency(Request $request, $id): JsonResponse
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            // Prevent sending if urgency is already active
            if ($dokumen->urgency_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi urgency sudah aktif untuk dokumen ini. Tunggu hingga dokumen diselesaikan.',
                ], 422);
            }

            $dokumen->update([
                'urgency_active'  => true,
                'urgency_sent_at' => now(),
                'urgency_sent_by' => auth()->id(),
            ]);

            \Log::info('Urgency alert sent', [
                'dokumen_id'   => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'handler'      => $dokumen->current_handler,
                'sent_by'      => auth()->user()?->name,
                'sent_at'      => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success'               => true,
                'message'               => 'Pengingat urgency berhasil dikirim!',
                'urgency_sent_at_human' => now()->diffForHumans(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            \Log::error('Error sending urgency: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'], 500);
        }
    }

    /**
     * DELETE /owner/dokumen/{id}/urgency
     * Reset (deactivate) urgency for a document.
     */
    public function resetUrgency(Request $request, $id): JsonResponse
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            $dokumen->update([
                'urgency_active'  => false,
                'urgency_sent_at' => null,
                'urgency_sent_by' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Urgency berhasil direset.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            \Log::error('Error resetting urgency: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.'], 500);
        }
    }

    /**
     * GET /api/documents/urgency/active
     * Returns urgency-active documents for the logged-in user's role.
     * Used by recipient-role dashboards for polling.
     */
    public function getActiveUrgencies(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $role = strtolower($user->role ?? '');

            // Map role to current_handler values stored in dokumens
            $roleHandlerMap = [
                'operator'        => ['operator'],
                'team_verifikasi' => ['team_verifikasi', 'verifikasi'],
                'verifikasi'      => ['team_verifikasi', 'verifikasi'],
                'perpajakan'      => ['perpajakan'],
                'akutansi'        => ['akutansi'],
                'pembayaran'      => ['pembayaran'],
            ];

            $handlers = $roleHandlerMap[$role] ?? [];

        // If the role is not in the handler map, return empty results
        // This prevents non-recipient roles (e.g., programmer, owner) from seeing ALL urgency documents
        if (empty($handlers)) {
            return response()->json([
                'success'   => true,
                'count'     => 0,
                'urgencies' => [],
            ]);
        }

        $query = Dokumen::where('urgency_active', true)
            ->select('id', 'nomor_agenda', 'nomor_spp', 'current_handler', 'urgency_sent_at', 'urgency_sent_by')
            ->whereIn('current_handler', $handlers);

            $urgencies = $query->orderBy('urgency_sent_at', 'asc')->get()->map(function ($dok) {
                return [
                    'id'            => $dok->id,
                    'nomor_agenda'  => $dok->nomor_agenda,
                    'nomor_spp'     => $dok->nomor_spp,
                    'sent_at_human' => $dok->urgency_sent_at ? $dok->urgency_sent_at->diffForHumans() : null,
                    'sent_at'       => $dok->urgency_sent_at ? $dok->urgency_sent_at->toISOString() : null,
                ];
            });

            return response()->json([
                'success'   => true,
                'count'     => $urgencies->count(),
                'urgencies' => $urgencies,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching active urgencies: ' . $e->getMessage());
            return response()->json(['success' => false, 'urgencies' => [], 'count' => 0], 500);
        }
    }

    /**
     * GET /owner/dokumen/{id}/history
     * Returns the DocumentTracking timeline for a Dokumen record.
     */
    public function getHistory(int $id): \Illuminate\Http\JsonResponse
    {
        $dokumen = Dokumen::findOrFail($id);

        $entries = \App\Models\DocumentTracking::where('document_id', $id)
            ->orderBy('action_at', 'asc')
            ->get();

        $actorLabels = [
            'operator'       => 'Operator',
            'team_verifikasi'=> 'Team Verifikasi',
            'perpajakan'     => 'Perpajakan',
            'akutansi'       => 'Akutansi',
            'pembayaran'     => 'Pembayaran',
            'system'         => 'System',
        ];

        $actionLabels = [
            'created'                    => 'Dokumen Dibuat',
            'sent_to_team_verifikasi'    => 'Dikirim ke Team Verifikasi',
            'sent_to_perpajakan'         => 'Dikirim ke Perpajakan',
            'sent_to_akutansi'           => 'Dikirim ke Akutansi',
            'sent_to_pembayaran'         => 'Dikirim ke Pembayaran',
            'returned_to_perpajakan'     => 'Dikembalikan ke Perpajakan',
            'returned_to_akutansi'       => 'Dikembalikan ke Akutansi',
            'returned_to_operator'       => 'Dikembalikan ke Operator',
            'returned_to_verifikasi'     => 'Dikembalikan ke Verifikasi',
            'deadline_set'               => 'Deadline Ditetapkan',
            'processed_perpajakan'       => 'Diproses Perpajakan',
            'processed_akutansi'         => 'Diproses Akutansi',
            'processed_pembayaran'       => 'Pembayaran Dikonfirmasi',
            'urgency_sent'               => 'Urgency Dikirim',
        ];

        $actionColors = [
            'created'                 => '#10b981', // green
            'sent_to_team_verifikasi' => '#3b82f6', // blue
            'sent_to_perpajakan'      => '#6366f1', // indigo
            'sent_to_akutansi'        => '#f59e0b', // amber
            'sent_to_pembayaran'      => '#14b8a6', // teal
            'returned_to_perpajakan'  => '#ef4444', // red
            'returned_to_akutansi'    => '#ef4444',
            'returned_to_operator'    => '#ef4444',
            'deadline_set'            => '#f59e0b',
            'processed_perpajakan'    => '#6366f1',
            'processed_akutansi'      => '#f59e0b',
            'processed_pembayaran'    => '#10b981',
            'urgency_sent'            => '#f97316', // orange
        ];

        $actionIcons = [
            'created'                 => 'fa-plus-circle',
            'sent_to_team_verifikasi' => 'fa-paper-plane',
            'sent_to_perpajakan'      => 'fa-file-invoice',
            'sent_to_akutansi'        => 'fa-calculator',
            'sent_to_pembayaran'      => 'fa-money-bill-wave',
            'returned_to_perpajakan'  => 'fa-undo',
            'returned_to_akutansi'    => 'fa-undo',
            'returned_to_operator'    => 'fa-undo',
            'deadline_set'            => 'fa-clock',
            'processed_perpajakan'    => 'fa-stamp',
            'processed_akutansi'      => 'fa-check-double',
            'processed_pembayaran'    => 'fa-check-circle',
            'urgency_sent'            => 'fa-bell',
        ];

        $nodes = $entries->map(function ($entry, $index) use (
            $entries, $actionLabels, $actorLabels, $actionColors, $actionIcons
        ) {
            $next      = $entries->get($index + 1);
            $durationSec = $next
                ? $entry->action_at->diffInSeconds($next->action_at)
                : null;

            return [
                'id'           => $entry->id,
                'action'       => $entry->action,
                'action_label' => $actionLabels[$entry->action] ?? ucfirst(str_replace('_', ' ', $entry->action)),
                'actor'        => $entry->actor,
                'actor_label'  => $actorLabels[$entry->actor] ?? $entry->actor,
                'action_at'    => $entry->action_at->format('d M Y H:i'),
                'action_at_iso'=> $entry->action_at->toISOString(),
                'metadata'     => $entry->metadata ?? [],
                'color'        => $actionColors[$entry->action] ?? '#64748b',
                'icon'         => $actionIcons[$entry->action]  ?? 'fa-circle',
                'duration_sec' => $durationSec,
                'duration_label' => $durationSec !== null ? $this->formatDuration($durationSec) : null,
                'is_last'      => $index === $entries->count() - 1,
            ];
        })->values();

        // Identify slowest stage (for red highlight)
        $maxDuration = $nodes->max('duration_sec');

        $nodes = $nodes->map(function ($node) use ($maxDuration) {
            $node['is_slowest'] = $maxDuration && $node['duration_sec'] === $maxDuration && $maxDuration > 86400;
            return $node;
        });

        // Summary
        $totalSec = $entries->count() > 1
            ? $entries->first()->action_at->diffInSeconds($entries->last()->action_at)
            : null;

        return response()->json([
            'success'       => true,
            'document'      => [
                'id'           => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp'    => $dokumen->nomor_spp,
                'dibayar_kepada' => $dokumen->dibayar_kepada,
                'status'       => $dokumen->status,
            ],
            'nodes'         => $nodes,
            'summary'       => [
                'total_events'  => $entries->count(),
                'total_duration'=> $totalSec ? $this->formatDuration($totalSec) : '-',
                'total_sec'     => $totalSec,
            ],
        ]);
    }

    /** Format seconds into "X hari Y jam Z menit" */
    private function formatDuration(int $seconds): string
    {
        $days    = intdiv($seconds, 86400);
        $hours   = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        $parts = [];
        if ($days)    $parts[] = "{$days} hari";
        if ($hours)   $parts[] = "{$hours} jam";
        if ($minutes) $parts[] = "{$minutes} menit";
        return $parts ? implode(' ', $parts) : 'Kurang dari 1 menit';
    }
}







