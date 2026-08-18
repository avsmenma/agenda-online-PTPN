<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DocumentTracking;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DokumenHelper;
use App\Support\ColumnCustomization;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DashboardPembayaranController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsRoleDashboard;
    use \App\Http\Controllers\Concerns\ExportsDocuments;

    /**
     * Halaman dashboard Pembayaran (gaya workflow, selaras role lain).
     */
    public function dashboard()
    {
        $data = $this->buildRoleDashboardData('pembayaran');
        $data['title'] = 'Dashboard Pembayaran';
        $data['module'] = 'pembayaran';
        $data['menuDashboard'] = 'Active';

        // Kartu 2-4 memakai status pembayaran: belum siap / siap / sudah dibayar.
        $counts = $this->paymentStatusCounts();
        $data['card2Label'] = 'Belum Siap Bayar';
        $data['card2Value'] = $counts['belum'];
        $data['card2Sub']   = 'status: belum siap dibayar';
        $data['card3Label'] = 'Siap Bayar';
        $data['card3Value'] = $counts['siap'];
        $data['card3Sub']   = 'status: siap dibayar';
        $data['card3Color']  = '#f59e0b';   // Siap Bayar → kuning
        $data['card3IconBg'] = '#fffbeb';
        $data['fourthLabel'] = 'Sudah Dibayar';
        $data['fourthCount'] = $counts['sudah'];
        $data['fourthSub']   = 'status: sudah dibayar';
        $data['fourthColor']  = '#10b981';  // Sudah Dibayar → hijau
        $data['fourthIconBg'] = '#ecfdf5';

        return view('dashboard.workflow', $data);
    }

    /**
     * Hitung jumlah dokumen per status pembayaran (keseluruhan, tanpa filter tanggal).
     * Logika identik dengan kartu statistik pada halaman dokumen pembayaran.
     */
    private function paymentStatusCounts(): array
    {
        $base = fn() => Dokumen::whereNotNull('nomor_agenda');

        $sudah = $base()->where(function ($q) {
            $q->whereNotNull('tanggal_dibayar')
                ->orWhereNotNull('link_bukti_pembayaran')
                ->orWhereIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
        })->count();

        $siap = $base()
            ->where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran');
            })
            ->whereNull('tanggal_dibayar')
            ->where(function ($q) {
                $q->whereNull('status_pembayaran')
                    ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
            })
            ->whereNull('link_bukti_pembayaran')
            ->count();

        $total = $base()->count();
        $belum = max(0, $total - $sudah - $siap);

        return ['belum' => $belum, 'siap' => $siap, 'sudah' => $sudah];
    }

    

    public function index(Request $request)
    {
        $now = Carbon::now();

        // Get filter parameters
        $statusPembayaran = request('status_pembayaran');
        $year = request('year');
        $month = request('month', request('filter_bulan'));
        $date = request('date');
        $dateFilter = $this->normalizeDateFilter($date);
        $search = request('search');
        $mode = request('mode', 'normal'); // normal or rekapan_table
        $defaultColumns = $this->getPembayaranDashboardDefaultColumns();
        $selectedColumns = request('columns', []); // Array of selected columns in order
        $selectedColumns = is_array($selectedColumns) ? array_values(array_filter($selectedColumns)) : [];

        if (request()->has('columns') && !empty($selectedColumns)) {
            $user = Auth::user();
            if ($user) {
                $preferences = $user->table_columns_preferences ?? [];
                $preferences['pembayaran_dashboard'] = $selectedColumns;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }

            session(['pembayaran_dashboard_table_columns' => $selectedColumns]);
        } else {
            $user = Auth::user();
            if ($user && isset($user->table_columns_preferences['pembayaran_dashboard'])) {
                $selectedColumns = $user->table_columns_preferences['pembayaran_dashboard'];
            } else {
                $selectedColumns = session('pembayaran_dashboard_table_columns', $defaultColumns);
            }

            $selectedColumns = is_array($selectedColumns) ? array_values(array_filter($selectedColumns)) : $defaultColumns;

            // Saring terhadap katalog: preferensi tersimpan bisa memuat kunci kolom
            // yang sudah DIHAPUS dari katalog, dan kunci basi itu akan dirender
            // sebagai kolom berjudul kunci mentah. Lihat catatan sama di tiga
            // controller peran lain (nomor_kontrak, 2026-08-04).
            $selectedColumns = array_values(array_intersect(
                $selectedColumns,
                array_keys($this->getPembayaranDashboardAvailableColumns())
            ));

            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            session(['pembayaran_dashboard_table_columns' => $selectedColumns]);
        }

        if (!in_array('tanggal_dibayar', $selectedColumns, true)) {
            $selectedColumns[] = 'tanggal_dibayar';
            session(['pembayaran_dashboard_table_columns' => $selectedColumns]);
        }

        // === Konfigurasi kolom beku (frozen) ===
        // Logika bersama 5 role — lihat App\Support\ColumnCustomization.
        $pinnedColumns = ['nomor_agenda'];
        $frozenResolved = ColumnCustomization::resolveFrozen($request, Auth::user(), [
            'available'  => $this->getPembayaranDashboardAvailableColumns(),
            'selected'   => $selectedColumns,
            'default'    => ['left' => $pinnedColumns, 'right' => []],
            'pinnedLeft' => $pinnedColumns,
            'prefKey'    => 'pembayaran_dashboard_frozen',
            'sessionKey' => 'pembayaran_dashboard_frozen_columns',
        ]);
        $frozenColumns = ['left' => $frozenResolved['left'], 'right' => $frozenResolved['right']];
        $frozenLeft = $frozenResolved['left'];
        $frozenRight = $frozenResolved['right'];
        $renderColumns = $frozenResolved['render'];

        // Handler yang dianggap "belum siap dibayar"
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'operator', 'team_verifikasi', 'ibu_a', 'ibu_b'];

        // ============================================
        // STATISTICS CARDS (with Rupiah values)
        // ============================================

        // Helper function to calculate computed status
        $getComputedStatus = function ($doc) use ($belumSiapHandlers) {
            if (
                $doc->tanggal_dibayar ||
                $doc->link_bukti_pembayaran ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH_DIBAYAR' ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar'
            ) {
                return 'sudah_dibayar';
            }
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };

        // OPTIMIZED: Use aggregate DB queries instead of ->get() to prevent memory exhaustion
        $baseStatsFilter = function() use ($year, $month, $dateFilter) {
            $q = Dokumen::whereNotNull('nomor_agenda');
            if ($year) { $q->whereYear('created_at', $year); }
            if ($month) { $this->applyMonthFilter($q, $month); }
            if ($dateFilter) { $q->whereDate('tanggal_masuk', $dateFilter); }
            return $q;
        };

        // Sudah dibayar: has tanggal_dibayar OR link_bukti OR status_pembayaran matches
        $sudahDibayarQuery = $baseStatsFilter()->where(function ($q) {
            $q->whereNotNull('tanggal_dibayar')
              ->orWhereNotNull('link_bukti_pembayaran')
              ->orWhere('status_pembayaran', 'sudah_dibayar')
              ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
              ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR');
        });
        $countSudahDibayar = (clone $sudahDibayarQuery)->count();
        $nilaiSudahDibayar = (clone $sudahDibayarQuery)->sum('nilai_rupiah');

        // Siap dibayar: current_handler=pembayaran OR status=sent_to_pembayaran, AND NOT sudah_dibayar
        $siapDibayarQuery = $baseStatsFilter()->where(function ($q) {
            $q->where('current_handler', 'pembayaran')
              ->orWhere('status', 'sent_to_pembayaran');
        })->where(function ($q) {
            $q->whereNull('tanggal_dibayar');
        })->where(function ($q) {
            $q->whereNull('status_pembayaran')
              ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
        })->whereNull('link_bukti_pembayaran');
        $countSiapDibayar = (clone $siapDibayarQuery)->count();
        $nilaiSiapDibayar = (clone $siapDibayarQuery)->sum('nilai_rupiah');

        // Total
        $totalCount = $baseStatsFilter()->count();
        $totalNilai = $baseStatsFilter()->sum('nilai_rupiah');

        // Belum siap = total - sudah - siap
        $countBelumSiap = $totalCount - $countSudahDibayar - $countSiapDibayar;
        $nilaiBelumSiap = $totalNilai - $nilaiSudahDibayar - $nilaiSiapDibayar;

        $statistics = [
            'total_documents' => $totalCount,
            'total_nilai' => $totalNilai,
            'by_status' => [
                'belum_dibayar' => max(0, $countBelumSiap),
                'siap_dibayar' => $countSiapDibayar,
                'sudah_dibayar' => $countSudahDibayar,
            ],
            'total_nilai_by_status' => [
                'belum_dibayar' => max(0, $nilaiBelumSiap),
                'siap_dibayar' => $nilaiSiapDibayar,
                'sudah_dibayar' => $nilaiSudahDibayar,
            ],
        ];

        // ============================================
        // DEADLINE CARDS (Aman/Peringatan/Terlambat)
        // ============================================
        $totalAman = 0;
        $totalPeringatan = 0;
        $totalTerlambat = 0;

        $allDokumensPembayaran = Dokumen::with(['roleData'])
            ->whereNotNull('nomor_agenda')
            ->where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere('status_pembayaran', 'sudah_dibayar')
                    ->orWhere(function ($csvQ) {
                        $csvQ->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                            $query->where('imported_from_csv', true);
                        });
                    });
            })
            ->select(['id', 'nomor_agenda', 'current_handler', 'status', 'status_pembayaran', 'tanggal_dibayar', 'tanggal_masuk', 'created_at'])
            ->get();

        foreach ($allDokumensPembayaran as $dok) {
            $roleData = $dok->roleData->where('role_code', 'pembayaran')->first();
            $receivedAt = $roleData ? $roleData->received_at : null;
            $processedAt = $roleData ? $roleData->processed_at : null;

            $isCompleted = $dok->status_pembayaran === 'sudah_dibayar';
            if ($isCompleted) {
                $endTime = $processedAt ?? $dok->tanggal_dibayar ?? $now;
            } else {
                $endTime = $now;
            }

            if ($receivedAt) {
                $hoursDiff = Carbon::parse($receivedAt)->diffInHours(Carbon::parse($endTime));
                if ($hoursDiff < 168) {
                    $totalAman++;
                } elseif ($hoursDiff < 504) {
                    $totalPeringatan++;
                } else {
                    $totalTerlambat++;
                }
            } else {
                // Pembayaran memakai tanggal_masuk sebagai jam-mulai sah bila received_at kosong.
                $baseDate = $dok->tanggal_masuk ?? $dok->created_at;
                if ($baseDate) {
                    $hoursDiff = Carbon::parse($baseDate)->diffInHours(Carbon::parse($endTime));
                    if ($hoursDiff < 168) {
                        $totalAman++;
                    } elseif ($hoursDiff < 504) {
                        $totalPeringatan++;
                    } else {
                        $totalTerlambat++;
                    }
                }
                // Tanpa received_at MAUPUN tanggal_masuk/created_at → NETRAL (tidak dihitung)
            }
        }

        // ============================================
        // DOKUMEN LIST WITH PAGINATION
        // ============================================
        $query = Dokumen::with('dibayarKepadas')->whereNotNull('nomor_agenda');

        // Apply status filter
        if ($statusPembayaran) {
            if ($statusPembayaran === 'belum_siap_dibayar') {
                $query->whereIn('current_handler', $belumSiapHandlers);
            } elseif ($statusPembayaran === 'siap_dibayar') {
                $query->where(function ($q) {
                    $q->where('current_handler', 'pembayaran')
                        ->orWhere('status', 'sent_to_pembayaran');
                })->where(function ($q) {
                    $q->whereNull('status_pembayaran')
                        ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
                })->whereNull('tanggal_dibayar');
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                        ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                        ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR')
                        ->orWhereNotNull('tanggal_dibayar');
                });
            }
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $this->applyMonthFilter($query, $month);
        }
        if ($dateFilter) {
            $query->whereDate('tanggal_masuk', $dateFilter);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%")
                    ->orWhere('dibayar_kepada', 'like', "%{$search}%");
            });
        }

        // ============================================
        // ADVANCED FILTER SECTION
        // ============================================
        $filterVendor = request('filter_vendor');
        $filterKategori = request('filter_kategori');
        $filterJenisDokumen = request('filter_jenis_dokumen');
        $filterJenisSubPekerjaan = request('filter_jenis_sub_pekerjaan');
        $filterKebun = request('filter_kebun');
        $filterJenisPembayaran = request('filter_jenis_pembayaran');
        $filterBagian = request('filter_bagian');

        if ($filterVendor) {
            $query->where('dibayar_kepada', $filterVendor);
        }
        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }
        if ($filterJenisDokumen) {
            $query->where('jenis_dokumen', $filterJenisDokumen);
        }
        if ($filterJenisSubPekerjaan) {
            $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
        }
        if ($filterKebun) {
            $query->where(function ($q) use ($filterKebun) {
                $q->where('kebun', $filterKebun)
                    ->orWhere('nama_kebuns', $filterKebun);
            });
        }
        if ($filterJenisPembayaran) {
            $query->where('jenis_pembayaran', $filterJenisPembayaran);
        }
        if ($filterBagian) {
            $query->where('bagian', $filterBagian);
        }

        // Apply rekapan detail filters
        if ($mode === 'rekapan_table') {
            $filterDibayarKepada = request('filter_dibayar_kepada_column');
            if ($filterDibayarKepada) {
                $query->where('dibayar_kepada', $filterDibayarKepada);
            }
            $filterKategori = request('filter_kategori_column');
            if ($filterKategori) {
                $query->where('kategori', $filterKategori);
            }
            $filterJenisDokumen = request('filter_jenis_dokumen_column');
            if ($filterJenisDokumen) {
                $query->where('jenis_dokumen', $filterJenisDokumen);
            }
            $filterJenisSubPekerjaan = request('filter_jenis_sub_pekerjaan_column');
            if ($filterJenisSubPekerjaan) {
                $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
            }
            $filterJenisPembayaran = request('filter_jenis_pembayaran_column');
            if ($filterJenisPembayaran) {
                $query->where('jenis_pembayaran', $filterJenisPembayaran);
            }
            $filterKebun = request('filter_jenis_kebuns_column');
            if ($filterKebun) {
                $query->where(function ($q) use ($filterKebun) {
                    $q->where('kebun', $filterKebun)
                        ->orWhere('nama_kebuns', $filterKebun);
                });
            }
        }

        // For rekapan table mode - group by vendor
        $rekapanByVendor = null;
        if ($mode === 'rekapan_table' && !empty($selectedColumns)) {
            $allDocsForRekapan = $query->orderBy('dibayar_kepada')->get();
            $allDocsForRekapan->each(function ($doc) use ($getComputedStatus) {
                $doc->computed_status = $getComputedStatus($doc);
            });
            $allDocsForRekapan = $allDocsForRekapan->filter(function ($doc) {
                return in_array($doc->computed_status, ['siap_dibayar', 'sudah_dibayar']);
            })->values();

            if ($statusPembayaran && in_array($statusPembayaran, ['siap_dibayar', 'sudah_dibayar'])) {
                $allDocsForRekapan = $allDocsForRekapan->filter(function ($doc) use ($statusPembayaran) {
                    return $doc->computed_status === $statusPembayaran;
                })->values();
            }

            $rekapanByVendor = $allDocsForRekapan->groupBy(function ($doc) {
                return $doc->dibayar_kepada ?: null;
            })->map(function ($docs, $vendor) {
                return [
                    'vendor' => $vendor ?: 'Tidak Diketahui',
                    'documents' => $docs,
                    'total_nilai' => $docs->sum('nilai_rupiah'),
                    'total_belum_dibayar' => $docs->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah'),
                    'total_siap_dibayar' => $docs->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah'),
                    'total_sudah_dibayar' => $docs->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah'),
                    'count' => $docs->count(),
                ];
            });
        }

        $perPage = 50;
        if ($mode === 'rekapan_table') {
            // Get all results first (before pagination) to apply computed_status filter
            $allDokumens = $query->orderBy('created_at', 'desc')->get();
            $allDokumens->each(function ($doc) use ($getComputedStatus) {
                $doc->computed_status = $getComputedStatus($doc);
            });

            // Only filter by status if a specific status is selected
            if ($statusPembayaran && in_array($statusPembayaran, ['belum_siap_dibayar', 'siap_dibayar', 'sudah_dibayar'])) {
                $allDokumens = $allDokumens->filter(function ($doc) use ($statusPembayaran) {
                    return $doc->computed_status === $statusPembayaran;
                })->values();
            }

            // Paginate manually
            $currentPage = request()->get('page', 1);
            $perPage = request()->get('per_page', session('pembayaran_per_page', 15));
            if ($perPage === 'all') {
                $perPage = 999999;
            } else {
                $perPage = in_array((int) $perPage, [10, 15, 25, 50, 100]) ? (int) $perPage : 15;
            }
            session(['pembayaran_per_page' => $perPage]);
            $total = $allDokumens->count();
            $currentItems = $allDokumens->slice(($currentPage - 1) * $perPage, $perPage)->values();
        } else {
            $currentPage = 1;
            $total = (clone $query)->count();
            $currentItems = collect();
        }

        $dokumens = new LengthAwarePaginator(
            $currentItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->except('page')
            ]
        );

        // ============================================
        // DROPDOWN DATA FOR FILTERS
        // ============================================
        $availableYears = Dokumen::whereNotNull('nomor_agenda')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $createFilteredQuery = function () use ($year, $month, $dateFilter) {
            $q = Dokumen::whereNotNull('nomor_agenda');
            if ($year) {
                $q->whereYear('created_at', $year);
            }
            if ($month) {
                $this->applyMonthFilter($q, $month);
            }
            if ($dateFilter) {
                $q->whereDate('tanggal_masuk', $dateFilter);
            }
            return $q;
        };

        $availableDibayarKepada = $createFilteredQuery()
            ->whereNotNull('dibayar_kepada')
            ->where('dibayar_kepada', '!=', '')
            ->selectRaw('DISTINCT dibayar_kepada')
            ->orderBy('dibayar_kepada')
            ->pluck('dibayar_kepada', 'dibayar_kepada');

        $availableKategori = $createFilteredQuery()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->selectRaw('DISTINCT kategori')
            ->orderBy('kategori')
            ->pluck('kategori', 'kategori');

        $availableJenisDokumen = $createFilteredQuery()
            ->whereNotNull('jenis_dokumen')
            ->where('jenis_dokumen', '!=', '')
            ->selectRaw('DISTINCT jenis_dokumen')
            ->orderBy('jenis_dokumen')
            ->pluck('jenis_dokumen', 'jenis_dokumen');

        $availableJenisSubPekerjaan = $createFilteredQuery()
            ->whereNotNull('jenis_sub_pekerjaan')
            ->where('jenis_sub_pekerjaan', '!=', '')
            ->selectRaw('DISTINCT jenis_sub_pekerjaan')
            ->orderBy('jenis_sub_pekerjaan')
            ->pluck('jenis_sub_pekerjaan', 'jenis_sub_pekerjaan');

        $availableJenisPembayaran = $createFilteredQuery()
            ->whereNotNull('jenis_pembayaran')
            ->where('jenis_pembayaran', '!=', '')
            ->selectRaw('DISTINCT jenis_pembayaran')
            ->orderBy('jenis_pembayaran')
            ->pluck('jenis_pembayaran', 'jenis_pembayaran');

        $kebunFromKebun = $createFilteredQuery()
            ->whereNotNull('kebun')
            ->where('kebun', '!=', '')
            ->distinct()
            ->pluck('kebun', 'kebun');

        $kebunFromNamaKebuns = $createFilteredQuery()
            ->whereNotNull('nama_kebuns')
            ->where('nama_kebuns', '!=', '')
            ->distinct()
            ->pluck('nama_kebuns', 'nama_kebuns');

        $availableKebuns = $kebunFromKebun->merge($kebunFromNamaKebuns)->unique()->sortKeys();

        $availableBagians = $createFilteredQuery()
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->selectRaw('DISTINCT bagian')
            ->orderBy('bagian')
            ->pluck('bagian', 'bagian');

        // Available columns for rekapan table
        $availableColumns = $this->getPembayaranDashboardAvailableColumns();
        [$ieKategoriList, $ieSubKriteriaList, $ieItemSubKriteriaList, $ieJenisPembayaranList] = $this->getPembayaranInlineEditOptions();

        $data = [
            'title' => 'Dashboard Pembayaran',
            'module' => 'pembayaran',
            'menuDashboard' => 'Active',
            'menuDokumen' => '',
            // Statistics
            'statistics' => $statistics,
            // Deadline cards
            'totalAman' => $totalAman,
            'totalPeringatan' => $totalPeringatan,
            'totalTerlambat' => $totalTerlambat,
            // Dokumen list
            'dokumens' => $dokumens,
            'perPage' => $perPage,
            // Filters
            'selectedStatus' => $statusPembayaran,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'selectedDate' => $dateFilter ?? $date,
            'search' => $search,
            'availableYears' => $availableYears,
            'mode' => $mode,
            'selectedColumns' => $selectedColumns,
            'frozenColumns' => $frozenColumns,
            'pinnedColumns' => $pinnedColumns,
            'availableColumns' => $availableColumns,
            'frozenLeft' => $frozenLeft,
            'frozenRight' => $frozenRight,
            'renderColumns' => $renderColumns,
            'rekapanByVendor' => $rekapanByVendor,
            // Dropdown data
            'availableDibayarKepada' => $availableDibayarKepada,
            'availableKategori' => $availableKategori,
            'availableJenisDokumen' => $availableJenisDokumen,
            'availableJenisSubPekerjaan' => $availableJenisSubPekerjaan,
            'availableJenisPembayaran' => $availableJenisPembayaran,
            'availableKebuns' => $availableKebuns,
            'availableBagians' => $availableBagians,
            'ieKategoriList' => $ieKategoriList,
            'ieSubKriteriaList' => $ieSubKriteriaList,
            'ieItemSubKriteriaList' => $ieItemSubKriteriaList,
            'ieJenisPembayaranList' => $ieJenisPembayaranList,
        ];

        return view('pembayaranNEW.dashboardPembayaran', $data);
    }

    /**
     * Pembangun query dasar tabel Tabulator pembayaran (Rollout 4). Memakai
     * ulang logika filter buildPembayaranDashboardQuery() (status_pembayaran/
     * year/month/date/vendor/kategori/dll) — TERMASUK dokumen hasil import CSV,
     * karena pembayaran adalah SATU-SATUNYA role yang tidak mengecualikan CSV
     * (paritas index():338, lihat juga catatan di PembayaranDocumentRow).
     * Menambah eager-load yang dibutuhkan DocumentRow::baseRow() +
     * PembayaranDocumentRow (dibayarKepadas, dokumenPos, roleData, roleStatuses)
     * agar nol N+1 per baris, dan sort default created_at desc, id desc.
     */
    private function buildPembayaranQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->buildPembayaranDashboardQuery($request);

        $query->with([
            'dibayarKepadas',
            'dokumenPos',
            'roleData' => fn ($q) => $q->where('role_code', 'pembayaran'),
            'roleStatuses',
        ]);

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        return $query;
    }

    /**
     * Endpoint JSON progressive-load Tabulator pembayaran. Mirror bentuk
     * DashboardPerpajakanController::datatable() — {last_page,total,data}.
     */
    public function datatableTabulator(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildPembayaranQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        // Opsi pengurus dokumen kini DIISI (dulu selalu []) karena kolom "Pengurus
        // Dokumen" dihidupkan untuk Pembayaran — mereka satu-satunya role yang melihat
        // dokumen TANPA filter current_handler (termasuk hasil impor CSV), jadi justru
        // paling butuh tahu posisi tiap dokumen. Peta bagian dibangun sekali per-request.
        $bagianMap = \App\Support\HandlerOptions::bagianMap();

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\PembayaranDocumentRow::fromDokumen(
                $d,
                \App\Support\HandlerOptions::forDokumen(
                    $d->bagian,
                    $bagianMap,
                    'pembayaran',
                    \App\Support\DocumentRow::handlerTampilanMentah($d)
                ),
                'pembayaran'
            ))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    private function buildPembayaranDashboardQuery(Request $request, bool $includeSearch = true)
    {
        $statusPembayaran = $request->get('status_pembayaran');
        $year = $request->get('year');
        $month = $request->get('month', $request->get('filter_bulan'));
        $dateFilter = $this->normalizeDateFilter($request->get('date'));
        $search = $request->get('filter_search', $request->get('search'));
        $search = is_array($search) ? '' : $search;
        $query = Dokumen::whereNotNull('nomor_agenda');
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'operator', 'team_verifikasi', 'ibu_a', 'ibu_b'];

        if ($statusPembayaran) {
            if ($statusPembayaran === 'belum_siap_dibayar') {
                $query->where(function ($q) use ($belumSiapHandlers) {
                    $q->whereIn('current_handler', $belumSiapHandlers)
                        ->whereNull('tanggal_dibayar')
                        ->whereNull('link_bukti_pembayaran')
                        ->where(function ($statusQ) {
                            $statusQ->whereNull('status_pembayaran')
                                ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
                        });
                });
            } elseif ($statusPembayaran === 'siap_dibayar') {
                $query->where(function ($q) {
                    $q->where('current_handler', 'pembayaran')
                        ->orWhere('status', 'sent_to_pembayaran');
                })->whereNull('tanggal_dibayar')
                    ->whereNull('link_bukti_pembayaran')
                    ->where(function ($q) {
                        $q->whereNull('status_pembayaran')
                            ->orWhereNotIn('status_pembayaran', ['sudah_dibayar', 'SUDAH DIBAYAR', 'SUDAH_DIBAYAR']);
                    });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                        ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                        ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR')
                        ->orWhereNotNull('tanggal_dibayar')
                        ->orWhereNotNull('link_bukti_pembayaran');
                });
            }
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $this->applyMonthFilter($query, $month);
        }
        if ($dateFilter) {
            $query->whereDate('tanggal_masuk', $dateFilter);
        }

        $filterVendor = $request->get('filter_vendor');
        $filterKategori = $request->get('filter_kategori');
        $filterJenisDokumen = $request->get('filter_jenis_dokumen');
        $filterJenisSubPekerjaan = $request->get('filter_jenis_sub_pekerjaan');
        $filterKebun = $request->get('filter_kebun');
        $filterJenisPembayaran = $request->get('filter_jenis_pembayaran');
        $filterBagian = $request->get('filter_bagian');

        if ($filterVendor) {
            $query->where('dibayar_kepada', $filterVendor);
        }
        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }
        if ($filterJenisDokumen) {
            $query->where('jenis_dokumen', $filterJenisDokumen);
        }
        if ($filterJenisSubPekerjaan) {
            $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
        }
        if ($filterKebun) {
            $query->where(function ($q) use ($filterKebun) {
                $q->where('kebun', $filterKebun)
                    ->orWhere('nama_kebuns', $filterKebun);
            });
        }
        if ($filterJenisPembayaran) {
            $query->where('jenis_pembayaran', $filterJenisPembayaran);
        }
        if ($filterBagian) {
            $query->where('bagian', $filterBagian);
        }

        if ($includeSearch && $search) {
            $this->applyPembayaranDashboardSearch($query, $search);
        }

        return $query;
    }

    private function applyPembayaranDashboardSearch($query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('nomor_agenda', 'like', "%{$search}%")
                ->orWhere('nomor_spp', 'like', "%{$search}%")
                ->orWhere('uraian_spp', 'like', "%{$search}%")
                ->orWhere('dibayar_kepada', 'like', "%{$search}%");
        });
    }

    private function getPembayaranDashboardDefaultColumns(): array
    {
        return [
            'nomor_agenda',
            'nomor_spp',
            'dibayar_kepada',
            'uraian_spp',
            'nilai_rupiah',
            'status_pembayaran',
            'tanggal_dibayar',
            'link',
        ];
    }

    private function getSavedPembayaranDashboardColumns(): array
    {
        $defaultColumns = $this->getPembayaranDashboardDefaultColumns();
        $user = Auth::user();

        if ($user && isset($user->table_columns_preferences['pembayaran_dashboard'])) {
            $columns = $user->table_columns_preferences['pembayaran_dashboard'];
        } else {
            $columns = session('pembayaran_dashboard_table_columns', $defaultColumns);
        }

        $columns = is_array($columns) ? array_values(array_filter($columns)) : $defaultColumns;

        if (empty($columns)) {
            return $defaultColumns;
        }

        if (!in_array('tanggal_dibayar', $columns, true)) {
            $columns[] = 'tanggal_dibayar';
        }

        return $columns;
    }

    private function getPembayaranDashboardAvailableColumns(): array
    {
        return [
            'nomor_agenda' => 'Nomor Agenda',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'kategori' => 'Kriteria CF',
            'jenis_dokumen' => 'Sub Kriteria',
            'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'nomor_spp' => 'No SPP',
            'tanggal_spp' => 'TGL SPP',
            'tanggal_masuk' => 'TGL Masuk',
            'dibayar_kepada' => 'Nama Vendor/Dibayar Kepada',
            'uraian_spp' => 'Uraian SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'tanggal_paraf' => 'Tanggal Paraf',
            'pemaraf' => 'Pemaraf',
            'tanggal_selesai_diproses' => 'Tgl Selesai Diproses',
            'tanggal_kembali_ke_bagian' => 'Tgl Kembali ke Bagian',
            'tanggal_hasil_koreksi_bagian' => 'Tgl Hasil Koreksi Bagian',
            'kepala_sub_bagian' => 'Kepala Sub Bagian',
            'keterangan' => 'Keterangan',
            'status_dokumen_custom' => 'Status Dokumen',
            'status_pembayaran' => 'Status Pembayaran',
            'tanggal_dibayar' => 'Tanggal Bayar',
            'bagian' => 'Bagian',
            'link' => 'Link',
            'nama_pengirim' => 'Nama Pengirim',
            'no_spk' => 'Nomor SPK',
            'tanggal_spk' => 'TGL SPK',
            'tanggal_berakhir_spk' => 'TGL Berakhir SPK',
            'no_berita_acara' => 'Nomor BA',
            'tanggal_berita_acara' => 'TGL BA',
            'nomor_po' => 'No PO',
            'nomor_mirror' => 'Nomor Miro',
            'no_faktur' => 'No Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tgl Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPH Terhutang',
            'tanggal_berakhir_ba' => 'TGL Akhir BA',
            'kebun' => 'Kebun',
            'umur_dokumen_tanggal_masuk' => 'Umur(tgl Msk)',
            'umur_dokumen_tanggal_spp' => 'Umur(Tgl SPP)',
            'umur_dokumen_tanggal_ba' => 'Umur(Tgl BA)',
            'npwp' => 'NPWP',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
        ];
    }

    private function getPembayaranInlineEditOptions(): array
    {
        $ieKategoriList = $ieSubKriteriaList = $ieItemSubKriteriaList = $ieJenisPembayaranList = [];

        try {
            $ieKategoriList = \App\Models\KategoriKriteria::where('tipe', 'Keluar')
                ->get(['id_kategori_kriteria as id', 'nama_kriteria'])
                ->toArray();
            $ieSubKriteriaList = \App\Models\SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])
                ->toArray();
            $ieItemSubKriteriaList = \App\Models\ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])
                ->toArray();
            $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')
                ->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('Pembayaran inline edit options fallback: ' . $e->getMessage());
        }

        if (empty($ieKategoriList)) {
            $ieKategoriList = Dokumen::whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->distinct()
                ->orderBy('kategori')
                ->pluck('kategori')
                ->map(fn($value) => ['id' => $value, 'nama_kriteria' => $value])
                ->toArray();
        }
        if (empty($ieSubKriteriaList)) {
            $ieSubKriteriaList = Dokumen::whereNotNull('jenis_dokumen')
                ->where('jenis_dokumen', '!=', '')
                ->distinct()
                ->orderBy('jenis_dokumen')
                ->get(['jenis_dokumen', 'kategori'])
                ->unique('jenis_dokumen')
                ->map(fn($dokumen) => [
                    'id' => $dokumen->jenis_dokumen,
                    'nama_sub_kriteria' => $dokumen->jenis_dokumen,
                    'id_kategori_kriteria' => $dokumen->kategori,
                ])
                ->values()
                ->toArray();
        }
        if (empty($ieItemSubKriteriaList)) {
            $ieItemSubKriteriaList = Dokumen::whereNotNull('jenis_sub_pekerjaan')
                ->where('jenis_sub_pekerjaan', '!=', '')
                ->distinct()
                ->orderBy('jenis_sub_pekerjaan')
                ->get(['jenis_sub_pekerjaan', 'jenis_dokumen'])
                ->unique('jenis_sub_pekerjaan')
                ->map(fn($dokumen) => [
                    'id' => $dokumen->jenis_sub_pekerjaan,
                    'nama_item_sub_kriteria' => $dokumen->jenis_sub_pekerjaan,
                    'id_sub_kriteria' => $dokumen->jenis_dokumen,
                ])
                ->values()
                ->toArray();
        }
        if (empty($ieJenisPembayaranList)) {
            $ieJenisPembayaranList = Dokumen::whereNotNull('jenis_pembayaran')
                ->where('jenis_pembayaran', '!=', '')
                ->distinct()
                ->orderBy('jenis_pembayaran')
                ->pluck('jenis_pembayaran')
                ->map(fn($value) => ['id_jenis_pembayaran' => $value, 'nama_jenis_pembayaran' => $value])
                ->toArray();
        }

        return [$ieKategoriList, $ieSubKriteriaList, $ieItemSubKriteriaList, $ieJenisPembayaranList];
    }

    public function rekapanKeterlambatan(Request $request)
    {
        return app(OwnerDashboardController::class)->rekapanKeterlambatanByRole($request, 'pembayaran');
    }

    

    /**
     * Export dokumen pembayaran (Excel .xls / PDF) — fitur export bersama.
     * Menggantikan exportToExcel()/exportToExcelByVendor() lama (PhpSpreadsheet,
     * FATAL karena library itu tak terpasang di composer.json) dengan
     * App\Support\DocumentExporter (dependency-free, XML Spreadsheet 2003) lewat
     * trait ExportsDocuments::respondDocumentExport(). Route: GET
     * documents/pembayaran/export (documents.pembayaran.export), dipanggil tombol
     * Export dropdown toolbar Tabulator (CFG.exportUrl).
     *
     * Kolom: columns[] dari request (pilihan WYSIWYG user di tabel) DI-INTERSECT
     * dengan katalog getPembayaranDashboardAvailableColumns() — pertahanan
     * terhadap field asing/objek (mis. status_badge) yang lolos dari klien.
     * Kosong → fallback ke kolom tersimpan user (getSavedPembayaranDashboardColumns()),
     * kosong lagi → seluruh katalog. Export flat 1-sheet — mode per-vendor
     * (form/route reports.pembayaran.export) DIHAPUS atas keputusan user.
     */
    public function exportDocuments(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $query = $this->buildPembayaranQuery($request);
        $rows = $query->get()
            ->map(fn (Dokumen $d) => \App\Support\PembayaranDocumentRow::fromDokumen($d, [], 'pembayaran'))
            ->all();

        $catalog = $this->getPembayaranDashboardAvailableColumns();

        $requestedKeys = $request->get('columns', []);
        $requestedKeys = is_array($requestedKeys) ? array_map('strval', $requestedKeys) : [];
        $requestedKeys = array_values(array_intersect($requestedKeys, array_keys($catalog)));

        if (empty($requestedKeys)) {
            $requestedKeys = array_values(array_intersect($this->getSavedPembayaranDashboardColumns(), array_keys($catalog)));
        }
        if (empty($requestedKeys)) {
            $requestedKeys = array_keys($catalog);
        }

        $columns = array_map(
            fn (string $key) => ['key' => $key, 'label' => $catalog[$key]],
            $requestedKeys
        );

        $options = [
            'title'     => 'Rekapan Pembayaran',
            'total_key' => 'nilai_rupiah',
        ];

        return $this->respondDocumentExport($request, $rows, $columns, $options);
    }

    /**
     * Normalize date filter values from browser date input or localized URLs.
     */
    private function normalizeDateFilter($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {
                // Try the next supported format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Apply month filter supporting month number (1-12), month name (Januari/January),
     * and checking both `bulan` column, `tanggal_masuk`, and `created_at`.
     */
    private function applyMonthFilter($query, $month): void
    {
        if (!$month) {
            return;
        }

        $monthMap = [
            'januari' => 1, 'january' => 1, '1' => 1, '01' => 1,
            'februari' => 2, 'february' => 2, '2' => 2, '02' => 2,
            'maret' => 3, 'march' => 3, '3' => 3, '03' => 3,
            'april' => 4, '4' => 4, '04' => 4,
            'mei' => 5, 'may' => 5, '5' => 5, '05' => 5,
            'juni' => 6, 'june' => 6, '6' => 6, '06' => 6,
            'juli' => 7, 'july' => 7, '7' => 7, '07' => 7,
            'agustus' => 8, 'august' => 8, '8' => 8, '08' => 8,
            'september' => 9, '9' => 9, '09' => 9,
            'oktober' => 10, 'october' => 10, '10' => 10,
            'november' => 11, '11' => 11,
            'desember' => 12, 'december' => 12, '12' => 12,
        ];

        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $normalized = strtolower(trim((string) $month));
        $monthNum = $monthMap[$normalized] ?? (is_numeric($month) ? (int) $month : null);
        $monthName = $monthNum && isset($indoMonths[$monthNum]) ? $indoMonths[$monthNum] : null;

        $query->where(function ($q) use ($monthNum, $monthName, $month) {
            if ($monthNum) {
                $q->whereMonth('created_at', $monthNum)
                  ->orWhereMonth('tanggal_masuk', $monthNum);
            }
            if ($monthName) {
                $q->orWhere('bulan', $monthName);
            }
            if (is_string($month) && $month !== '') {
                $q->orWhere('bulan', $month);
            }
        });
    }
}
