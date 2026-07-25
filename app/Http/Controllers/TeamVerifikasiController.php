<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\View\View;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\Bidang;
use App\Models\Bagian;
use App\Models\DibayarKepada;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Events\DocumentReturned;
use App\Helpers\SearchHelper;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ActivityLogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeamVerifikasiController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsRoleDashboard;

    /**
     * Halaman dashboard Team Verifikasi.
     */
    public function dashboard()
    {
        $data = $this->buildRoleDashboardData('team_verifikasi');
        $data['title'] = 'Dashboard Team Verifikasi';
        $data['module'] = 'team_verifikasi';
        $data['menuDashboard'] = 'Active';

        return view('dashboard.workflow', $data);
    }

    /**
     * Endpoint JSON tabel Tabulator Team Verifikasi (Rollout 3, Task 2, fix
     * round 1). Query sama dgn dokumens() via buildVerifikasiQuery(); baris
     * via VerifikasiDocumentRow. viewerRole DIKUNCI 'team_verifikasi' (bukan
     * Auth::user()?->role) — endpoint ini eksklusif utk viewer Team Verifikasi
     * (route di-guard role:admin,team_verifikasi,verifikasi).
     * Bentuk balasan {last_page,total,data} — DISAMAKAN PERSIS dgn
     * DashboardPerpajakanController::datatable() (bukan {data:[...]} polos
     * seperti draf awal task ini) karena engine bersama
     * public/js/document-tabulator.js memakai progressiveLoad:'scroll' yang
     * membaca last_page/data dari response; kontrak flat tidak bisa
     * menggerakkannya. Param size/page & default/cap IDENTIK perpajakan.
     * TIDAK ada loadMissing() pasca-paginate di sini (beda dari perpajakan) —
     * bukan lupa: buildVerifikasiQuery() SUDAH mengeager-load dibayarKepadas/
     * roleData/roleStatuses/dokumenPos LEBIH AWAL (di dalam query builder,
     * sebelum dieksekusi), jadi saat paginate() dieksekusi baris sudah lengkap.
     * loadMissing() di sini hanya akan jadi no-op (relationLoaded()===true utk
     * semuanya) — sengaja tidak ditambah supaya tidak ada kode mati.
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildVerifikasiQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        $handlerOptions = $this->buildVerifikasiHandlerOptions();

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\VerifikasiDocumentRow::fromDokumen($d, $handlerOptions, 'team_verifikasi'))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /** Opsi pengurus dokumen (5 peran base + optgroup Bagian). Bentuk identik DokumenController::buildHandlerOptions(). */
    private function buildVerifikasiHandlerOptions(): array
    {
        $handlerOptions = [
            ['value' => 'operator',        'label' => 'Operator'],
            ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
            ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
            ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
            ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
        ];
        $bagian = Bagian::active()->ordered()->get(['kode', 'nama']);
        if ($bagian->isNotEmpty()) {
            $handlerOptions[] = [
                'optgroup' => 'Bagian',
                'options'  => $bagian->map(fn ($b) => ['value' => 'bagian_' . strtolower($b->kode), 'label' => $b->nama ?: $b->kode])->all(),
            ];
        }
        return $handlerOptions;
    }

    /**
     * Pembangun query daftar dokumen Team Verifikasi (cross-role visibility) —
     * SUMBER TUNGGAL dipakai dokumens() (view legacy) & datatable() (JSON
     * Tabulator). Diekstrak VERBATIM dari dokumens() lama (select + leftJoin
     * team_verifikasi_data + sort preference + filter search/tanggal/nilai/
     * status/keterlambatan + eager-load dibayarKepadas/roleData/roleStatuses +
     * withCount dokumenPos/dokumenPrs). SATU-SATUNYA tambahan vs versi lama:
     * eager-load relasi dokumenPos (koleksi, bukan count) — base DTO
     * App\Support\DocumentRow::baseRow() memanggil
     * $dokumen->dokumenPos->pluck('nomor_po'), yang butuh relasi ter-load.
     * withCount(['dokumenPos','dokumenPrs']) TETAP dipertahankan apa adanya
     * karena masih dipakai render legacy (dokumens()/view lama).
     */
    private function buildVerifikasiQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        // Team Verifikasi sees ALL documents (cross-role visibility)
        // Action buttons are disabled for documents not yet at this role (controlled in blade view)
        // Base query - akan dimodifikasi oleh filter status jika ada.
        // Dokumen yang dikembalikan ke bagian tetap ditampilkan di daftar ini;
        // dropdown Pengurus Dokumen menunjukkan bagian tujuan sampai fisiknya kembali.
        $query = Dokumen::with('activityLogs');

        // Exclude CSV imported documents - they are exclusive to Pembayaran module
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');

        // Always exclude CSV imported documents regardless of status filter
        $query->when($hasImportedFromCsvColumn, function ($query) {
            $query->where(function ($q) {
                $q->where('imported_from_csv', false)
                    ->orWhereNull('imported_from_csv');
            });
        });

        $query->leftJoin('dokumen_role_data as team_verifikasi_data', function ($join) {
            $join->on('dokumens.id', '=', 'team_verifikasi_data.dokumen_id')
                ->where('team_verifikasi_data.role_code', '=', 'team_verifikasi');
        })
            ->select([
                'dokumens.id',
                'dokumens.nomor_agenda',
                'dokumens.nomor_spp',
                'dokumens.uraian_spp',
                'dokumens.nilai_rupiah',
                'dokumens.status',
                'dokumens.created_at',
                'dokumens.tanggal_masuk',
                'dokumens.tanggal_spp',
                'dokumens.keterangan',
                // Deadline fields are now in dokumen_role_data table - use aliases for easier access
                'team_verifikasi_data.deadline_at as deadline_at',
                'team_verifikasi_data.deadline_days as deadline_days',
                'team_verifikasi_data.deadline_note as deadline_note',
                'dokumens.current_handler',
                'dokumens.bulan',
                'dokumens.tahun',
                'dokumens.kategori',
                'dokumens.kebun',
                'dokumens.jenis_dokumen',
                'dokumens.jenis_sub_pekerjaan',
                'dokumens.updated_at',
                'dokumens.tanggal_spk',
                'dokumens.tanggal_berakhir_spk',
                'dokumens.no_spk',
                'dokumens.nomor_miro',
                'dokumens.nama_pengirim',
                'dokumens.jenis_pembayaran',
                'dokumens.dibayar_kepada',
                'dokumens.no_berita_acara',
                'dokumens.tanggal_berita_acara',
                'dokumens.bagian', // Added: bagian field for return to bidang modal
                'dokumens.return_source', // Added: for returned_to_verifikasi status badge
                'dokumens.return_reason', // Added: for returned_to_verifikasi reason
                'dokumens.returned_at', // Added: for returned_to_verifikasi timestamp
                // Paraf & processing columns
                'dokumens.tanggal_paraf',
                'dokumens.pemaraf',
                'dokumens.tanggal_selesai_diproses',
                // Perpajakan columns
                'dokumens.no_faktur',
                'dokumens.tanggal_faktur',
                'dokumens.tanggal_selesai_verifikasi_pajak',
                'dokumens.jenis_pph',
                'dokumens.dpp_pph',
                'dokumens.ppn_terhutang',
                'dokumens.tanggal_dibayar',
                // 'dokumens.inbox_approval_responded_at', // REMOVED - now in dokumen_statuses
                // 'dokumens.inbox_approval_reason', // REMOVED
                // 'dokumens.inbox_approval_for', // REMOVED
                // 'dokumens.inbox_approval_status', // REMOVED
                'dokumens.created_by'
            ]);

        // Handle sort preferences with persistence (similar to column preferences)
        $user = Auth::user();

        // Check if sort parameters are in the request (URL)
        if ($request->has('sort') || $request->has('order')) {
            // User is actively changing sort - save preferences
            $sortColumn = $request->get('sort', 'nomor_agenda');
            $sortOrder = $request->get('order', 'asc');

            // Validate sort order to prevent SQL injection
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';


            // Save to database (permanent)
            if ($user && \Schema::hasColumn('users', 'sort_preferences')) {
                // Decode JSON if it's a string, otherwise use as array
                $sortPreferences = is_string($user->sort_preferences)
                    ? json_decode($user->sort_preferences, true) ?? []
                    : ($user->sort_preferences ?? []);

                $sortPreferences['team_verifikasi'] = [
                    'column' => $sortColumn,
                    'order' => $sortOrder
                ];
                $user->sort_preferences = json_encode($sortPreferences);
                $user->save();
            }

            // Also save to session for backward compatibility
            session([
                'team_verifikasi_sort_column' => $sortColumn,
                'team_verifikasi_sort_order' => $sortOrder
            ]);
        } else {
            // No URL params - load from saved preferences
            $sortColumn = 'nomor_agenda'; // default
            $sortOrder = 'asc'; // default

            // Try database first (permanent), then session, then default
            if ($user) {
                // Decode JSON if it's a string
                $sortPrefs = is_string($user->sort_preferences)
                    ? json_decode($user->sort_preferences, true) ?? []
                    : ($user->sort_preferences ?? []);

                if (isset($sortPrefs['team_verifikasi'])) {
                    $saved = $sortPrefs['team_verifikasi'];
                    $sortColumn = $saved['column'] ?? 'nomor_agenda';
                    $sortOrder = $saved['order'] ?? 'asc';
                } else {
                    // Fallback to session
                    $sortColumn = session('team_verifikasi_sort_column', 'nomor_agenda');
                    $sortOrder = session('team_verifikasi_sort_order', 'asc');
                }
            } else {
                // Fallback to session
                $sortColumn = session('team_verifikasi_sort_column', 'nomor_agenda');
                $sortOrder = session('team_verifikasi_sort_order', 'asc');
            }

            // Validate loaded preferences
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';
        }

        // Apply sorting based on column
        if ($sortColumn === 'nomor_agenda') {
            // Natural number sorting for nomor_agenda (extract numeric part before underscore)
            $query->orderByRaw("CAST(
                CASE 
                    WHEN dokumens.nomor_agenda REGEXP '^[0-9]+_[0-9]+$' THEN SUBSTRING_INDEX(dokumens.nomor_agenda, '_', 1)
                    WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN dokumens.nomor_agenda
                    ELSE '0'
                END AS UNSIGNED
            ) {$sortOrder}");
        } else {
            // Fallback to default descending order
            $query->orderByRaw("CAST(
                CASE 
                    WHEN dokumens.nomor_agenda REGEXP '^[0-9]+_[0-9]+$' THEN SUBSTRING_INDEX(dokumens.nomor_agenda, '_', 1)
                    WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN dokumens.nomor_agenda
                    ELSE '0'
                END AS UNSIGNED
            ) DESC");
        }

        // Secondary sorting by received_at and id (always DESC for consistency)
        $query->orderByRaw("
                COALESCE(team_verifikasi_data.received_at, dokumens.created_at) DESC,
                dokumens.id DESC
            ");

        // Enhanced search functionality - search across all relevant fields
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '') {
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
                    ->orWhere('jenis_sub_pekerjaan', 'like', '%' . $search . '%')
                    ->orWhere('no_berita_acara', 'like', '%' . $search . '%')
                    ->orWhere('no_spk', 'like', '%' . $search . '%')
                    ->orWhere('nomor_miro', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%')
                    ->orWhere('dibayar_kepada', 'like', '%' . $search . '%');

                // Search in nilai_rupiah - handle various formats
                $numericSearch = preg_replace('/[^0-9]/', '', $search);
                if (is_numeric($numericSearch) && $numericSearch > 0) {
                    $q->orWhereRaw('CAST(nilai_rupiah AS CHAR) LIKE ?', ['%' . $numericSearch . '%']);
                }
            })
                ->orWhereHas('dibayarKepadas', function ($q) use ($search) {
                    $q->where('nama_penerima', 'like', '%' . $search . '%');
                });
        }

        if ($request->filled('filter_dari')) {
            $query->where('dokumens.bagian', $request->filter_dari);
        }

        if ($request->filled('filter_tanggal_masuk')) {
            try {
                $query->whereDate('dokumens.tanggal_masuk', Carbon::parse($request->filter_tanggal_masuk)->toDateString());
            } catch (\Throwable $e) {
                Log::warning('Team Verifikasi ignored invalid tanggal_masuk filter', [
                    'filter_tanggal_masuk' => $request->filter_tanggal_masuk,
                ]);
            }
        }

        if ($request->filled('filter_nilai_min')) {
            $query->where('dokumens.nilai_rupiah', '>=', (float) preg_replace('/[^0-9]/', '', (string) $request->filter_nilai_min));
        }

        if ($request->filled('filter_nilai_max')) {
            $query->where('dokumens.nilai_rupiah', '<=', (float) preg_replace('/[^0-9]/', '', (string) $request->filter_nilai_max));
        }

        // Filter by status - Apply strict filtering (override base filter)
        if ($request->has('status') && $request->status) {
            $statusFilter = $request->status;
            switch ($statusFilter) {
                case 'menunggu_approve':
                    // Dokumen yang sudah dikirim dari Team Verifikasi dan menunggu approval di role lain
                    // Filter: pending di Perpajakan, Akutansi, atau Pembayaran (BUKAN di Team Verifikasi/inbox)
                    $query->where(function ($q) {
                        // Status dokumen menunjukkan sudah dikirim atau pending approval di role lain
                        $q->whereIn('status', [
                            'sent_to_perpajakan',
                            'sent_to_akutansi',
                            'sent_to_pembayaran',
                            'pending_approval_perpajakan',
                            'pending_approval_akutansi',
                            'pending_approval_pembayaran',
                            'waiting_approval_perpajakan',
                            'waiting_approval_akuntansi',
                            'waiting_approval_pembayaran',
                            'menunggu_di_approve'
                        ])
                            // DAN ada pending status di role selanjutnya
                            ->whereHas('roleStatuses', function ($rq) {
                                $rq->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran'])
                                    ->where('status', DokumenStatus::STATUS_PENDING);
                            });
                    });
                    break;
                case 'sedang_proses':
                    // Dokumen yang sedang diproses oleh Team Verifikasi (Team Verifikasi atau verifikasi)
                    $query->where(function ($q) {
                        $q->where(function ($activeQ) {
                            $activeQ->whereIn('current_handler', ['team_verifikasi', 'team_verifikasi'])
                                // Exclude dokumen yang sudah terkirim ke role lain
                                ->whereNotIn('status', [
                                    'sent_to_perpajakan',
                                    'sent_to_akutansi',
                                    'sent_to_pembayaran',
                                    'returned_to_department',
                                    'returned_to_verifikasi',
                                    'selesai',
                                    'completed'
                                ])
                                // Exclude dokumen yang pending approval
                                ->whereDoesntHave('roleStatuses', function ($rq) {
                                    $rq->where('status', DokumenStatus::STATUS_PENDING);
                                })
                                // Exclude dokumen yang ditolak
                                ->whereDoesntHave('roleStatuses', function ($rq) {
                                    $rq->where('role_code', 'team_verifikasi')->where('status', 'rejected');
                                });
                        })
                            // Keep documents returned to bagian visible in the active verification list.
                            ->orWhere('status', 'returned_to_bidang');
                    });
                    break;
                case 'terkirim_perpajakan':
                    // Dokumen yang terkirim ke perpajakan - hanya status ini saja
                    $query->where('status', 'sent_to_perpajakan');
                    break;
                case 'terkirim_akutansi':
                    // Dokumen yang terkirim ke akutansi - hanya status ini saja
                    $query->where('status', 'sent_to_akutansi');
                    break;
                case 'terkirim_pembayaran':
                    // Dokumen yang terkirim ke pembayaran atau sudah completed setelah pembayaran, exclude CSV imports
                    $query->where(function ($statusQ) {
                        $statusQ->where('status', 'sent_to_pembayaran')
                            ->orWhere(function ($completedQ) {
                                // Include completed documents that have status_pembayaran (indicating they went through pembayaran)
                                $completedQ->whereIn('status', ['completed', 'selesai'])
                                    ->whereNotNull('status_pembayaran');
                            });
                    });
                    // Only exclude CSV imports if column exists
                    if ($hasImportedFromCsvColumn) {
                        $query->where(function ($csvQ) {
                            $csvQ->where('imported_from_csv', false)
                                ->orWhereNull('imported_from_csv');
                        });
                    }
                    break;
                case 'terkirim':
                    // Semua dokumen yang sudah terkirim ke tahap selanjutnya (gabungan semua terkirim)
                    $query->where(function ($statusQ) {
                        $statusQ->whereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran', 'completed', 'selesai']);
                    })
                        ->where('current_handler', '!=', 'team_verifikasi');
                    // Only exclude CSV imports if column exists
                    if ($hasImportedFromCsvColumn) {
                        $query->where(function ($csvQ) {
                            $csvQ->where('imported_from_csv', false)
                                ->orWhereNull('imported_from_csv');
                        });
                    }
                    break;
                case 'ditolak':
                    // Dokumen yang ditolak - termasuk yang ditolak oleh perpajakan/akutansi
                    $query->where(function ($q) {
                        // Dokumen yang ditolak oleh Team Verifikasi sendiri
                        $q->whereHas('roleStatuses', function ($rq) {
                            $rq->where('role_code', 'team_verifikasi')->where('status', 'rejected');
                        })
                            // ATAU dokumen yang ditolak oleh perpajakan/akutansi dan dikembalikan ke verifikasi
                            ->orWhere(function ($rejectQ) {
                                $rejectQ->where('current_handler', 'team_verifikasi')
                                    ->whereHas('roleStatuses', function ($rq) {
                                        $rq->whereIn('role_code', ['perpajakan', 'akutansi'])
                                            ->where('status', 'rejected');
                                    });
                            })
                            // ATAU dokumen dengan status returned_to_department dari perpajakan/akutansi
                            ->orWhere(function ($returnQ) {
                                $returnQ->where('status', 'returned_to_department')
                                    ->where('current_handler', 'team_verifikasi')
                                    ->whereIn('return_source', ['perpajakan', 'akutansi']);
                            })
                            // ATAU dokumen dengan status returned_to_verifikasi (new: current_handler tetap di department)
                            ->orWhere(function ($returnNewQ) {
                                $returnNewQ->where('status', 'returned_to_verifikasi')
                                    ->whereIn('return_source', ['perpajakan', 'akutansi']);
                            });
                    });
                    break;
            }
        }

        // Filter by keterlambatan (deadline card click filter)
        if ($request->has('keterlambatan') && in_array($request->keterlambatan, ['aman', 'peringatan', 'terlambat'])) {
            $keterlambatanFilter = $request->keterlambatan;
            $now = Carbon::now();

            // Get all relevant dokumen IDs with their roleData received_at.
            // Documents returned to bagian stay visible for Team Verifikasi, with the timer paused.
            $allDokumenWithRoleData = Dokumen::when($hasImportedFromCsvColumn, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('imported_from_csv', false)->orWhereNull('imported_from_csv');
                    });
                })
                ->with(['roleData' => function ($q) {
                    $q->where('role_code', 'team_verifikasi');
                }])
                ->get(['id', 'status']);

            $filteredIds = [];
            foreach ($allDokumenWithRoleData as $doc) {
                $roleData = $doc->roleData->first();
                if ($roleData && $roleData->received_at) {
                    $receivedAt = Carbon::parse($roleData->received_at);
                    $isSent = in_array($doc->status, [
                        'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
                        'waiting_approval_perpajakan', 'waiting_approval_akuntansi',
                        'waiting_approval_pembayaran', 'pending_approval_perpajakan', 'pending_approval_akutansi',
                        'returned_to_bidang'
                    ]);
                    $hoursDiff = ($isSent && $roleData->processed_at)
                        ? $receivedAt->diffInHours(Carbon::parse($roleData->processed_at))
                        : $receivedAt->diffInHours($now);

                    $isAman = $hoursDiff < 24;
                    $isPeringatan = $hoursDiff >= 24 && $hoursDiff < 72;
                    $isTerlambat = $hoursDiff >= 72;
                } else {
                    // Tanpa received_at (belum diterima) → NETRAL: tidak masuk kategori mana pun
                    $isAman = false;
                    $isPeringatan = false;
                    $isTerlambat = false;
                }

                if ($keterlambatanFilter === 'aman' && $isAman) {
                    $filteredIds[] = $doc->id;
                } elseif ($keterlambatanFilter === 'peringatan' && $isPeringatan) {
                    $filteredIds[] = $doc->id;
                } elseif ($keterlambatanFilter === 'terlambat' && $isTerlambat) {
                    $filteredIds[] = $doc->id;
                }
            }

            $query->whereIn('dokumens.id', empty($filteredIds) ? [0] : $filteredIds);
        }


        // Use eager loading for relations to prevent N+1 queries
        $query->with([
            'dibayarKepadas',
            'roleData' => function ($query) {
                $query->where('role_code', 'team_verifikasi');
            },
            'roleStatuses' => function ($query) {
                // Load all role statuses to check for pending approvals
                $query->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']);
            }
        ])
            ->withCount([
                'dokumenPos',
                'dokumenPrs'
            ])
            // Tambahan Task 2 (Rollout Tabulator verifikasi) — lihat docblock method:
            // eager-load koleksi dokumenPos (bukan sekadar count) demi DocumentRow::baseRow().
            ->with('dokumenPos');

        return $query;
    }

    public function dokumens(Request $request)
    {
        $query = $this->buildVerifikasiQuery($request);

        $perPage = $request->get('per_page', 'all');
        $showAllRows = $perPage === 'all';
        if ($showAllRows) {
            $perPage = 100;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['verifikasi_per_page' => $showAllRows ? 'all' : $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Cast deadline_at from alias to Carbon if it's a string
        // Also set is_at_my_role flag for cross-role document visibility
        $dokumens->getCollection()->transform(function ($dokumen) {
            if ($dokumen->deadline_at && is_string($dokumen->deadline_at)) {
                try {
                    $dokumen->deadline_at = \Carbon\Carbon::parse($dokumen->deadline_at);
                } catch (\Exception $e) {
                    $dokumen->deadline_at = null;
                }
            }

            // Cross-role visibility: determine if document is at Team Verifikasi's role
            // Documents are "at my role" if:
            // - current_handler is team_verifikasi
            // - status indicates it was sent/processed by team_verifikasi (sent_to_perpajakan, etc.)
            // - status indicates it was returned to verifikasi
            // - status is completed/selesai with status_pembayaran set
            $dokumen->is_at_my_role = in_array($dokumen->current_handler, ['team_verifikasi'])
                || in_array($dokumen->status, [
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve',
                    'waiting_approval_perpajakan',
                    'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran',
                    'returned_to_verifikasi',
                    'sedang diproses',
                    'sedang_diproses',
                ])
                || (in_array($dokumen->status, ['completed', 'selesai']) && !empty($dokumen->status_pembayaran))
                || ($dokumen->status === 'returned_to_department' && in_array($dokumen->return_source, ['perpajakan', 'akutansi']) && $dokumen->current_handler === 'team_verifikasi');

            return $dokumen;
        });

        // Cache statistics for better performance (4 dashboard-style stats)
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');

        // 1. Total Dokumen Agenda - semua dokumen dalam sistem (exclude CSV imports)
        $totalDokumenAgenda = Dokumen::when($hasImportedFromCsvColumn, function ($query) {
            $query->where(function ($q) {
                $q->where('imported_from_csv', false)
                    ->orWhereNull('imported_from_csv');
            });
        })->count();

        // 2. Total Dokumen Verifikasi - dokumen yang terlihat di Team Verifikasi
        $totalDokumenVerifikasi = Dokumen::where(function ($q) {
            $q->whereIn('current_handler', ['team_verifikasi'])
                ->orWhereIn('status', [
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'waiting_approval_perpajakan',
                    'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve'
                ]);
        })
            ->when($hasImportedFromCsvColumn, function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                        ->orWhereNull('imported_from_csv');
                });
            })
            ->count();

        // 3. Total Terkirim - dokumen yang sudah dikirim ke tahap selanjutnya
        $totalTerkirim = Dokumen::whereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran', 'completed', 'selesai'])
            ->where('current_handler', '!=', 'team_verifikasi')
            ->when($hasImportedFromCsvColumn, function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                        ->orWhereNull('imported_from_csv');
                });
            })
            ->count();

        // Keterlambatan: hitung berdasarkan waktu dokumen diterima dari roleData
        $now = Carbon::now();
        $teamDokumensForDelay = Dokumen::where(function ($q) {
            $q->whereIn('current_handler', ['team_verifikasi'])
                ->orWhereIn('status', [
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'waiting_approval_perpajakan',
                    'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve'
                ]);
        })
            ->when($hasImportedFromCsvColumn, function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                        ->orWhereNull('imported_from_csv');
                });
            })
            ->with(['roleData' => function ($q) {
                $q->where('role_code', 'team_verifikasi');
            }])
            ->get();

        $dokumenLessThan24h = 0;
        $dokumen24to72h = 0;
        $dokumenMoreThan72h = 0;

        foreach ($teamDokumensForDelay as $doc) {
            $roleData = $doc->roleData->first();
            if ($roleData && $roleData->received_at) {
                $receivedAt = Carbon::parse($roleData->received_at);
                $isSent = in_array($doc->status, [
                    'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
                    'waiting_approval_perpajakan', 'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran', 'pending_approval_perpajakan', 'pending_approval_akutansi',
                    'returned_to_bidang'
                ]);
                if ($isSent && $roleData->processed_at) {
                    $hoursDiff = $receivedAt->diffInHours(Carbon::parse($roleData->processed_at));
                } else {
                    $hoursDiff = $receivedAt->diffInHours($now);
                }
                if ($hoursDiff < 24) {
                    $dokumenLessThan24h++;
                } elseif ($hoursDiff < 72) {
                    $dokumen24to72h++;
                } else {
                    $dokumenMoreThan72h++;
                }
            } else {
                $dokumenMoreThan72h++;
            }
        }

        // Total nilai rupiah dokumen verifikasi
        $totalNilaiRupiah = Dokumen::where(function ($q) {
            $q->whereIn('current_handler', ['team_verifikasi'])
                ->orWhereIn('status', [
                    'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran',
                    'waiting_approval_perpajakan', 'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran', 'pending_approval_perpajakan',
                    'pending_approval_akutansi', 'pending_approval_pembayaran', 'menunggu_di_approve'
                ]);
        })
            ->when($hasImportedFromCsvColumn, function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)->orWhereNull('imported_from_csv');
                });
            })
            ->sum('nilai_rupiah');

        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'team_verifikasi');
        }

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        // Kolom tersedia = base terpusat tanpa 'status' (Verifikasi punya kolom
        // Status tetap, bukan opsi kustomisasi). Sumber: config/document_columns.php.
        $availableColumns = \Illuminate\Support\Arr::except(config('document_columns.base'), ['status']);

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Filter out 'status' and 'keterangan' from selectedColumns if present
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'status';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array

        // If columns are provided in request, save to database and session
        if ($request->has('columns') && !empty($selectedColumns)) {
            // Save to database (permanent)
            $user = Auth::user();
            if ($user) {
                $preferences = $user->table_columns_preferences ?? [];
                $preferences['team_verifikasi'] = $selectedColumns;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }
            // Also save to session for backward compatibility
            session(['team_verifikasi_dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from database first (permanent), then fallback to session, then default
            $user = Auth::user();
            $defaultColumns = [
                'nomor_agenda',
                'nomor_spp',
                'tanggal_masuk',
                'nilai_rupiah',
                'nomor_miro',
                'link'
            ];

            if ($user && isset($user->table_columns_preferences['team_verifikasi'])) {
                $selectedColumns = $user->table_columns_preferences['team_verifikasi'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('team_verifikasi_dokumens_table_columns', $defaultColumns);
            }

            // Filter out 'status' and 'keterangan' if they exist
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'status';
            });
            $selectedColumns = array_values($selectedColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['team_verifikasi_dokumens_table_columns' => $selectedColumns]);
        }

        // Load IE dropdown data
        $ieKategoriList = $ieSubKriteriaList = $ieItemSubKriteriaList = $ieJenisPembayaranList = [];
        try {
            $ieKategoriList = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get(['id_kategori_kriteria as id', 'nama_kriteria'])->toArray();
            $ieSubKriteriaList = \App\Models\SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])->toArray();
            $ieItemSubKriteriaList = \App\Models\ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])->toArray();
            $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])->toArray();
        } catch (\Exception $e) {
            \Log::error('IE dropdown load error (verifikasi): ' . $e->getMessage());
        }
        if (empty($ieKategoriList)) {
            $ieKategoriList = \App\Models\Dokumen::whereNotNull('kategori')->where('kategori','!=','')->distinct()->orderBy('kategori')->pluck('kategori')->map(fn($v)=>['id'=>$v,'nama_kriteria'=>$v])->toArray();
        }
        if (empty($ieSubKriteriaList)) {
            $ieSubKriteriaList = \App\Models\Dokumen::whereNotNull('jenis_dokumen')->where('jenis_dokumen','!=','')->distinct()->orderBy('jenis_dokumen')->get(['jenis_dokumen','kategori'])->unique('jenis_dokumen')->map(fn($d)=>['id'=>$d->jenis_dokumen,'nama_sub_kriteria'=>$d->jenis_dokumen,'id_kategori_kriteria'=>$d->kategori])->values()->toArray();
        }
        if (empty($ieItemSubKriteriaList)) {
            $ieItemSubKriteriaList = \App\Models\Dokumen::whereNotNull('jenis_sub_pekerjaan')->where('jenis_sub_pekerjaan','!=','')->distinct()->orderBy('jenis_sub_pekerjaan')->get(['jenis_sub_pekerjaan','jenis_dokumen'])->unique('jenis_sub_pekerjaan')->map(fn($d)=>['id'=>$d->jenis_sub_pekerjaan,'nama_item_sub_kriteria'=>$d->jenis_sub_pekerjaan,'id_sub_kriteria'=>$d->jenis_dokumen])->values()->toArray();
        }
        if (empty($ieJenisPembayaranList)) {
            $ieJenisPembayaranList = \App\Models\Dokumen::whereNotNull('jenis_pembayaran')->where('jenis_pembayaran','!=','')->distinct()->orderBy('jenis_pembayaran')->pluck('jenis_pembayaran')->map(fn($v)=>['id_jenis_pembayaran'=>$v,'nama_jenis_pembayaran'=>$v])->toArray();
        }
        $filterDariOptions = Dokumen::when($hasImportedFromCsvColumn, function ($query) {
            $query->where(function ($q) {
                $q->where('imported_from_csv', false)
                    ->orWhereNull('imported_from_csv');
            });
        })
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->distinct()
            ->orderBy('bagian')
            ->pluck('bagian', 'bagian')
            ->toArray();

        $data = array(
            "title" => "Daftar Dokumen Team Verifikasi",
            "module" => "team_verifikasi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'totalDokumenAgenda' => $totalDokumenAgenda,
            'totalDokumenVerifikasi' => $totalDokumenVerifikasi,
            'totalTerkirim' => $totalTerkirim,
            'dokumenLessThan24h' => $dokumenLessThan24h,
            'dokumen24to72h' => $dokumen24to72h,
            'dokumenMoreThan72h' => $dokumenMoreThan72h,
            'totalNilaiRupiah' => $totalNilaiRupiah,
            'suggestions' => $suggestions,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
            'ieKategoriList' => $ieKategoriList,
            'ieSubKriteriaList' => $ieSubKriteriaList,
            'ieItemSubKriteriaList' => $ieItemSubKriteriaList,
            'ieJenisPembayaranList' => $ieJenisPembayaranList,
            'filterDariOptions' => $filterDariOptions,
        );

        // Rollout 3 selesai (Task 6): view legacy + cabang ?classic dihapus permanen.
        // dokumens() sekarang selalu menyajikan Tabulator; ?classic=1 jadi no-op.
        return view('team_verifikasi.dokumens.daftarDokumenTabulator', $data);
    }

    /**
     * Daftar Pengembalian Dokumen ke Bidang
     */
    public function pengembalianKeBidang(Request $request)
    {
        // Get documents returned to bidang. Older records may have current_handler = bagian_xxx,
        // so the page should key from the return status/source instead of hiding those rows.
        $query = Dokumen::where('status', 'returned_to_bidang')
            ->latest('returned_at');

        // Filter by specific bidang if provided
        if ($request->has('bidang') && $request->bidang) {
            $query->where('return_source', $request->bidang);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                    ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                    ->orWhere('uraian_spp', 'like', '%' . $search . '%');
            });
        }

        // Get paginated results
        $dokumens = $query->select([
            'id',
            'nomor_agenda',
            'nomor_spp',
            'uraian_spp',
            'nilai_rupiah',
            'return_source',
            'returned_at',
            'return_reason',
            'status',
            'created_at',
            'updated_at',
            'bulan',
            'tahun'
        ]);
        $perPage = $request->get('per_page', session('verifikasi_bidang_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['verifikasi_bidang_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Get statistics
        $totalReturned = Dokumen::where('status', 'returned_to_bidang')
            ->count();

        // Map bidang codes to names (hardcoded)
        $bidangList = [
            'AKN' => 'Akuntansi',
            'DPM' => 'Divisi Produksi dan Manufaktur',
            'KPL' => 'Keuangan dan Pelaporan',
            'PMO' => 'Project Management Office',
            'PTI' => 'Pengadaan dan Teknologi Informasi',
            'SDM' => 'Sumber Daya Manusia',
            'SKH' => 'Sub Kontrak Hutan',
            'TAN' => 'Tanaman dan Perkebunan',
            'TEP' => 'Teknik dan Perencanaan',
        ];

        $bidangStats = [];
        foreach ($bidangList as $kode => $nama) {
            $count = Dokumen::where('status', 'returned_to_bidang')
                ->where('return_source', $kode)
                ->count();

            $bidangStats[] = [
                'kode_bidang' => $kode,
                'nama_bidang' => $nama,
                'count' => $count
            ];
        }

        $data = array(
            "title" => "Daftar Pengembalian Dokumen ke Bidang",
            "module" => "team_verifikasi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuPengembalianKeBidang' => "Active",
            'dokumens' => $dokumens,
            'totalReturned' => $totalReturned,
            'bidangStats' => $bidangStats,
            'selectedBidang' => $request->bidang
        );

        return view('team_verifikasi.dokumens.pengembalianKeBidang', $data);
    }

    /**
     * Return document to bidang (auto-detect from bagian field)
     */
    public function returnToBidang(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if current_handler is Team Verifikasi and status is appropriate
            if ($dokumen->current_handler !== 'team_verifikasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengembalikan dokumen ini ke bidang.'
                ], 403);
            }

            // Auto-detect bidang from document's bagian field
            $bagian = $dokumen->bagian;

            // Map bagian names to bidang codes (handle various naming conventions)
            $bagianToBidangMap = [
                // AKN
                'AKN' => 'AKN',
                'Akuntansi' => 'AKN',
                // DPM
                'DPM' => 'DPM',
                'Divisi Produksi dan Manufaktur' => 'DPM',
                'Produksi' => 'DPM',
                // KPL
                'KPL' => 'KPL',
                'Keuangan dan Pelaporan' => 'KPL',
                'Keuangan' => 'KPL',
                // PMO
                'PMO' => 'PMO',
                'Project Management Office' => 'PMO',
                // PTI
                'PTI' => 'PTI',
                'Pengadaan dan Teknologi Informasi' => 'PTI',
                'Pengadaan' => 'PTI',
                'Teknologi Informasi' => 'PTI',
                'IT' => 'PTI',
                // SDM
                'SDM' => 'SDM',
                'Sumber Daya Manusia' => 'SDM',
                'HR' => 'SDM',
                // SKH
                'SKH' => 'SKH',
                'Sub Kontrak Hutan' => 'SKH',
                // TAN
                'TAN' => 'TAN',
                'Tanaman dan Perkebunan' => 'TAN',
                'Tanaman' => 'TAN',
                // TEP
                'TEP' => 'TEP',
                'Teknik dan Perencanaan' => 'TEP',
                'Teknik' => 'TEP',
            ];

            // Try to find matching bidang code
            $targetBidang = null;
            if ($bagian) {
                // Direct match
                if (isset($bagianToBidangMap[$bagian])) {
                    $targetBidang = $bagianToBidangMap[$bagian];
                } else {
                    // Case-insensitive partial match
                    foreach ($bagianToBidangMap as $name => $code) {
                        if (stripos($bagian, $name) !== false || stripos($name, $bagian) !== false) {
                            $targetBidang = $code;
                            break;
                        }
                    }
                }
            }

            // If still no match, check if bagian itself is a valid code
            $validCodes = ['AKN', 'DPM', 'KPL', 'PMO', 'PTI', 'SDM', 'SKH', 'TAN', 'TEP'];
            if (!$targetBidang && $bagian && in_array(strtoupper($bagian), $validCodes)) {
                $targetBidang = strtoupper($bagian);
            }

            // If no bidang detected, return error
            if (!$targetBidang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mendeteksi bidang asal dokumen. Field bagian kosong atau tidak valid.',
                    'bagian_value' => $bagian
                ], 422);
            }

            // Validate optional reason (only if provided)
            $request->validate([
                'bidang_return_reason' => 'required|string|min:10|max:1000'
            ], [
                'bidang_return_reason.required' => 'Alasan pengembalian ke bidang wajib diisi.',
                'bidang_return_reason.min' => 'Alasan pengembalian minimal 10 karakter.',
                'bidang_return_reason.max' => 'Alasan pengembalian maksimal 1000 karakter.'
            ]);

            \DB::beginTransaction();

            // Update document with bidang return information
            $dokumen->update([
                'status' => 'returned_to_bidang',
                'current_handler' => 'team_verifikasi', // Tetap di verifikasi untuk tracking
                'return_source' => $targetBidang,
                // Unified return fields
                'return_reason' => $request->bidang_return_reason,
                'returned_at' => now(),
            ]);

            \DB::commit();

            // Log activity: dokumen dikembalikan ke bidang oleh Team Verifikasi
            try {
                \App\Helpers\ActivityLogHelper::logReturned(
                    $dokumen,
                    $targetBidang,
                    $request->bidang_return_reason,
                    'team_verifikasi'
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log activity for returnToBidang: ' . $logException->getMessage());
            }

            \Log::info('Document returned to bidang', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'original_bagian' => $bagian,
                'return_source' => $targetBidang,
                'reason' => $request->bidang_return_reason ?? 'Dikembalikan ke bidang asal'
            ]);

            // Map bidang codes to names
            $bidangNames = [
                'AKN' => 'Akuntansi',
                'DPM' => 'Divisi Produksi dan Manufaktur',
                'KPL' => 'Keuangan dan Pelaporan',
                'PMO' => 'Project Management Office',
                'PTI' => 'Pengadaan dan Teknologi Informasi',
                'SDM' => 'Sumber Daya Manusia',
                'SKH' => 'Sub Kontrak Hutan',
                'TAN' => 'Tanaman dan Perkebunan',
                'TEP' => 'Teknik dan Perencanaan',
            ];

            $bidangName = $bidangNames[$targetBidang] ?? $targetBidang;

            // R6: Kirim notifikasi ke user Bagian setelah dokumen dikembalikan
            try {
                // Cari user dengan bagian_code yang sesuai
                $bagianUsers = \App\Models\User::where('bagian_code', $targetBidang)
                    ->whereNotNull('phone_number')
                    ->where('phone_number', '!=', '')
                    ->get();

                if ($bagianUsers->isNotEmpty()) {
                    $docUrl  = url(route('inbox.show', $dokumen->id, false));
                    $reason  = $request->bidang_return_reason;
                    $agenda  = $dokumen->nomor_agenda ?? 'N/A';
                    $message = "🔔 *NOTIFIKASI SISTEM AGENDA ONLINE*\n\n"
                        . "Dokumen dengan nomor agenda *{$agenda}* telah *dikembalikan* ke Bidang {$bidangName}.\n\n"
                        . "📋 *Alasan Pengembalian:*\n{$reason}\n\n"
                        . "Silakan lakukan perbaikan dan kirim ulang dokumen.\n\n"
                        . "🔗 Lihat dokumen: {$docUrl}";

                    $whatsAppService = app(\App\Services\FonnteWhatsAppService::class);

                    foreach ($bagianUsers as $bagianUser) {
                        $whatsAppService->sendMessage($bagianUser->phone_number, $message);
                    }

                    \Log::info('[R6] WhatsApp notification sent for returnToBidang', [
                        'dokumen_id'   => $dokumen->id,
                        'target_bidang' => $targetBidang,
                        'notified_count' => $bagianUsers->count(),
                    ]);
                } else {
                    // Fallback: Database notification (in-app) jika tidak ada nomor HP
                    $bagianUsersAll = \App\Models\User::where('bagian_code', $targetBidang)->get();
                    foreach ($bagianUsersAll as $bagianUser) {
                        $bagianUser->notify(new \App\Notifications\DokumenDikembalikanNotification(
                            $dokumen,
                            $request->bidang_return_reason,
                            $bidangName
                        ));
                    }

                    \Log::info('[R6] In-app notification sent (no phone) for returnToBidang', [
                        'dokumen_id'    => $dokumen->id,
                        'target_bidang' => $targetBidang,
                    ]);
                }
            } catch (\Exception $notifException) {
                // Notifikasi gagal tidak boleh menghentikan alur utama
                \Log::error('[R6] Failed to send return notification: ' . $notifException->getMessage(), [
                    'dokumen_id' => $dokumen->id,
                ]);
            }

            return response()->json([
                'success'      => true,
                'message'      => "Dokumen berhasil dikembalikan ke bidang {$bidangName}.",
                'return_source' => $targetBidang,
                'bidang_name'  => $bidangName,
                'reason'       => $request->bidang_return_reason
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error returning document to bidang: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan dokumen ke bidang.'
            ], 500);
        }
    }



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
     * Get search suggestions when no results found
     */
    private function getSearchSuggestions($searchTerm, $year = null, $handler = 'team_verifikasi'): array
    {
        $suggestions = [];

        // Get all unique values from relevant fields
        $baseQuery = Dokumen::where(function ($q) use ($handler) {
            $q->where('current_handler', $handler)
                ->orWhere(function ($subQ) {
                    $subQ->where('status', 'sedang_diproses')
                        ->where('current_handler', 'team_verifikasi');
                })
                ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
        })
            ->where('status', '!=', 'returned_to_bidang');

        if ($year) {
            $baseQuery->where('tahun', $year);
        }

        // Collect all searchable values
        $allValues = collect();

        // Get from main fields
        $fields = [
            'nomor_agenda',
            'nomor_spp',
            'uraian_spp',
            'nama_pengirim',
            'bagian',
            'kategori',
            'jenis_dokumen',
            'no_berita_acara',
            'no_spk',
            'nomor_miro',
            'keterangan',
            'dibayar_kepada'
        ];

        foreach ($fields as $field) {
            $values = $baseQuery->whereNotNull($field)
                ->distinct()
                ->pluck($field)
                ->filter()
                ->toArray();
            $allValues = $allValues->merge($values);
        }

        // Get from dibayarKepadas relation
        $dibayarKepadaQuery = DibayarKepada::whereHas('dokumen', function ($q) use ($handler, $year) {
            $q->where(function ($subQ) use ($handler) {
                $subQ->where('current_handler', $handler)
                    ->orWhere(function ($subSubQ) {
                        $subSubQ->where('status', 'sedang_diproses')
                            ->where('current_handler', 'team_verifikasi');
                    })
                    ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
            })
                ->where('status', '!=', 'returned_to_bidang');
            if ($year) {
                $q->where('tahun', $year);
            }
        });

        $dibayarKepadaValues = $dibayarKepadaQuery
            ->distinct()
            ->pluck('nama_penerima')
            ->filter()
            ->toArray();

        $allValues = $allValues->merge($dibayarKepadaValues);

        // Remove duplicates and find suggestions
        $uniqueValues = $allValues->unique()->values()->toArray();
        $foundSuggestions = SearchHelper::findSuggestions($searchTerm, $uniqueValues, 60.0, 5);

        // Format suggestions
        foreach ($foundSuggestions as $suggestion) {
            $suggestions[] = $suggestion['value'];
        }

        return $suggestions;
    }

    /**
     * API endpoint untuk check dokumen yang di-reject dari inbox untuk Team Verifikasi
     */
    public function checkRejectedDocuments(Request $request)
    {
        try {
            $user = auth()->user();

            // Hanya allow Team Verifikasi
            if (!$user || !in_array(strtolower($user->role), ['team_verifikasi', 'ibu b', 'ibu yuni'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get last check time from request (dari localStorage client)
            $lastCheckTime = $request->input('last_check_time');

            // Cari dokumen yang di-reject dalam 24 jam terakhir (untuk memastikan notifikasi selalu muncul)
            // Jika ada lastCheckTime, gunakan yang lebih lama antara lastCheckTime atau 24 jam yang lalu
            $checkFrom24Hours = now()->subHours(24);

            // Initialize $checkFrom dengan default value
            $checkFrom = $checkFrom24Hours;

            try {
                if ($lastCheckTime) {
                    $parsedTime = \Carbon\Carbon::parse($lastCheckTime);
                    // Gunakan waktu yang lebih lama untuk memastikan tidak ada yang terlewat
                    $checkFrom = $parsedTime->gt($checkFrom24Hours) ? $checkFrom24Hours : $parsedTime;
                }
            } catch (\Exception $e) {
                \Log::warning('Invalid last_check_time format for Team Verifikasi, using 24 hours ago', [
                    'last_check_time' => $lastCheckTime,
                    'error' => $e->getMessage()
                ]);
                // $checkFrom already set to $checkFrom24Hours as default
            }

            \Log::info('Team Verifikasi checkRejectedDocuments called', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'last_check_time' => $lastCheckTime,
                'check_from' => $checkFrom->toIso8601String(),
            ]);

            // Cari dokumen yang di-reject dari inbox Perpajakan atau Akutansi dalam 24 jam terakhir
            // Menggunakan dokumen_statuses table yang baru
            $rejectedDocuments = Dokumen::where('current_handler', 'team_verifikasi')
                ->whereHas('roleStatuses', function ($query) use ($checkFrom) {
                    $query->whereIn('role_code', ['perpajakan', 'akutansi'])
                        ->where('status', 'rejected')
                        ->where('status_changed_at', '>=', $checkFrom);
                })
                ->with([
                    'roleStatuses' => function ($query) {
                        $query->whereIn('role_code', ['perpajakan', 'akutansi'])
                            ->where('status', 'rejected')
                            ->latest('status_changed_at');
                    },
                    'activityLogs'
                ])
                ->get()
                ->filter(function ($doc) {
                    // Filter to only include documents with rejection status
                    return $doc->roleStatuses->where('status', 'rejected')->isNotEmpty();
                })
                ->sortByDesc(function ($doc) {
                    $rejectedStatus = $doc->roleStatuses->where('status', 'rejected')->first();
                    return $rejectedStatus?->status_changed_at ?? now();
                })
                ->take(50)
                ->values();

            // Hitung total rejected
            $totalRejected = Dokumen::where('current_handler', 'team_verifikasi')
                ->whereHas('roleStatuses', function ($query) {
                    $query->whereIn('role_code', ['perpajakan', 'akutansi'])
                        ->where('status', 'rejected');
                })
                ->count();

            return response()->json([
                'success' => true,
                'rejected_documents_count' => $rejectedDocuments->count(),
                'total_rejected' => $totalRejected,
                'rejected_documents' => $rejectedDocuments->map(function ($doc) {
                    // Get rejected status from dokumen_statuses
                    $rejectedStatus = $doc->roleStatuses
                        ->where('status', 'rejected')
                        ->whereIn('role_code', ['perpajakan', 'akutansi'])
                        ->sortByDesc('status_changed_at')
                        ->first();

                    // Get rejected by name from activity log
                    $rejectLog = $doc->activityLogs()
                        ->where('action', 'rejected')
                        ->whereIn('stage', ['perpajakan', 'akutansi'])
                        ->latest('action_at')
                        ->first();

                    $rejectedBy = 'Unknown';
                    $rejectionReason = '-';

                    if ($rejectedStatus) {
                        $rejectedBy = $rejectedStatus->changed_by ?? 'Unknown';
                        $rejectionReason = $rejectedStatus->notes ?? '-';

                        // Map role to display name
                        $nameMap = [
                            'Perpajakan' => 'Team Perpajakan',
                            'perpajakan' => 'Team Perpajakan',
                            'Akutansi' => 'Team Akutansi',
                            'akutansi' => 'Team Akutansi',
                        ];
                        $roleCode = $rejectedStatus->role_code;
                        if (isset($nameMap[$roleCode])) {
                            $rejectedBy = $nameMap[$roleCode];
                        }
                    }

                    if ($rejectLog) {
                        $rejectedBy = $rejectLog->performed_by ?? $rejectedBy;
                        if (isset($rejectLog->details['rejection_reason'])) {
                            $rejectionReason = $rejectLog->details['rejection_reason'];
                        }
                    }

                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => \Illuminate\Support\Str::limit($doc->uraian_spp ?? '-', 50),
                        'nilai_rupiah' => $doc->formatted_nilai_rupiah ?? 'Rp 0',
                        'rejected_at' => $rejectedStatus?->status_changed_at?->format('d/m/Y H:i') ?? '-',
                        'rejected_by' => $rejectedBy,
                        'rejection_reason' => \Illuminate\Support\Str::limit($rejectionReason, 100),
                        'url' => route('team_verifikasi.rejected.show', $doc->id),
                    ];
                }),
                'current_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking rejected documents for Team Verifikasi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'last_check_time' => $request->input('last_check_time')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa dokumen yang ditolak: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail dokumen yang di-reject dari inbox Perpajakan/Akutansi untuk Team Verifikasi
     */
    public function showRejectedDocument(Dokumen $dokumen)
    {
        try {
            $user = auth()->user();

            // Hanya allow Team Verifikasi
            if (!$user || !in_array(strtolower($user->role), ['team_verifikasi', 'ibu b', 'ibu yuni'])) {
                abort(403, 'Unauthorized access');
            }

            // Validasi: dokumen harus di-reject dari inbox Perpajakan/Akutansi dan dikembalikan ke Team Verifikasi
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->whereIn('role_code', ['perpajakan', 'akutansi'])
                ->first();

            if (
                !$rejectedStatus ||
                strtolower($dokumen->current_handler) !== 'team_verifikasi'
            ) {
                abort(404, 'Dokumen tidak ditemukan atau tidak valid');
            }

            // Get rejected by name from activity log
            $rejectLog = $dokumen->activityLogs()
                ->where('action', 'inbox_rejected')
                ->latest('action_at')
                ->first();

            $rejectedBy = 'Unknown';
            if ($rejectLog) {
                $rejectedBy = $rejectLog->performed_by ?? $rejectLog->details['rejected_by'] ?? 'Unknown';
                // Map role to display name
                $nameMap = [
                    'Perpajakan' => 'Team Perpajakan',
                    'perpajakan' => 'Team Perpajakan',
                    'Akutansi' => 'Team Akutansi',
                    'akutansi' => 'Team Akutansi',
                ];
                $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;
            } else if ($rejectedStatus) {
                $nameMap = [
                    'perpajakan' => 'Team Perpajakan',
                    'akutansi' => 'Team Akutansi',
                ];
                $rejectedBy = $nameMap[$rejectedStatus->role_code] ?? ucfirst($rejectedStatus->role_code);
            }

            $data = [
                "title" => "Detail Dokumen Ditolak",
                "module" => "team_verifikasi",
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "dokumen" => $dokumen,
                "rejectedBy" => $rejectedBy,
                "rejectionReason" => $rejectedStatus->notes ?? '-',
                "rejectedAt" => $rejectedStatus->status_changed_at ?? null,
            ];

            return view('team_verifikasi.rejected-detail', $data);

        } catch (\Exception $e) {
            \Log::error('Error showing rejected document for Team Verifikasi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail dokumen yang ditolak');
        }
    }





}
