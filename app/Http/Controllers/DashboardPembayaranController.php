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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DashboardPembayaranController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsRoleDashboard;

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

    

    public function index()
    {
        $now = Carbon::now();

        // Get filter parameters
        $statusPembayaran = request('status_pembayaran');
        $year = request('year');
        $month = request('month');
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

            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            session(['pembayaran_dashboard_table_columns' => $selectedColumns]);
        }

        if (!in_array('tanggal_dibayar', $selectedColumns, true)) {
            $selectedColumns[] = 'tanggal_dibayar';
            session(['pembayaran_dashboard_table_columns' => $selectedColumns]);
        }

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
            if ($month) { $q->whereMonth('created_at', $month); }
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
            $query->whereMonth('created_at', $month);
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
                $q->whereMonth('created_at', $month);
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
            'availableColumns' => $availableColumns,
            'rekapanByVendor' => $rekapanByVendor,
            // Dropdown data
            'availableDibayarKepada' => $availableDibayarKepada,
            'availableKategori' => $availableKategori,
            'availableJenisDokumen' => $availableJenisDokumen,
            'availableJenisSubPekerjaan' => $availableJenisSubPekerjaan,
            'availableJenisPembayaran' => $availableJenisPembayaran,
            'availableKebuns' => $availableKebuns,
            'ieKategoriList' => $ieKategoriList,
            'ieSubKriteriaList' => $ieSubKriteriaList,
            'ieItemSubKriteriaList' => $ieItemSubKriteriaList,
            'ieJenisPembayaranList' => $ieJenisPembayaranList,
        ];

        // Return JSON for AJAX requests (no page refresh)
        if (request()->ajax() || request()->wantsJson()) {
            $dokumensArray = $dokumens->map(function ($dok) use ($selectedColumns, $availableColumns) {
                $row = ['id' => $dok->id];
                foreach ($selectedColumns as $colKey) {
                    if ($colKey == 'nilai_rupiah') {
                        $row[$colKey] = number_format($dok->nilai_rupiah ?? 0, 0, ',', '.');
                    } elseif ($colKey == 'dibayar_kepada') {
                        $row[$colKey] = \Illuminate\Support\Str::limit($dok->dibayar_kepada ?? '-', 30);
                    } elseif ($colKey == 'uraian_spp') {
                        $row[$colKey] = \Illuminate\Support\Str::limit($dok->uraian_spp ?? '-', 40);
                    } elseif ($colKey == 'tgl_jatuhtempo') {
                        $row[$colKey] = $dok->tgl_jatuhtempo ? Carbon::parse($dok->tgl_jatuhtempo)->format('d/m/Y') : '-';
                    } elseif ($colKey == 'status_pembayaran') {
                        $row[$colKey] = $dok->computed_status ?? 'belum_siap_dibayar';
                    } else {
                        $row[$colKey] = $dok->$colKey ?? '-';
                    }
                }
                $row['computed_status'] = $dok->computed_status ?? 'belum_siap_dibayar';
                return $row;
            });

            return response()->json([
                'dokumens' => $dokumensArray,
                'pagination' => [
                    'current_page' => $dokumens->currentPage(),
                    'last_page' => $dokumens->lastPage(),
                    'per_page' => $dokumens->perPage(),
                    'total' => $dokumens->total(),
                    'first_item' => $dokumens->firstItem(),
                    'last_item' => $dokumens->lastItem(),
                ],
                'statistics' => $statistics,
                'totalAman' => $totalAman,
                'totalPeringatan' => $totalPeringatan,
                'totalTerlambat' => $totalTerlambat,
                'selectedColumns' => $selectedColumns,
                'availableColumns' => $availableColumns,
                'mode' => $mode,
            ]);
        }

        return view('pembayaranNEW.dashboardPembayaran', $data);
    }

    public function datatable(Request $request)
    {
        $draw = (int) $request->get('draw', 1);
        $start = max(0, (int) $request->get('start', 0));
        $length = (int) $request->get('length', 50);
        $length = $length > 0 ? min($length, 250) : 50;
        $availableColumns = $this->getPembayaranDashboardAvailableColumns();
        $selectedColumns = $request->get('visible_columns', []);
        $selectedColumns = is_array($selectedColumns) ? array_values(array_filter($selectedColumns)) : [];

        if (empty($selectedColumns)) {
            $selectedColumns = $this->getSavedPembayaranDashboardColumns();
        }

        $selectedColumns = array_values(array_filter($selectedColumns, fn($col) => isset($availableColumns[$col])));
        if (empty($selectedColumns)) {
            $selectedColumns = $this->getPembayaranDashboardDefaultColumns();
        }

        $query = $this->buildPembayaranDashboardQuery($request, false);
        $recordsTotal = (clone $query)->count();

        $searchValue = trim((string) (data_get($request->input('search'), 'value') ?: $request->get('filter_search', '')));
        if ($searchValue !== '') {
            $this->applyPembayaranDashboardSearch($query, $searchValue);
        }

        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = (int) data_get($request->input('order'), '0.column', 0);
        $orderDirection = strtolower((string) data_get($request->input('order'), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = data_get($request->input('columns'), "{$orderColumnIndex}.data");

        if ($orderColumn && isset($availableColumns[$orderColumn]) && Schema::hasColumn('dokumens', $orderColumn)) {
            $query->orderBy($orderColumn, $orderDirection);
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        $dokumens = $query
            ->skip($start)
            ->take($length)
            ->get();

        $data = $dokumens->map(function ($dokumen) use ($selectedColumns) {
            $row = [
                'DT_RowId' => 'dokumen_' . $dokumen->id,
                'dokumen_id' => $dokumen->id,
                'kategori_raw' => $dokumen->kategori ?? '',
                'jenis_dokumen_raw' => $dokumen->jenis_dokumen ?? '',
                'jenis_sub_pekerjaan_raw' => $dokumen->jenis_sub_pekerjaan ?? '',
                '_raw' => [],
            ];
            foreach ($selectedColumns as $column) {
                $row[$column] = $this->formatPembayaranDashboardCell($dokumen, $column);
                $row['_raw'][$column] = $this->getPembayaranDashboardRawValue($dokumen, $column);
            }

            return $row;
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    private function buildPembayaranDashboardQuery(Request $request, bool $includeSearch = true)
    {
        $statusPembayaran = $request->get('status_pembayaran');
        $year = $request->get('year');
        $month = $request->get('month');
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
            $query->whereMonth('created_at', $month);
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

    private function formatPembayaranDashboardCell(Dokumen $dokumen, string $column): string
    {
        if ($column === 'status_pembayaran') {
            return $this->renderPembayaranStatusPill($this->getPembayaranComputedStatus($dokumen));
        }

        // Kolom link: kirim URL mentah; JS DataTables yang membungkus jadi tautan bisa diklik.
        if (preg_match('/link/i', $column)) {
            return (string) ($dokumen->{$column} ?? '');
        }

        if ($column === 'nilai_rupiah') {
            return number_format((float) ($dokumen->nilai_rupiah ?? 0), 0, ',', '.');
        }

        if (in_array($column, $this->getPembayaranDateEditableColumns(), true)) {
            $value = $dokumen->{$column} ?? null;
            if (!$value) {
                return '-';
            }

            return $value instanceof Carbon
                ? $value->format('d/m/Y')
                : Carbon::parse($value)->format('d/m/Y');
        }

        $value = $dokumen->{$column} ?? '-';
        if ($value instanceof Carbon) {
            // Tanggal-waktu (mis. TGL MASUK): tanggal-bulan-tahun lalu jam.
            $value = $value->format('d/m/Y H:i:s');
        }

        return e((string) ($value === '' || $value === null ? '-' : $value));
    }

    private function getPembayaranDashboardRawValue(Dokumen $dokumen, string $column): string
    {
        if ($column === 'dibayar_kepada') {
            $relationValue = $dokumen->relationLoaded('dibayarKepadas')
                ? $dokumen->dibayarKepadas->pluck('nama_penerima')->implode(', ')
                : $dokumen->dibayarKepadas()->pluck('nama_penerima')->implode(', ');

            return $relationValue ?: (string) ($dokumen->dibayar_kepada ?? '');
        }

        if (in_array($column, $this->getPembayaranDateEditableColumns(), true)) {
            $value = $dokumen->{$column} ?? null;
            if (!$value) {
                return '';
            }

            return $value instanceof Carbon
                ? $value->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d');
        }

        return (string) ($dokumen->{$column} ?? '');
    }

    private function getPembayaranDateEditableColumns(): array
    {
        return [
            'tanggal_spp',
            'tanggal_berita_acara',
            'tanggal_spk',
            'tanggal_berakhir_spk',
            'tanggal_faktur',
            'tanggal_paraf',
            'tanggal_miro',
            'tanggal_selesai_verifikasi_pajak',
            'tanggal_dibayar',
        ];
    }

    private function getPembayaranComputedStatus(Dokumen $dokumen): string
    {
        if (
            $dokumen->tanggal_dibayar ||
            $dokumen->link_bukti_pembayaran ||
            strtoupper(trim($dokumen->status_pembayaran ?? '')) === 'SUDAH_DIBAYAR' ||
            strtoupper(trim($dokumen->status_pembayaran ?? '')) === 'SUDAH DIBAYAR' ||
            $dokumen->status_pembayaran === 'sudah_dibayar'
        ) {
            return 'sudah_dibayar';
        }

        if ($dokumen->current_handler === 'pembayaran' || $dokumen->status === 'sent_to_pembayaran') {
            return 'siap_dibayar';
        }

        return 'belum_siap_dibayar';
    }

    private function renderPembayaranStatusPill(string $status): string
    {
        if ($status === 'siap_dibayar') {
            return '<span class="status-pill status-pill--ready"><i class="fas fa-circle"></i> Siap Dibayar</span>';
        }

        if ($status === 'sudah_dibayar') {
            return '<span class="status-pill status-pill--paid"><i class="fas fa-circle"></i> Sudah Dibayar</span>';
        }

        return '<span class="status-pill status-pill--pending"><i class="fas fa-circle"></i> Belum Siap</span>';
    }

    

    

    

    

    

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'status_pembayaran' => 'required|in:siap_dibayar,sudah_dibayar',
            ], [
                'status_pembayaran.required' => 'Status pembayaran wajib dipilih.',
                'status_pembayaran.in' => 'Status pembayaran tidak valid.',
            ]);

            // Store old value for logging
            $oldStatusPembayaran = $dokumen->status_pembayaran;

            DB::transaction(function () use ($dokumen, $validated) {
                $updateData = [
                    'status_pembayaran' => $validated['status_pembayaran'],
                ];

                // If status is siap_dibayar, set deadline_at to 3 weeks from now
                if ($validated['status_pembayaran'] === 'siap_dibayar') {
                    $updateData['deadline_at'] = Carbon::now()->addWeeks(3);
                }

                // If status is sudah_dibayar, also update general status and current_handler
                if ($validated['status_pembayaran'] === 'sudah_dibayar') {
                    $updateData['status'] = 'completed';
                    $updateData['current_handler'] = 'pembayaran'; // Move ownership to pembayaran
                }

                $dokumen->update($updateData);
            });

            $dokumen->refresh();

            // Log status change
            if ($oldStatusPembayaran != $dokumen->status_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'status_pembayaran',
                        $oldStatusPembayaran ? ucfirst(str_replace('_', ' ', $oldStatusPembayaran)) : null,
                        ucfirst(str_replace('_', ' ', $dokumen->status_pembayaran)),
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log status change: ' . $logException->getMessage());
                }
            }

            Log::info('Status pembayaran successfully updated', [
                'document_id' => $dokumen->id,
                'status_pembayaran' => $validated['status_pembayaran']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating payment status: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage(), [
                'document_id' => $dokumen->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    

    /**
     * Upload link bukti pembayaran
     */
    public function uploadBukti(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'link_bukti_pembayaran' => 'required|url:http,https|max:1000',
            ], [
                'link_bukti_pembayaran.required' => 'Link bukti pembayaran wajib diisi.',
                'link_bukti_pembayaran.url' => 'Format link tidak valid. Gunakan alamat http:// atau https://.',
                'link_bukti_pembayaran.max' => 'Link maksimal 1000 karakter.',
            ]);

            // Store old value for logging
            $oldLinkBukti = $dokumen->link_bukti_pembayaran;

            DB::transaction(function () use ($dokumen, $validated) {
                $dokumen->update([
                    'link_bukti_pembayaran' => $validated['link_bukti_pembayaran'],
                ]);
            });

            $dokumen->refresh();

            // Log link upload
            if ($oldLinkBukti != $dokumen->link_bukti_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'link_bukti_pembayaran',
                        $oldLinkBukti,
                        $dokumen->link_bukti_pembayaran,
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log link upload: ' . $logException->getMessage());
                }
            }

            Log::info('Link bukti pembayaran successfully uploaded', [
                'document_id' => $dokumen->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Link bukti pembayaran berhasil disimpan.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error uploading payment proof: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading payment proof: ' . $e->getMessage(), [
                'document_id' => $dokumen->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan link bukti pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    

    public function pengembalian()
    {
        // Redirect ke daftar pembayaran karena tidak ada view pengembalian khusus
        return redirect()->route('dashboard.pembayaran')->with('info', 'Halaman pengembalian diarahkan ke dashboard pembayaran');
    }

    public function rekapanKeterlambatan(Request $request)
    {
        return app(OwnerDashboardController::class)->rekapanKeterlambatanByRole($request, 'pembayaran');
    }

    

    /**
     * Export rekapan pembayaran to Excel or PDF
     */
    public function exportRekapan(Request $request)
    {
        $exportType = $request->get('format', $request->get('export', 'excel')); // excel or pdf
        $mode = $request->get('mode', 'normal'); // normal or rekapan_table
        $statusPembayaran = $request->get('status_pembayaran');
        $year = $request->get('year');
        $month = $request->get('month');
        $date = $request->get('date');
        $dateFilter = $this->normalizeDateFilter($date);
        $search = $request->get('search');
        $selectedColumns = $request->get('columns', []);

        // Handler yang dianggap "belum siap dibayar"
        // Perhatikan: di database menggunakan camelCase (Operator, Team Verifikasi), bukan snake_case (ibu_a, ibu_b)
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'operator', 'team_verifikasi', 'ibu_a', 'ibu_b'];

        // Base query - semua dokumen yang sudah melewati proses awal
        $query = Dokumen::whereNotNull('nomor_agenda');

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
                // Sudah dibayar - cek berbagai format (dari CSV: "SUDAH DIBAYAR", dari aplikasi: "sudah_dibayar")
                // Also include documents with tanggal_dibayar set
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
            $query->whereMonth('created_at', $month);
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
        // ADVANCED FILTER SECTION (from /dashboard/pembayaran page)
        // These filters are applied regardless of mode
        // ============================================
        $filterVendor = $request->get('filter_vendor');
        $filterKategori = $request->get('filter_kategori');
        $filterJenisDokumen = $request->get('filter_jenis_dokumen');
        $filterJenisSubPekerjaan = $request->get('filter_jenis_sub_pekerjaan');
        $filterKebun = $request->get('filter_kebun');
        $filterJenisPembayaran = $request->get('filter_jenis_pembayaran');

        if ($filterVendor && $filterVendor !== '') {
            $query->where('dibayar_kepada', $filterVendor);
        }
        if ($filterKategori && $filterKategori !== '') {
            $query->where('kategori', $filterKategori);
        }
        if ($filterJenisDokumen && $filterJenisDokumen !== '') {
            $query->where('jenis_dokumen', $filterJenisDokumen);
        }
        if ($filterJenisSubPekerjaan && $filterJenisSubPekerjaan !== '') {
            $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
        }
        if ($filterKebun && $filterKebun !== '') {
            $query->where(function ($q) use ($filterKebun) {
                $q->where('kebun', $filterKebun)
                    ->orWhere('nama_kebuns', $filterKebun);
            });
        }
        if ($filterJenisPembayaran && $filterJenisPembayaran !== '') {
            $query->where('jenis_pembayaran', $filterJenisPembayaran);
        }

        // Apply rekapan detail filters (only for rekapan_table mode)
        if ($mode === 'rekapan_table') {
            // Filter by Dibayar Kepada (Vendor)
            $filterDibayarKepada = $request->get('filter_dibayar_kepada_column');
            if ($filterDibayarKepada) {
                $query->where('dibayar_kepada', $filterDibayarKepada);
            }

            // Filter by Kategori
            $filterKategori = $request->get('filter_kategori_column');
            if ($filterKategori) {
                $query->where('kategori', $filterKategori);
            }

            // Filter by Jenis Dokumen
            $filterJenisDokumen = $request->get('filter_jenis_dokumen_column');
            if ($filterJenisDokumen) {
                $query->where('jenis_dokumen', $filterJenisDokumen);
            }

            // Filter by Jenis Sub Pekerjaan
            $filterJenisSubPekerjaan = $request->get('filter_jenis_sub_pekerjaan_column');
            if ($filterJenisSubPekerjaan) {
                $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
            }

            // Filter by Jenis Pembayaran
            $filterJenisPembayaran = $request->get('filter_jenis_pembayaran_column');
            if ($filterJenisPembayaran) {
                $query->where('jenis_pembayaran', $filterJenisPembayaran);
            }

            // Filter by Kebun (check both kebun and nama_kebuns fields)
            $filterKebun = $request->get('filter_jenis_kebuns_column');
            if ($filterKebun) {
                $query->where(function ($q) use ($filterKebun) {
                    $q->where('kebun', $filterKebun)
                        ->orWhere('nama_kebuns', $filterKebun);
                });
            }
        }

        // Helper function to calculate computed status (must match index() logic)
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
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };

        // Get all documents (no pagination for export)
        // For rekapan_table mode, order by dibayar_kepada to match the display order
        if ($mode === 'rekapan_table') {
            $dokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
                ->orderBy('dibayar_kepada')
                ->get();
        } else {
            $dokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Add computed status to each document
        $dokumens->each(function ($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Available columns mapping - must match index() method's $availableColumns
        $availableColumns = [
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
            'tanggal_berakhir_ba' => 'TGL Akhir BA',
            'nomor_po' => 'No PO',
            'nomor_mirror' => 'Nomor Miro',
            'no_faktur' => 'No Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tgl Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPH Terhutang',
            'kebun' => 'Kebun',
            'umur_dokumen_tanggal_masuk' => 'Umur(tgl Msk)',
            'umur_dokumen_tanggal_spp' => 'Umur(Tgl SPP)',
            'umur_dokumen_tanggal_ba' => 'Umur(Tgl BA)',
            'nilai_belum_siap_bayar' => 'Belum siap bayar',
            'nilai_siap_bayar' => 'sudah siap bayar',
            'nilai_sudah_dibayar' => 'sudah dibayar',
        ];

        // Default columns for export
        $defaultColumns = ['nomor_agenda', 'nomor_spp', 'dibayar_kepada', 'uraian_spp', 'nilai_rupiah'];

        // Always use selected columns from request if provided, regardless of mode
        if (!empty($selectedColumns)) {
            $columnsToExport = $selectedColumns;
        } else {
            $columnsToExport = $defaultColumns;
        }

        // Get vendor export mode (multi_sheet, single_sheet, single_vendor, or empty)
        $vendorExportMode = $request->get('vendor_export_mode', '');

        if ($exportType === 'excel') {
            // If vendor export mode is specified and in rekapan_table mode, use vendor-grouped export
            if ($mode === 'rekapan_table' && in_array($vendorExportMode, ['multi_sheet', 'single_sheet', 'single_vendor'])) {
                return $this->exportToExcelByVendor($dokumens, $columnsToExport, $availableColumns, $vendorExportMode);
            }
            return $this->exportToExcel($dokumens, $columnsToExport, $availableColumns, $mode, $statusPembayaran, $year, $month, $search);
        } else {
            return $this->exportToPDF($dokumens, $columnsToExport, $availableColumns, $mode, $statusPembayaran, $year, $month, $search);
        }
    }

    /**
     * Export to Excel with PhpSpreadsheet (styled XLSX)
     */
    private function exportToExcel($dokumens, $columns, $availableColumns, $mode, $statusFilter, $year, $month, $search)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapan Pembayaran');

        // Header row mapping
        $headers = [];
        foreach ($columns as $col) {
            if ($col === 'sent_to_pembayaran_at') {
                $headers[] = 'Tgl Diterima';
            } elseif ($col === 'computed_status') {
                $headers[] = 'Status';
            } elseif ($col === 'tanggal_dibayar') {
                $headers[] = 'Tgl Dibayar';
            } else {
                $headers[] = $availableColumns[$col] ?? ucfirst(str_replace('_', ' ', $col));
            }
        }

        // Write headers (row 1)
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue([$colIndex, 1], $header);
            $colIndex++;
        }

        // Style headers - green background with white bold text
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0d6efd'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Write data rows
        $rowIndex = 2;
        foreach ($dokumens as $dokumen) {
            $colIndex = 1;
            foreach ($columns as $col) {
                $value = $this->getColumnValue($dokumen, $col);
                $sheet->setCellValue([$colIndex, $rowIndex], $value);

                // Format currency columns
                if (in_array($col, ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'])) {
                    $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                }
                $colIndex++;
            }

            // Zebra stripe - alternate row colors
            if ($rowIndex % 2 == 0) {
                $rowStyle = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ];
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")->applyFromArray($rowStyle);
            }

            $rowIndex++;
        }

        // Apply center + middle alignment to all data cells
        $lastRow = $rowIndex - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        // Apply borders to all data cells
        $lastRow = $rowIndex - 1;
        if ($lastRow >= 2) {
            $dataBorderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray($dataBorderStyle);
        }

        // =============================================
        // Add TOTAL row at the bottom
        // =============================================
        $totalRow = $rowIndex;
        $totalNilai = 0;
        $nilaiColumnIndex = null;

        // Find the nilai_rupiah column index and calculate total
        foreach ($columns as $idx => $col) {
            if ($col === 'nilai_rupiah') {
                $nilaiColumnIndex = $idx + 1; // 1-indexed
                break;
            }
        }

        // Calculate total from dokumens
        foreach ($dokumens as $dokumen) {
            $totalNilai += floatval($dokumen->nilai_rupiah ?? 0);
        }

        // Write TOTAL label in first column
        $sheet->setCellValue([1, $totalRow], 'TOTAL');

        // Write total nilai in the nilai_rupiah column if it exists
        if ($nilaiColumnIndex !== null) {
            $sheet->setCellValue([$nilaiColumnIndex, $totalRow], $totalNilai);
            // Format as currency
            $totalNilaiCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($nilaiColumnIndex) . $totalRow;
            $sheet->getStyle($totalNilaiCell)->getNumberFormat()->setFormatCode('#,##0');
        }

        // Style the TOTAL row - green background with bold white text
        $totalRowStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'], // Green color
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A{$totalRow}:{$lastColumn}{$totalRow}")->applyFromArray($totalRowStyle);

        // Auto-size columns (except uraian_spp which gets fixed width + wrap)
        foreach (range(1, count($headers)) as $colNum) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum);
            $colKey = $columns[$colNum - 1] ?? '';
            if ($colKey === 'uraian_spp') {
                // Fixed width + text wrap for Uraian SPP so it doesn't stretch too wide
                $sheet->getColumnDimension($colLetter)->setWidth(50);
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$totalRow}")->getAlignment()->setWrapText(true);
            } else {
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Generate output
        $filename = 'Rekapan_Pembayaran_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Use temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export to Excel grouped by vendor
     * Supports multi_sheet (each vendor on separate sheet) and single_sheet (all in one sheet with vendor separation)
     */
    private function exportToExcelByVendor($dokumens, $columns, $availableColumns, $exportMode)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Group documents by vendor
        $groupedByVendor = [];
        foreach ($dokumens as $dokumen) {
            $vendor = $dokumen->dibayar_kepada ?? 'Tidak Diketahui';
            $groupedByVendor[$vendor][] = $dokumen;
        }

        // Define column headers for vendor export
        $vendorColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'No. SPP',
            'sent_to_pembayaran_at' => 'Tgl Diterima',
            'dibayar_kepada' => 'Nama Vendor',
            'nilai_rupiah' => 'Nilai Rupiah',
            'computed_status' => 'Status',
            'tanggal_dibayar' => 'Tgl Dibayar',
        ];

        // Style arrays
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '083E40']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $totalRowStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '22C55E']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $vendorHeaderStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '083E40']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        if ($exportMode === 'multi_sheet') {
            // Multi-sheet mode: each vendor on a separate sheet
            $sheetIndex = 0;
            foreach ($groupedByVendor as $vendor => $documents) {
                if ($sheetIndex === 0) {
                    $sheet = $spreadsheet->getActiveSheet();
                } else {
                    $sheet = $spreadsheet->createSheet();
                }

                // Sanitize sheet name (max 31 chars, no special chars)
                $sheetName = substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $vendor), 0, 31);
                $sheet->setTitle($sheetName ?: 'Vendor ' . ($sheetIndex + 1));

                // Write header row
                $colIndex = 1;
                foreach ($vendorColumns as $key => $label) {
                    $sheet->setCellValue([$colIndex, 1], $label);
                    $colIndex++;
                }

                // Apply header style
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($vendorColumns));
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

                // Write data rows
                $rowIndex = 2;
                $vendorTotal = 0;
                foreach ($documents as $dokumen) {
                    $colIndex = 1;
                    foreach ($vendorColumns as $key => $label) {
                        $value = $this->getExportCellValue($dokumen, $key);
                        $sheet->setCellValue([$colIndex, $rowIndex], $value);

                        if ($key === 'nilai_rupiah') {
                            $vendorTotal += floatval($dokumen->nilai_rupiah ?? 0);
                        }
                        $colIndex++;
                    }
                    $rowIndex++;
                }

                // Apply borders to all data cells
                $lastDataRow = $rowIndex - 1;
                if ($lastDataRow >= 2) {
                    $sheet->getStyle("A2:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    $sheet->getStyle("A2:{$lastCol}{$lastDataRow}")->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }

                // Add TOTAL row for this vendor
                $totalRow = $rowIndex;
                $sheet->setCellValue([1, $totalRow], 'TOTAL');
                $sheet->mergeCells([1, $totalRow, 4, $totalRow]); // Merge first 4 columns
                $sheet->setCellValue([5, $totalRow], 'Rp ' . number_format($vendorTotal, 0, ',', '.'));
                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray($totalRowStyle);

                // Auto-size columns (except uraian_spp which gets fixed width + wrap)
                $vendorColumnKeys = array_keys($vendorColumns);
                foreach (range(1, count($vendorColumns)) as $col) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $colKey = $vendorColumnKeys[$col - 1] ?? '';
                    if ($colKey === 'uraian_spp') {
                        $sheet->getColumnDimension($colLetter)->setWidth(50);
                        $sheet->getStyle("{$colLetter}2:{$colLetter}{$totalRow}")->getAlignment()->setWrapText(true);
                    } else {
                        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                    }
                }

                $sheetIndex++;
            }
        } else {
            // Single-sheet mode: all vendors in one sheet with separation
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekapan Per Vendor');

            $rowIndex = 1;
            $grandTotal = 0;

            foreach ($groupedByVendor as $vendor => $documents) {
                // Vendor header row
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($vendorColumns));
                $sheet->setCellValue([1, $rowIndex], $vendor);
                $sheet->mergeCells([1, $rowIndex, count($vendorColumns), $rowIndex]);
                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")->applyFromArray($vendorHeaderStyle);
                $rowIndex++;

                // Column headers
                $colIndex = 1;
                foreach ($vendorColumns as $key => $label) {
                    $sheet->setCellValue([$colIndex, $rowIndex], $label);
                    $colIndex++;
                }
                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")->applyFromArray($headerStyle);
                $rowIndex++;

                // Data rows
                $vendorTotal = 0;
                $vendorCount = 0;
                foreach ($documents as $dokumen) {
                    $colIndex = 1;
                    foreach ($vendorColumns as $key => $label) {
                        $value = $this->getExportCellValue($dokumen, $key);
                        $sheet->setCellValue([$colIndex, $rowIndex], $value);

                        if ($key === 'nilai_rupiah') {
                            $vendorTotal += floatval($dokumen->nilai_rupiah ?? 0);
                        }
                        $colIndex++;
                    }
                    $vendorCount++;
                    $rowIndex++;
                }

                // Apply borders to data cells for this vendor section
                $dataStartRow = $rowIndex - $vendorCount;
                $dataEndRow = $rowIndex - 1;
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$dataEndRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$dataEndRow}")->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }

                // Vendor summary rows
                $sheet->setCellValue([1, $rowIndex], "Total Dokumen: {$vendorCount}");
                $sheet->mergeCells([1, $rowIndex, 4, $rowIndex]);
                $sheet->setCellValue([5, $rowIndex], 'Rp ' . number_format($vendorTotal, 0, ',', '.'));
                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")->applyFromArray($totalRowStyle);
                $grandTotal += $vendorTotal;
                $rowIndex++;

                // Empty row for separation
                $rowIndex++;
            }

            // Grand total row at the end
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($vendorColumns));
            $sheet->setCellValue([1, $rowIndex], 'GRAND TOTAL');
            $sheet->mergeCells([1, $rowIndex, 4, $rowIndex]);
            $sheet->setCellValue([5, $rowIndex], 'Rp ' . number_format($grandTotal, 0, ',', '.'));
            $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F766E']
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ]);

            // Auto-size columns (except uraian_spp which gets fixed width + wrap)
            $vendorColumnKeys = array_keys($vendorColumns);
            foreach (range(1, count($vendorColumns)) as $col) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $colKey = $vendorColumnKeys[$col - 1] ?? '';
                if ($colKey === 'uraian_spp') {
                    $sheet->getColumnDimension($colLetter)->setWidth(50);
                    $sheet->getStyle("{$colLetter}1:{$colLetter}{$rowIndex}")->getAlignment()->setWrapText(true);
                } else {
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }
            }
        }

        // Generate output
        $filename = 'Rekapan_Pembayaran_Per_Vendor_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'export_vendor_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Get cell value for export based on column key
     */
    private function getExportCellValue($dokumen, $key)
    {
        switch ($key) {
            case 'nomor_agenda':
                return $dokumen->nomor_agenda ?? '-';
            case 'nomor_spp':
                return $dokumen->nomor_spp ?? '-';
            case 'sent_to_pembayaran_at':
                return $dokumen->sent_to_pembayaran_at
                    ? \Carbon\Carbon::parse($dokumen->sent_to_pembayaran_at)->format('d/m/Y')
                    : '-';
            case 'dibayar_kepada':
                return $dokumen->dibayar_kepada ?? '-';
            case 'nilai_rupiah':
                return 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.');
            case 'computed_status':
            case 'status_pembayaran':
                $status = $dokumen->computed_status ?? 'belum_siap_dibayar';
                if ($status === 'sudah_dibayar')
                    return 'Sudah Dibayar';
                if ($status === 'siap_dibayar')
                    return 'Siap Dibayar';
                return 'Belum Siap Dibayar';
            case 'tanggal_dibayar':
                return $dokumen->tanggal_dibayar
                    ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d/m/Y')
                    : '-';
            default:
                return $dokumen->$key ?? '-';
        }
    }

    /**
     * Convert array to CSV row
     */
    private function arrayToCsv($array, $delimiter = ';')
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $array, $delimiter);
        rewind($output);
        $data = fread($output, 1048576);
        fclose($output);
        return rtrim($data, "\n\r");
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($dokumens, $columns, $availableColumns, $mode, $statusFilter, $year, $month, $search)
    {
        // Handler yang dianggap "belum siap dibayar"
        // Perhatikan: di database menggunakan camelCase (Operator, Team Verifikasi), bukan snake_case (ibu_a, ibu_b)
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'operator', 'team_verifikasi', 'ibu_a', 'ibu_b'];

        // Helper function to calculate computed status
        $getComputedStatus = function ($doc) use ($belumSiapHandlers) {
            $statusPembayaran = strtoupper(trim($doc->status_pembayaran ?? ''));
            if (
                $statusPembayaran === 'SUDAH_DIBAYAR' ||
                $statusPembayaran === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar'
            ) {
                return 'sudah_dibayar';
            }
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };

        // Add computed status to all documents first
        $dokumens->each(function ($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Calculate totals for subtotal and grand total
        $rekapanByVendor = null;
        $grandTotalNilai = 0;
        $grandTotalBelum = 0;
        $grandTotalSiap = 0;
        $grandTotalSudah = 0;

        if ($mode === 'rekapan_table' && !empty($columns)) {
            // Group by vendor - same logic as rekapan() method
            $rekapanByVendor = $dokumens->groupBy(function ($doc) {
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
            })->sortBy(function ($vendorData) {
                // Sort vendors: "Tidak Diketahui" should come last, others alphabetically
                if ($vendorData['vendor'] === 'Tidak Diketahui') {
                    return 'zzz_' . $vendorData['vendor'];
                }
                return $vendorData['vendor'];
            });

            // Calculate grand totals
            foreach ($rekapanByVendor as $vendorData) {
                $grandTotalNilai += $vendorData['total_nilai'];
                $grandTotalBelum += $vendorData['total_belum_dibayar'];
                $grandTotalSiap += $vendorData['total_siap_dibayar'];
                $grandTotalSudah += $vendorData['total_sudah_dibayar'];
            }
        } else {
            // For normal mode, calculate grand totals from all documents
            $grandTotalNilai = $dokumens->sum('nilai_rupiah');
            $grandTotalBelum = $dokumens->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah');
            $grandTotalSiap = $dokumens->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah');
            $grandTotalSudah = $dokumens->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah');
        }

        // Find the index of first value column
        $valueColumns = ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'];
        $firstValueIndex = null;
        foreach ($columns as $idx => $col) {
            if (in_array($col, $valueColumns)) {
                $firstValueIndex = $idx;
                break;
            }
        }
        $colspanCount = $firstValueIndex !== null ? $firstValueIndex + 1 : count($columns) + 1;

        // Prepare data for PDF view
        $pdfData = [
            'dokumens' => $dokumens,
            'columns' => $columns,
            'availableColumns' => $availableColumns,
            'statusFilter' => $statusFilter,
            'year' => $year,
            'month' => $month,
            'search' => $search,
            'mode' => $mode,
            'rekapanByVendor' => $rekapanByVendor,
            'grandTotalNilai' => $grandTotalNilai,
            'grandTotalBelum' => $grandTotalBelum,
            'grandTotalSiap' => $grandTotalSiap,
            'grandTotalSudah' => $grandTotalSudah,
            'firstValueIndex' => $firstValueIndex,
            'colspanCount' => $colspanCount,
        ];

        // Return view that can be printed as PDF using browser print
        return view('pembayaranNEW.dokumens.export-pdf', $pdfData);
    }

    /**
     * Get column value for export
     */
    private function getColumnValue($dokumen, $column)
    {
        switch ($column) {
            case 'nomor_agenda':
                return $dokumen->nomor_agenda ?? '-';
            case 'nomor_spp':
                return $dokumen->nomor_spp ?? '-';
            case 'sent_to_pembayaran_at':
                return $dokumen->sent_to_pembayaran_at ? $dokumen->sent_to_pembayaran_at->format('d/m/Y') : '-';
            case 'dibayar_kepada':
                if ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0) {
                    return $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ');
                }
                return $dokumen->dibayar_kepada ?? '-';
            case 'nilai_rupiah':
                return 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.');
            case 'computed_status':
            case 'status_pembayaran':
                $status = $dokumen->computed_status ?? 'belum_siap_dibayar';
                if ($status === 'sudah_dibayar')
                    return 'Sudah Dibayar';
                if ($status === 'siap_dibayar')
                    return 'Siap Dibayar';
                return 'Belum Siap Dibayar';
            case 'tanggal_dibayar':
                return $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-';
            case 'jenis_pembayaran':
                return $dokumen->jenis_pembayaran ?? '-';
            case 'jenis_sub_pekerjaan':
                return $dokumen->jenis_sub_pekerjaan ?? '-';
            case 'nomor_mirror':
                return $dokumen->nomor_mirror ?? '-';
            case 'tanggal_spp':
                return $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-';
            case 'uraian_spp':
                return $dokumen->uraian_spp ?? '-';
            case 'kebun':
                return $dokumen->kebun ?? $dokumen->nama_kebuns ?? '-';
            case 'tanggal_berita_acara':
                return $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-';
            case 'no_berita_acara':
                return $dokumen->no_berita_acara ?? '-';
            case 'tanggal_berakhir_ba':
                return $dokumen->tanggal_berakhir_ba ? $dokumen->tanggal_berakhir_ba->format('d/m/Y') : '-';
            case 'no_spk':
                return $dokumen->no_spk ?? '-';
            case 'tanggal_spk':
                return $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-';
            case 'tanggal_berakhir_spk':
                return $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-';
            case 'umur_dokumen_tanggal_masuk':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_masuk) {
                    $tanggalMasuk = \Carbon\Carbon::parse($dokumen->tanggal_masuk)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalMasuk->lte($hariIni) ? (int) $tanggalMasuk->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'umur_dokumen_tanggal_spp':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_spp) {
                    $tanggalSpp = \Carbon\Carbon::parse($dokumen->tanggal_spp)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalSpp->lte($hariIni) ? (int) $tanggalSpp->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'umur_dokumen_tanggal_ba':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_berita_acara) {
                    $tanggalBa = \Carbon\Carbon::parse($dokumen->tanggal_berita_acara)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalBa->lte($hariIni) ? (int) $tanggalBa->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'nilai_belum_siap_bayar':
                return $dokumen->computed_status === 'belum_siap_dibayar'
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            case 'nilai_siap_bayar':
                return $dokumen->computed_status === 'siap_dibayar'
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            case 'nilai_sudah_dibayar':
                return $dokumen->computed_status === 'sudah_dibayar'
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            default:
                return $dokumen->$column ?? '-';
        }
    }


    /**
     * Get document detail for Pembayaran view
     */
    /**
     * Get payment data (tanggal_dibayar and link_bukti_pembayaran) for edit modal
     */
    public function getPaymentData(Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'tanggal_dibayar' => $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('Y-m-d') : '',
            'link_bukti_pembayaran' => $dokumen->link_bukti_pembayaran ?? '',
        ]);
    }

    public function getDocumentDetail(Dokumen $dokumen)
    {
        try {
            // Log request details for debugging
            Log::info('getDocumentDetail called', [
                'dokumen_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'status' => $dokumen->status,
                'wantsJson' => request()->wantsJson(),
                'ajax' => request()->ajax(),
                'accept_header' => request()->header('Accept'),
                'x_requested_with' => request()->header('X-Requested-With'),
            ]);

            // Allow access if document is handled by pembayaran or sent to pembayaran
            $allowedHandlers = ['pembayaran', 'akutansi', 'perpajakan', 'team_verifikasi', 'team_verifikasi'];
            $allowedStatuses = ['sent_to_pembayaran', 'sedang diproses', 'selesai', 'sudah_dibayar', 'menunggu_di_approve', 'pending_approval_pembayaran'];

            // Check if document was sent to pembayaran role (using dokumen_role_data)
            $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');
            $isSentToPembayaran = $pembayaranRoleData && $pembayaranRoleData->received_at !== null;

            // Check if document status is for pembayaran approval
            $pembayaranStatus = $dokumen->getStatusForRole('pembayaran');
            $isPendingInPembayaran = $pembayaranStatus && in_array($pembayaranStatus->status, ['pending', 'approved', 'rejected']);

            // Allow access if:
            // 1. Document handler is in allowed list, OR
            // 2. Document status is in allowed list, OR
            // 3. Document was sent to pembayaran (has role_data with received_at), OR
            // 4. Document has pending status in pembayaran inbox
            $hasAccess = in_array(strtolower($dokumen->current_handler ?? ''), array_map('strtolower', $allowedHandlers)) ||
                in_array($dokumen->status, $allowedStatuses) ||
                $isSentToPembayaran ||
                $isPendingInPembayaran;

            if (!$hasAccess) {
                Log::warning('Access denied for document detail', [
                    'dokumen_id' => $dokumen->id,
                    'current_handler' => $dokumen->current_handler,
                    'status' => $dokumen->status,
                    'isSentToPembayaran' => $isSentToPembayaran,
                    'isPendingInPembayaran' => $isPendingInPembayaran,
                    'pembayaranRoleData' => $pembayaranRoleData ? [
                        'received_at' => $pembayaranRoleData->received_at,
                        'role_code' => $pembayaranRoleData->role_code,
                    ] : null,
                    'pembayaranStatus' => $pembayaranStatus ? [
                        'status' => $pembayaranStatus->status,
                        'role_code' => $pembayaranStatus->role_code,
                    ] : null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Document handler: ' . ($dokumen->current_handler ?? 'null') . ', Status: ' . ($dokumen->status ?? 'null')
                ], 403);
            }

            Log::info('Access granted for document detail', [
                'dokumen_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'status' => $dokumen->status,
                'isSentToPembayaran' => $isSentToPembayaran,
                'isPendingInPembayaran' => $isPendingInPembayaran,
            ]);

            // Load required relationships
            $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

            // Check if request wants JSON (for AJAX modal)
            // Check multiple conditions to ensure JSON response for AJAX requests
            $wantsJson = request()->wantsJson() ||
                request()->ajax() ||
                request()->expectsJson() ||
                (request()->header('Accept') && str_contains(request()->header('Accept'), 'application/json')) ||
                (request()->header('X-Requested-With') === 'XMLHttpRequest');

            Log::info('Request type check', [
                'wantsJson' => $wantsJson,
                'wantsJson_method' => request()->wantsJson(),
                'ajax_method' => request()->ajax(),
                'expectsJson_method' => request()->expectsJson(),
            ]);

            if ($wantsJson) {
                return $this->getDocumentDetailJson($dokumen);
            }

            // Return HTML partial for detail view (backward compatibility)
            $html = $this->generateDocumentDetailHtml($dokumen);
            return response($html);
        } catch (\Exception $e) {
            Log::error('Error in getDocumentDetail', [
                'dokumen_id' => $dokumen->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document detail as JSON for AJAX modal
     */
    private function getDocumentDetailJson(Dokumen $dokumen)
    {
        // Determine payment status
        $paymentStatus = $dokumen->computed_status ?? 'belum_siap_bayar';
        if (is_string($paymentStatus)) {
            $statusUpper = strtoupper(trim($paymentStatus));
            if ($statusUpper === 'SIAP_DIBAYAR' || $statusUpper === 'SIAP DIBAYAR') {
                $paymentStatus = 'siap_bayar';
            } elseif ($statusUpper === 'SUDAH_DIBAYAR' || $statusUpper === 'SUDAH DIBAYAR') {
                $paymentStatus = 'sudah_dibayar';
            } else {
                $paymentStatus = 'belum_siap_bayar';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'nilai_rupiah_formatted' => $dokumen->formatted_nilai_rupiah ?? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
                'tanggal_masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
                'tanggal_masuk_date' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('Y-m-d') : null,
                'tanggal_masuk_time' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('H:i:s') : null,
                'bulan' => $dokumen->bulan,
                'tahun' => $dokumen->tahun,
                'tanggal_spp' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
                'tanggal_spp_date' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : null,
                'kategori' => $dokumen->kategori ?? '-',
                'jenis_dokumen' => $dokumen->jenis_dokumen ?? '-',
                'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
                'jenis_pembayaran' => $dokumen->jenis_pembayaran ?? '-',
                'kebun' => $dokumen->kebun ?? '-',
                'bagian' => $dokumen->bagian ?? '-',
                'dibayar_kepada' => $dokumen->dibayarKepadas->count() > 0
                    ? $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ')
                    : ($dokumen->dibayar_kepada ?? '-'),
                'no_berita_acara' => $dokumen->no_berita_acara ?? '-',
                'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-',
                'tanggal_berita_acara_date' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : null,
                'no_spk' => $dokumen->no_spk ?? '-',
                'tanggal_spk' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-',
                'tanggal_spk_date' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : null,
                'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-',
                'tanggal_berakhir_spk_date' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('Y-m-d') : null,
                'no_po' => $dokumen->dokumenPos->count() > 0 ? $dokumen->dokumenPos->pluck('nomor_po')->join(', ') : '-',
                'no_pr' => $dokumen->dokumenPrs->count() > 0 ? $dokumen->dokumenPrs->pluck('nomor_pr')->join(', ') : '-',
                'nomor_miro' => $dokumen->nomor_miro ?? '-',
                'tanggal_miro' => $dokumen->tanggal_miro ? $dokumen->tanggal_miro->format('d/m/Y') : '-',
                'tanggal_miro_date' => $dokumen->tanggal_miro ? $dokumen->tanggal_miro->format('Y-m-d') : null,
                'status' => $dokumen->status,
                'status_display' => $this->getStatusDisplayName($dokumen->status),
                'payment_status' => $paymentStatus,
                'tanggal_dibayar' => $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-',
                'tanggal_dibayar_date' => $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('Y-m-d') : null,
                'link_bukti_pembayaran' => $dokumen->link_bukti_pembayaran ?? '-',
                // Perpajakan data
                'npwp' => $dokumen->npwp ?? '-',
                'status_perpajakan' => $dokumen->status_perpajakan ?? '-',
                'no_faktur' => $dokumen->no_faktur ?? '-',
                'tanggal_faktur' => $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '-',
                'tanggal_faktur_date' => $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('Y-m-d') : null,
                'tanggal_selesai_verifikasi_pajak' => $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d/m/Y') : '-',
                'tanggal_selesai_verifikasi_pajak_date' => $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('Y-m-d') : null,
                'jenis_pph' => $dokumen->jenis_pph ?? '-',
                'dpp_pph' => $dokumen->dpp_pph ? number_format($dokumen->dpp_pph, 0, ',', '.') : '-',
                'dpp_pph_raw' => $dokumen->dpp_pph,
                'ppn_terhutang' => $dokumen->ppn_terhutang ? number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-',
                'ppn_terhutang_raw' => $dokumen->ppn_terhutang,
                'link_dokumen_pajak' => $dokumen->link_dokumen_pajak ?? '-',
            ]
        ]);
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
            'Bulan' => $dokumen->bulan,
            'Tahun' => $dokumen->tahun,
            'No SPP' => $dokumen->nomor_spp,
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah ?? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
            'Kriteria CF' => $dokumen->kategori ?? '-',
            'Sub Kriteria' => $dokumen->jenis_dokumen ?? '-',
            'Item Sub Kriteria' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Kebun' => $dokumen->kebun ?? '-',
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

        if ($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi' || $dokumen->status == 'sent_to_pembayaran') {
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

        // Data Akutansi Section - Always show for documents sent to pembayaran
        $html .= '<div class="detail-section-separator">
            <div class="separator-content">
                <i class="fa-solid fa-calculator"></i>
                <span>Data Akutansi</span>
                <span class="tax-badge" style="background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);">DITAMBAHKAN OLEH TEAM AKUTANSI</span>
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
     * Set deadline for pembayaran
     */
    public function setDeadline(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Enhanced logging with user context
            Log::info('=== SET DEADLINE PEMBAYARAN REQUEST START ===', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'deadline_exists' => $dokumen->deadline_at ? true : false,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role,
                'request_data' => $request->all()
            ]);

            // Use helper for validation
            $validation = DokumenHelper::canSetDeadline($dokumen);
            if (!$validation['can_set']) {
                Log::warning('Deadline set failed - Validation error', [
                    'document_id' => $dokumen->id,
                    'user_role' => Auth::user()?->role,
                    'validation_result' => $validation
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                    'debug_info' => $validation['debug']
                ], 403);
            }

            $validated = $request->validate([
                'deadline_days' => 'required|integer|min:1|max:30',
                'deadline_note' => 'nullable|string|max:500',
            ], [
                'deadline_days.required' => 'Periode deadline wajib dipilih.',
                'deadline_days.integer' => 'Periode deadline harus berupa angka.',
                'deadline_days.min' => 'Deadline minimal 1 hari.',
                'deadline_days.max' => 'Deadline maksimal 30 hari.',
                'deadline_note.max' => 'Catatan maksimal 500 karakter.',
            ]);

            $deadlineDays = (int) $validated['deadline_days'];
            $deadlineNote = isset($validated['deadline_note']) && trim($validated['deadline_note']) !== ''
                ? trim($validated['deadline_note'])
                : null;

            // Update using transaction
            DB::transaction(function () use ($dokumen, $deadlineDays, $deadlineNote) {
                $dokumen->update([
                    'deadline_at' => now()->addDays($deadlineDays),
                    'deadline_days' => $deadlineDays,
                    'deadline_note' => $deadlineNote,
                    'status' => 'sedang diproses',
                    'processed_at' => now(),
                ]);
            });

            Log::info('Deadline successfully set for Pembayaran', [
                'document_id' => $dokumen->id,
                'deadline_days' => $deadlineDays,
                'deadline_at' => $dokumen->fresh()->deadline_at
            ]);

            return response()->json([
                'success' => true,
                'message' => "Deadline berhasil ditetapkan ({$deadlineDays} hari). Dokumen sekarang terbuka untuk diproses.",
                'deadline' => $dokumen->fresh()->deadline_at->format('d M Y, H:i'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error setting Pembayaran deadline: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error setting deadline in Pembayaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline'
            ], 500);
        }
    }

    /**
     * Check for new documents sent to pembayaran
     */
    public function checkUpdates(Request $request)
    {
        try {
            $lastChecked = $request->input('last_checked', 0);

            // Convert timestamp to Carbon instance
            $lastCheckedDate = $lastChecked > 0
                ? \Carbon\Carbon::createFromTimestamp($lastChecked)
                : \Carbon\Carbon::now();

            // Cek dokumen baru yang dikirim ke pembayaran menggunakan dokumen_role_data
            // Exclude documents imported from CSV to prevent notification spam
            $newDocuments = Dokumen::where(function ($query) use ($lastCheckedDate) {
                $query->where(function ($q) {
                    $q->where('current_handler', 'pembayaran')
                        ->orWhere('status', 'sent_to_pembayaran');
                })
                    ->where(function ($q) use ($lastCheckedDate) {
                        // Check if received_at in roleData is newer
                        $q->whereHas('roleData', function ($subQ) use ($lastCheckedDate) {
                            $subQ->where('role_code', 'pembayaran')
                                ->where('received_at', '>', $lastCheckedDate);
                        })
                            // Or check updated_at as fallback
                            ->orWhere('updated_at', '>', $lastCheckedDate);
                    });
            })
                // Exclude CSV imported documents (only if column exists) - Applied outside main where to ensure proper filtering
                ->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                    $query->where(function ($q) {
                        $q->where('imported_from_csv', false)
                            ->orWhereNull('imported_from_csv');
                    });
                })
                ->with([
                    'roleData' => function ($query) {
                        $query->where('role_code', 'pembayaran');
                    }
                ])
                ->latest('updated_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function ($query) {
                $query->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran');
            })->count();

            return response()->json([
                'has_updates' => $newDocuments->count() > 0,
                'new_count' => $newDocuments->count(),
                'total_documents' => $totalDocuments,
                'new_documents' => $newDocuments->map(function ($doc) {
                    $roleData = $doc->roleData->firstWhere('role_code', 'pembayaran');
                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => $doc->uraian_spp,
                        'nilai_rupiah' => $doc->nilai_rupiah,
                        'status' => $doc->status,
                        'sent_at' => $roleData?->received_at?->format('d/m/Y H:i') ?? ($doc->updated_at ? $doc->updated_at->format('d/m/Y H:i') : '-'),
                        'sent_from' => 'Team Akutansi',
                    ];
                }),
                'last_checked' => time()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in pembayaran/check-updates: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => true,
                'message' => 'Failed to check updates: ' . $e->getMessage()
            ], 500);
        }
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
     * Get status display name in Indonesian
     */
    private function getStatusDisplayName($status)
    {
        $statusMap = [
            'draft' => 'Draft',
            'sedang diproses' => 'Sedang Diproses',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pending_approval_team_verifikasi' => 'Menunggu Persetujuan Ibu Yuni',
            'sent_to_team_verifikasi' => 'Terkirim ke Ibu Yuni',
            'proses_Team Verifikasi' => 'Diproses Ibu Yuni',
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
            'returned_to_operator' => 'Dikembalikan ke Ibu Tarapul',
            'returned_to_department' => 'Dikembalikan ke Department',
            'returned_to_bidang' => 'Dikembalikan ke Bidang',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
