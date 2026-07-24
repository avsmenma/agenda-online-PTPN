<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use App\Models\DokumenStatus;
use App\Helpers\SearchHelper;
use App\Models\Bagian;
use App\Models\DocumentTracking;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardPerpajakanController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsRoleDashboard;

    /**
     * Halaman dashboard Team Perpajakan.
     */
    public function dashboard()
    {
        $data = $this->buildRoleDashboardData('perpajakan');
        $data['title'] = 'Dashboard Team Perpajakan';
        $data['module'] = 'perpajakan';
        $data['menuDashboard'] = 'Active';

        return view('dashboard.workflow', $data);
    }

    /**
     * Endpoint JSON tabel Tabulator perpajakan. {last_page,total,data} (cocok progressiveLoad).
     * Query sama dgn dokumens() via buildPerpajakanQuery(); baris via PerpajakanDocumentRow.
     * Eager-load roleData perpajakan-only + roleStatuses 4 role (parity byte tabel lama).
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildPerpajakanQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        // Eager-load relasi PERSIS seperti dokumens() (loadMissing pasca-paginate).
        $paginator->getCollection()->loadMissing([
            'roleData'     => fn ($q) => $q->where('role_code', 'perpajakan'),
            'roleStatuses' => fn ($q) => $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']),
            'dibayarKepadas', 'dokumenPos',
        ]);

        $handlerOptions = $this->buildPerpajakanHandlerOptions();
        $viewerRole = Auth::user()?->role;

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\PerpajakanDocumentRow::fromDokumen($d, $handlerOptions, $viewerRole))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /** Opsi pengurus dokumen (5 peran base + optgroup Bagian). Bentuk identik DokumenController::buildHandlerOptions(). */
    private function buildPerpajakanHandlerOptions(): array
    {
        $handlerOptions = [
            ['value' => 'operator',        'label' => 'Operator'],
            ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
            ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
            ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
            ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
        ];
        $bagian = \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']);
        if ($bagian->isNotEmpty()) {
            $handlerOptions[] = [
                'optgroup' => 'Bagian',
                'options'  => $bagian->map(fn ($b) => ['value' => 'bagian_' . strtolower($b->kode), 'label' => $b->nama ?: $b->kode])->all(),
            ];
        }
        return $handlerOptions;
    }

    /**
     * Pembangun query daftar dokumen perpajakan (cross-role visibility) —
     * SUMBER TUNGGAL dipakai dokumens() (view) & datatable() (JSON). Meliputi
     * base query, search, filter (dari/tanggal/nilai), switch status, JOIN
     * dokumen_role_data (perpajakan_data, dipakai sort sekunder received_at),
     * dan sort natural nomor_agenda / kolom lain.
     *
     * PENTING: roleData/roleStatuses TIDAK di-eager-load di sini (beda dgn
     * Akutansi) — keduanya dimuat via loadMissing() setelah paginate, baik di
     * dokumens() maupun datatable(), demi paritas persis dgn tabel lama.
     */
    private function buildPerpajakanQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        // Perpajakan sees ALL documents (cross-role visibility)
        // Action buttons are disabled for documents not yet at this role (controlled in blade view)
        // Exclude documents that are returned to bidang and CSV imports
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');

        $query = Dokumen::query()
            ->where('status', '!=', 'returned_to_bidang')
            ->excludeCsvImports()
            ->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

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
                Log::warning('Perpajakan ignored invalid tanggal_masuk filter', [
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

        // Filter by status
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'sedang_proses':
                    // Dokumen yang sedang diproses oleh perpajakan
                    $query->where('current_handler', 'perpajakan')
                        ->whereNotIn('status', [
                            'sent_to_akutansi',
                            'sent_to_pembayaran',
                            'pending_approval_akutansi',
                            'pending_approval_pembayaran',
                            'completed',
                            'selesai'
                        ])
                        // Exclude dokumen yang pending approval dari perpajakan
                        ->whereDoesntHave('roleStatuses', function ($statusQ) {
                            $statusQ->where('role_code', 'perpajakan')
                                ->where('status', DokumenStatus::STATUS_PENDING);
                        })
                        // Exclude dokumen yang ditolak oleh perpajakan
                        ->whereDoesntHave('roleStatuses', function ($statusQ) {
                            $statusQ->where('role_code', 'perpajakan')
                                ->where('status', DokumenStatus::STATUS_REJECTED);
                        });
                    break;
                case 'terkirim_akutansi':
                    // Dokumen yang sudah terkirim ke team akutansi
                    $query->where('status', 'sent_to_akutansi');
                    break;
                case 'terkirim_pembayaran':
                    // Dokumen yang sudah terkirim ke team pembayaran
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
                case 'menunggu_approve':
                    // Semua dokumen dengan status menunggu approve (pending di dokumen_statuses untuk role apapun)
                    // atau dokumen dengan status pending_approval_* atau menunggu_di_approve
                    $query->where(function ($q) {
                        $q->whereHas('roleStatuses', function ($statusQ) {
                            $statusQ->where('status', DokumenStatus::STATUS_PENDING);
                        })
                            ->orWhereIn('status', [
                                'pending_approval_team_verifikasi',
                                'pending_approval_perpajakan',
                                'pending_approval_akutansi',
                                'pending_approval_pembayaran',
                                'waiting_reviewer_approval',
                                'menunggu_di_approve'
                            ]);
                    });
                    break;
                case 'terkirim':
                    // Semua dokumen yang sudah terkirim ke tahap selanjutnya (gabungan semua terkirim)
                    $query->where(function ($statusQ) {
                        $statusQ->whereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran', 'completed', 'selesai']);
                    })
                        ->where('current_handler', '!=', 'perpajakan');
                    // Only exclude CSV imports if column exists
                    if ($hasImportedFromCsvColumn) {
                        $query->where(function ($csvQ) {
                            $csvQ->where('imported_from_csv', false)
                                ->orWhereNull('imported_from_csv');
                        });
                    }
                    break;
                case 'ditolak':
                    // Dokumen yang ditolak (rejected di dokumen_statuses)
                    // Include both: rejected by perpajakan AND rejected by akutansi (returned to perpajakan)
                    $query->where(function ($q) {
                        // Documents rejected by perpajakan
                        $q->whereHas('roleStatuses', function ($statusQ) {
                            $statusQ->where('role_code', 'perpajakan')
                                ->where('status', DokumenStatus::STATUS_REJECTED);
                        })
                            // Documents rejected by akutansi (returned to perpajakan)
                            ->orWhere(function ($akutansiQ) {
                                $akutansiQ->where('current_handler', 'perpajakan')
                                    ->whereHas('roleStatuses', function ($statusQ) {
                                        $statusQ->where('role_code', 'akutansi')
                                            ->where('status', DokumenStatus::STATUS_REJECTED);
                                    });
                            });
                    });
                    break;
            }
        }

        // === Sort/Order handling ===
        if ($request->has('sort') || $request->has('order')) {
            $sortColumn = $request->get('sort', 'nomor_agenda');
            $sortOrder = $request->get('order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
            session(['perpajakan_sort_column' => $sortColumn, 'perpajakan_sort_order' => $sortOrder]);
        } else {
            $sortColumn = session('perpajakan_sort_column', 'nomor_agenda');
            $sortOrder = session('perpajakan_sort_order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        }

        $query = $query
            ->leftJoin('dokumen_role_data as perpajakan_data', function ($join) {
                $join->on('dokumens.id', '=', 'perpajakan_data.dokumen_id')
                    ->where('perpajakan_data.role_code', '=', 'perpajakan');
            })
            ->select('dokumens.*');

        // Apply sorting based on column
        if ($sortColumn === 'nomor_agenda') {
            $query->orderByRaw("CASE
                WHEN dokumens.nomor_agenda LIKE '%\_%' THEN CAST(SUBSTRING_INDEX(LPAD(dokumens.nomor_agenda, 10, '0'), '_', 1) AS UNSIGNED)
                WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
                ELSE 0
            END {$sortOrder}")
                ->orderBy('dokumens.nomor_agenda', $sortOrder);
        } else {
            $allowedColumns = ['nomor_spp', 'tanggal_masuk', 'nilai_rupiah', 'tanggal_spp', 'uraian_spp', 'kategori', 'kebun', 'jenis_dokumen', 'jenis_sub_pekerjaan', 'jenis_pembayaran', 'nama_pengirim', 'dibayar_kepada', 'no_berita_acara', 'tanggal_berita_acara', 'no_spk', 'tanggal_spk', 'tanggal_berakhir_spk', 'status', 'nomor_miro', 'tanggal_miro'];
            if (in_array($sortColumn, $allowedColumns)) {
                $query->orderBy($sortColumn, $sortOrder);
            }
            $query->orderByRaw("CASE
                WHEN dokumens.nomor_agenda LIKE '%\_%' THEN CAST(SUBSTRING_INDEX(LPAD(dokumens.nomor_agenda, 10, '0'), '_', 1) AS UNSIGNED)
                WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC");
        }

        // Secondary sorting (butuh JOIN perpajakan_data di atas)
        $query->orderByDesc('perpajakan_data.received_at')
            ->orderByDesc('updated_at');

        return $query;
    }

    public function dokumens(Request $request)
    {
        $query = $this->buildPerpajakanQuery($request);

        // Sort/order sudah diterapkan & ditetapkan ke session di dalam
        // buildPerpajakanQuery() (baik dari request maupun sesi sebelumnya) —
        // baca ulang di sini agar $data (dipakai view) tetap punya nilai yang
        // sama persis dengan yang dipakai untuk sorting, tanpa menduplikasi logikanya.
        $sortColumn = session('perpajakan_sort_column', 'nomor_agenda');
        $sortOrder = session('perpajakan_sort_order', 'desc');

        $perPage = $request->get('per_page', 'all');
        $showAllRows = $perPage === 'all';
        if ($showAllRows) {
            $perPage = 100;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['perpajakan_per_page' => $showAllRows ? 'all' : $perPage]);

        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Eager load roleData and roleStatuses for perpajakan
        $dokumens->loadMissing([
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            },
            'roleStatuses' => function ($q) {
                // Muat status semua role agar pengecekan di view (akutansi/pembayaran) tidak memicu query per baris (N+1)
                $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']);
            }
        ]);

        // Add lock status to each document - use getCollection() to modify items while keeping Paginator
        $dokumens->getCollection()->transform(function ($dokumen) {
            // Ensure roleData is loaded for perpajakan - reload if not loaded or empty
            if (!$dokumen->relationLoaded('roleData') || $dokumen->roleData->isEmpty()) {
                $dokumen->load([
                    'roleData' => function ($q) {
                        $q->where('role_code', 'perpajakan');
                    }
                ]);
            }

            // Also ensure roleStatuses is loaded
            if (!$dokumen->relationLoaded('roleStatuses')) {
                $dokumen->load([
                    'roleStatuses' => function ($q) {
                        $q->where('role_code', 'perpajakan');
                    }
                ]);
            }

            $dokumen->is_locked = \App\Helpers\DokumenHelper::isDocumentLocked($dokumen);
            $dokumen->lock_status_message = \App\Helpers\DokumenHelper::getLockedStatusMessage($dokumen);
            $dokumen->can_edit = \App\Helpers\DokumenHelper::canEditDocument($dokumen, 'perpajakan');
            $dokumen->can_set_deadline = \App\Helpers\DokumenHelper::canSetDeadline($dokumen)['can_set'];
            $dokumen->lock_status_class = \App\Helpers\DokumenHelper::getLockStatusClass($dokumen);

            // Cross-role visibility: determine if document is at Perpajakan's role
            // Documents are "at my role" if:
            // - current_handler is perpajakan
            // - status indicates it was sent/processed by perpajakan (sent_to_akutansi, etc.)
            // - status indicates it was returned to perpajakan
            // - status is completed/selesai with status_pembayaran set (went through full workflow)
            $dokumen->is_at_my_role = in_array($dokumen->current_handler, ['perpajakan'])
                || in_array($dokumen->status, [
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'waiting_approval_akuntansi',
                    'waiting_approval_pembayaran',
                    'menunggu_di_approve',
                    'returned_to_verifikasi',
                ])
                || (in_array($dokumen->status, ['completed', 'selesai']) && !empty($dokumen->status_pembayaran))
                || ($dokumen->status === 'returned_to_department' && $dokumen->return_source === 'akutansi' && $dokumen->current_handler === 'perpajakan');

            return $dokumen;
        });

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'perpajakan');
        }

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        // Kolom tersedia = base terpusat tanpa 'status' (Perpajakan punya kolom
        // Status tetap). Sumber: config/document_columns.php.
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
                $preferences['perpajakan'] = $selectedColumns;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }
            // Also save to session for backward compatibility
            session(['perpajakan_dokumens_table_columns' => $selectedColumns]);
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

            if ($user && isset($user->table_columns_preferences['perpajakan'])) {
                $selectedColumns = $user->table_columns_preferences['perpajakan'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('perpajakan_dokumens_table_columns', $defaultColumns);
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
            session(['perpajakan_dokumens_table_columns' => $selectedColumns]);
        }

        // Virtual scroll: balas hanya potongan baris tabel (ringan) tanpa layout, stats, & partial berat
        if ($request->boolean('virtual_chunk')) {
            return view('perpajakan.dokumens._chunk', [
                'dokumens' => $dokumens,
                'selectedColumns' => $selectedColumns,
                'showActionColumn' => false,
            ]);
        }

        // Calculate 4 dashboard-style stats for the document list header
        // 1. Total Dokumen Agenda - semua dokumen dalam sistem (exclude CSV imports)
        $totalDokumenAgenda = Dokumen::excludeCsvImports()->count();

        // 2. Total Dokumen Perpajakan - dokumen yang terlihat oleh Perpajakan
        $totalDokumenPerpajakan = Dokumen::where(function ($query) {
            $query->where('current_handler', 'perpajakan')
                ->orWhereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran'])
                ->orWhere(function ($completedQ) {
                    $completedQ->whereIn('status', ['completed', 'selesai'])
                        ->whereNotNull('status_perpajakan');
                });
        })
            ->excludeCsvImports()
            ->count();

        // 3. Total Terkirim - dikirim ke tahap selanjutnya
        $totalTerkirim = Dokumen::whereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran', 'selesai'])
            ->where('current_handler', '!=', 'perpajakan')
            ->excludeCsvImports()
            ->count();

        // Calculate delay stats (same logic as dashboard index)
        $now = Carbon::now();
        $perpajakanDocsWithRoleData = Dokumen::where(function ($query) {
            $query->where('current_handler', 'perpajakan')
                ->orWhereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran'])
                ->orWhere(function ($completedQ) {
                    $completedQ->whereIn('status', ['completed', 'selesai'])
                        ->whereNotNull('status_perpajakan');
                });
        })
            ->excludeCsvImports()
            ->with(['roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            }])
            ->get(['id', 'status', 'current_handler', 'nilai_rupiah']);

        $dokumenLessThan24h = 0;
        $dokumen24to72h = 0;
        $dokumenMoreThan72h = 0;
        $totalNilaiRupiah = 0;

        foreach ($perpajakanDocsWithRoleData as $doc) {
            $totalNilaiRupiah += (float) preg_replace('/[^0-9]/', '', $doc->nilai_rupiah ?? 0);
            $roleData = $doc->roleData->first();
            if ($roleData && $roleData->received_at) {
                $receivedAt = Carbon::parse($roleData->received_at);
                $isSent = $doc->status === 'sent_to_akutansi' || $doc->current_handler !== 'perpajakan';
                $hoursDiff = ($isSent && $roleData->processed_at)
                    ? $receivedAt->diffInHours(Carbon::parse($roleData->processed_at))
                    : $receivedAt->diffInHours($now);
                if ($hoursDiff < 24) { $dokumenLessThan24h++; }
                elseif ($hoursDiff < 72) { $dokumen24to72h++; }
                else { $dokumenMoreThan72h++; }
            } else {
                $isBypassed = in_array($doc->status, ['sent_to_akutansi', 'sent_to_pembayaran', 'completed', 'selesai'])
                    || $doc->current_handler !== 'perpajakan';
                if ($isBypassed) { $dokumenLessThan24h++; }
                else { $dokumenMoreThan72h++; }
            }
        }

        // Filter by keterlambatan (deadline card click filter)
        if ($request->has('keterlambatan') && in_array($request->keterlambatan, ['aman', 'peringatan', 'terlambat'])) {
            $keterlambatanFilter = $request->keterlambatan;
            $filteredIds = [];
            foreach ($perpajakanDocsWithRoleData as $doc) {
                $roleData = $doc->roleData->first();
                if ($roleData && $roleData->received_at) {
                    $receivedAt = Carbon::parse($roleData->received_at);
                    $isSent = $doc->status === 'sent_to_akutansi' || $doc->current_handler !== 'perpajakan';
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
                if ($keterlambatanFilter === 'aman' && $isAman) $filteredIds[] = $doc->id;
                elseif ($keterlambatanFilter === 'peringatan' && $isPeringatan) $filteredIds[] = $doc->id;
                elseif ($keterlambatanFilter === 'terlambat' && $isTerlambat) $filteredIds[] = $doc->id;
            }
            $query->whereIn('dokumens.id', empty($filteredIds) ? [0] : $filteredIds);
        }

        // Load IE dropdown data
        $ieKategoriList = $ieSubKriteriaList = $ieItemSubKriteriaList = $ieJenisPembayaranList = [];
        try {
            $ieKategoriList = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get(['id_kategori_kriteria as id', 'nama_kriteria'])->toArray();
            $ieSubKriteriaList = \App\Models\SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])->toArray();
            $ieItemSubKriteriaList = \App\Models\ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])->toArray();
            $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])->toArray();
        } catch (\Exception $e) {
            \Log::error('IE dropdown load error (perpajakan): ' . $e->getMessage());
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
        $filterDariOptions = Dokumen::excludeCsvImports()
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->distinct()
            ->orderBy('bagian')
            ->pluck('bagian', 'bagian')
            ->toArray();

        $data = array(
            "title" => "Daftar Dokumen Team Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'totalDokumenAgenda' => $totalDokumenAgenda,
            'totalDokumenPerpajakan' => $totalDokumenPerpajakan,
            'totalTerkirim' => $totalTerkirim,
            'dokumenLessThan24h' => $dokumenLessThan24h,
            'dokumen24to72h' => $dokumen24to72h,
            'dokumenMoreThan72h' => $dokumenMoreThan72h,
            'totalNilaiRupiah' => $totalNilaiRupiah,
            'suggestions' => $suggestions,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
            'sortColumn' => $sortColumn,
            'sortOrder' => $sortOrder,
            'ieKategoriList' => $ieKategoriList,
            'ieSubKriteriaList' => $ieSubKriteriaList,
            'ieItemSubKriteriaList' => $ieItemSubKriteriaList,
            'ieJenisPembayaranList' => $ieJenisPembayaranList,
            'filterDariOptions' => $filterDariOptions,
        );
        return view('perpajakan.dokumens.daftarPerpajakan', $data);
    }




    /**
     * Set deadline for perpajakan
     */
    public function setDeadline(Request $request, Dokumen $dokumen)
    {

        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Validate - maksimal 3 hari untuk dokumen baru masuk
        $validator = Validator::make($request->all(), [
            'deadline_days' => 'required|integer|min:1|max:3',
            'deadline_note' => 'nullable|string|max:1000',
        ], [
            'deadline_days.max' => 'Deadline maksimal 3 hari untuk dokumen baru masuk.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Use helper for validation
            $validation = \App\Helpers\DokumenHelper::canSetDeadline($dokumen);
            if (!$validation['can_set']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                    'debug_info' => $validation['debug']
                ], 403);
            }

            $deadlineDays = (int) $request->deadline_days;
            // Calculate deadline using Asia/Jakarta timezone to match user's local time (WIB)
            // Important: Carbon will automatically convert to UTC when saving to database
            // When retrieved, we need to convert back to Asia/Jakarta for display
            $currentTime = \Carbon\Carbon::now('Asia/Jakarta');
            $deadlineAt = $currentTime->copy()->addDays($deadlineDays);

            // Ensure deadline_at is in UTC for database storage (Carbon will handle this automatically)
            $deadlineAtForDB = $deadlineAt->utc();

            Log::info('Deadline calculation for Perpajakan', [
                'document_id' => $dokumen->id,
                'current_time_wib' => $currentTime->format('Y-m-d H:i:s T'),
                'current_time_utc' => $currentTime->utc()->format('Y-m-d H:i:s T'),
                'deadline_days' => $deadlineDays,
                'deadline_at_wib' => $deadlineAt->format('Y-m-d H:i:s T'),
                'deadline_at_utc' => $deadlineAtForDB->format('Y-m-d H:i:s T'),
                'deadline_at_utc_to_wib' => $deadlineAtForDB->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s T'),
            ]);

            $deadlineNote = isset($request->deadline_note) && trim($request->deadline_note) !== ''
                ? trim($request->deadline_note)
                : null;

            // Update using transaction
            DB::transaction(function () use ($dokumen, $deadlineDays, $deadlineNote, $deadlineAtForDB) {
                // Update dokumen_role_data with deadline
                // Note: Carbon will automatically convert to UTC when saving to database
                $dokumen->setDataForRole('perpajakan', [
                    'deadline_at' => $deadlineAtForDB,
                    'deadline_days' => $deadlineDays,
                    'deadline_note' => $deadlineNote,
                    'received_at' => $dokumen->getDataForRole('perpajakan')?->received_at ?? now(),
                    'processed_at' => now(),
                ]);

                // Update dokumen status to 'sedang diproses' to unlock document
                $dokumen->update([
                    'status' => 'sedang diproses',
                ]);
            });

            // Refresh dokumen to get updated data
            $dokumen->refresh();
            // Reload roleData relationship to ensure getDataForRole() works correctly
            $dokumen->load([
                'roleData' => function ($q) {
                    $q->where('role_code', 'perpajakan');
                }
            ]);
            $updatedRoleData = $dokumen->getDataForRole('perpajakan');

            // Log activity: deadline diatur oleh Team Perpajakan
            try {
                \App\Helpers\ActivityLogHelper::logDeadlineSet(
                    $dokumen->fresh(),
                    'perpajakan',
                    [
                        'deadline_days' => $deadlineDays,
                        'deadline_at' => $updatedRoleData?->deadline_at?->format('Y-m-d H:i:s'),
                        'deadline_note' => $deadlineNote,
                    ]
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log deadline set: ' . $logException->getMessage());
            }

            \Log::info('Deadline successfully set for Perpajakan', [
                'document_id' => $dokumen->id,
                'deadline_days' => $deadlineDays,
                'deadline_at' => $updatedRoleData?->deadline_at
            ]);

            // Format deadline using Asia/Jakarta timezone for display
            // When retrieved from database, deadline_at is in UTC, so we need to convert to Asia/Jakarta
            $deadlineFormatted = null;
            if ($updatedRoleData && $updatedRoleData->deadline_at) {
                // Convert from UTC (database) to Asia/Jakarta (WIB) for display
                $deadlineWIB = $updatedRoleData->deadline_at->setTimezone('Asia/Jakarta');
                $deadlineFormatted = $deadlineWIB->format('d M Y, H:i');

                Log::info('Deadline formatted for display (Perpajakan)', [
                    'document_id' => $dokumen->id,
                    'deadline_at_db_utc' => $updatedRoleData->deadline_at->format('Y-m-d H:i:s T'),
                    'deadline_at_wib' => $deadlineWIB->format('Y-m-d H:i:s T'),
                    'deadline_formatted' => $deadlineFormatted,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Deadline berhasil ditetapkan ({$deadlineDays} hari). Dokumen sekarang terbuka untuk diproses.",
                'deadline' => $deadlineFormatted,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error setting deadline in Perpajakan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline'
            ], 500);
        }
    }

    /**
     * Get document detail for AJAX request
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow access if document was handled by perpajakan or returned from perpajakan
        $allowedHandlers = ['perpajakan', 'team_verifikasi', 'akutansi'];
        $allowedStatuses = ['sent_to_perpajakan', 'returned_to_department', 'returned_to_verifikasi', 'sent_to_akutansi'];

        if (!in_array($dokumen->current_handler, $allowedHandlers) && !in_array($dokumen->status, $allowedStatuses)) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load required relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // If request wants JSON (for modal view)
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'dokumen' => [
                    'id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'tanggal_spp' => $dokumen->tanggal_spp,
                    'bulan' => $dokumen->bulan,
                    'tahun' => $dokumen->tahun,
                    'tanggal_masuk' => $dokumen->tanggal_masuk,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan,
                    'kategori' => $dokumen->kategori,
                    'uraian_spp' => $dokumen->uraian_spp,
                    'nilai_rupiah' => $dokumen->nilai_rupiah,
                    'jenis_pembayaran' => $dokumen->jenis_pembayaran,
                    'dibayar_kepada' => $dokumen->dibayarKepadas->count() > 0
                        ? $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ')
                        : $dokumen->dibayar_kepada,
                    'kebun' => $dokumen->kebun,
                    'no_spk' => $dokumen->no_spk,
                    'tanggal_spk' => $dokumen->tanggal_spk,
                    'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk,
                    'nomor_miro' => $dokumen->nomor_miro,
                    'no_berita_acara' => $dokumen->no_berita_acara,
                    'tanggal_berita_acara' => $dokumen->tanggal_berita_acara,
                    'dokumen_pos' => $dokumen->dokumenPos->map(fn($po) => ['nomor_po' => $po->nomor_po]),
                    'dokumen_prs' => $dokumen->dokumenPrs->map(fn($pr) => ['nomor_pr' => $pr->nomor_pr]),
                    // Perpajakan fields
                    'komoditi_perpajakan' => $dokumen->komoditi_perpajakan,
                    'status_perpajakan' => $dokumen->status_perpajakan,
                    'npwp' => $dokumen->npwp,
                    'alamat_pembeli' => $dokumen->alamat_pembeli,
                    'no_kontrak' => $dokumen->no_kontrak,
                    'no_invoice' => $dokumen->no_invoice,
                    'tanggal_invoice' => $dokumen->tanggal_invoice,
                    'dpp_invoice' => $dokumen->dpp_invoice,
                    'ppn_invoice' => $dokumen->ppn_invoice,
                    'dpp_ppn_invoice' => $dokumen->dpp_ppn_invoice,
                    'tanggal_pengajuan_pajak' => $dokumen->tanggal_pengajuan_pajak,
                    'no_faktur' => $dokumen->no_faktur,
                    'tanggal_faktur' => $dokumen->tanggal_faktur,
                    'dpp_faktur' => $dokumen->dpp_faktur,
                    'ppn_faktur' => $dokumen->ppn_faktur,
                    'selisih_pajak' => $dokumen->selisih_pajak,
                    'keterangan_pajak' => $dokumen->keterangan_pajak,
                    'penggantian_pajak' => $dokumen->penggantian_pajak,
                    'dpp_penggantian' => $dokumen->dpp_penggantian,
                    'ppn_penggantian' => $dokumen->ppn_penggantian,
                    'selisih_ppn' => $dokumen->selisih_ppn,
                    'tanggal_selesai_verifikasi_pajak' => $dokumen->tanggal_selesai_verifikasi_pajak,
                    'jenis_pph' => $dokumen->jenis_pph,
                    'dpp_pph' => $dokumen->dpp_pph,
                    'ppn_terhutang' => $dokumen->ppn_terhutang,
                    'link_dokumen_pajak' => $dokumen->link_dokumen_pajak,
                    // Akutansi fields
                    'nomor_miro' => $dokumen->nomor_miro,
                    'tanggal_miro' => $dokumen->tanggal_miro,
                    // Fallback fields from CSV import
                    'NO_PO' => $dokumen->NO_PO ?? null,
                    'NO_MIRO_SES' => $dokumen->NO_MIRO_SES ?? null,
                ]
            ]);
        }

        // Return HTML partial for detail view (legacy)
        $html = $this->generateDocumentDetailHtml($dokumen);

        return response($html);
    }

    /**
     * Generate HTML for document detail
     */
    private function generateDocumentDetailHtml($dokumen)
    {
        $html = '<div class="detail-grid">';

        // Document Information Section
        $detailItems = [
            'Tanggal Masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
            'Bulan' => $dokumen->bulan,
            'Tahun' => $dokumen->tahun,
            'No SPP' => $dokumen->nomor_spp,
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah,
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

        // Visual Separator
        $html .= '<div class="detail-section-separator">
            <div class="separator-content">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Informasi Perpajakan</span>
                <span class="tax-badge">KHUSUS PERPAJAKAN</span>
            </div>
        </div>';

        // Tax Information Section - Always show all fields even when empty
        $html .= '<div class="detail-grid tax-section">';

        // Tax Fields - Show all fields regardless of whether they have data
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

        return sprintf(
            '<a href="%s" target="_blank" class="tax-document-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
            htmlspecialchars($link),
            htmlspecialchars($link)
        );
    }

    public function pengembalian(Request $request)
    {
        // Get all documents that have been returned to perpajakan
        // Includes: documents returned FROM perpajakan to verifikasi AND documents rejected BY akutansi
        $query = Dokumen::where(function ($q) {
            // Documents returned from perpajakan to verifikasi (new status)
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_verifikasi')
                    ->where('return_source', 'perpajakan');
            })
                // Legacy: documents with old returned_to_department status
                ->orWhere(function ($legacyQ) {
                    $legacyQ->where('status', 'returned_to_department')
                        ->where('return_source', 'perpajakan');
                })
                // Documents rejected by akutansi (via roleStatuses with rejected status)
                ->orWhere(function ($akutansiRejectQ) {
                    $akutansiRejectQ->where('current_handler', 'perpajakan')
                        ->whereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'akutansi')
                                ->where('status', 'rejected');
                        });
                });
        })
            ->with(['dokumenPos', 'dokumenPrs', 'roleStatuses'])
            ->orderByDesc('returned_at');

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nomor_agenda', 'like', "%{$searchTerm}%")
                    ->orWhere('nomor_spp', 'like', "%{$searchTerm}%")
                    ->orWhere('uraian_spp', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = $request->get('per_page', session('perpajakan_returned_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['perpajakan_returned_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Calculate statistics for returned documents
        // Include both: documents returned from perpajakan to verifikasi AND documents rejected by akutansi
        $baseQuery = Dokumen::where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_verifikasi')
                    ->where('return_source', 'perpajakan');
            })
                ->orWhere(function ($legacyQ) {
                    $legacyQ->where('status', 'returned_to_department')
                        ->where('return_source', 'perpajakan');
                })
                ->orWhere(function ($akutansiRejectQ) {
                    $akutansiRejectQ->where('current_handler', 'perpajakan')
                        ->whereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'akutansi')
                                ->where('status', 'rejected');
                        });
                });
        });

        // Total dokumen dikembalikan
        $totalReturned = (clone $baseQuery)->count();

        // Menunggu perbaikan: dokumen yang dikembalikan dan masih menunggu (belum diperbaiki)
        // Logika: status returned_to_verifikasi (masih menunggu) ATAU ditolak oleh akutansi
        $totalMenungguPerbaikan = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status', 'returned_to_verifikasi')
                    ->orWhere('current_handler', 'team_verifikasi')
                    ->orWhere(function ($akutansiQ) {
                        $akutansiQ->where('current_handler', 'perpajakan')
                            ->whereHas('roleStatuses', function ($statusQuery) {
                                $statusQuery->where('role_code', 'akutansi')
                                    ->where('status', 'rejected');
                            });
                    });
            })
            ->count();

        // Sudah diperbaiki: dokumen yang sudah diperbaiki dan dikirim kembali
        // Logika: status bukan returned_to_verifikasi lagi DAN tidak di team_verifikasi
        $totalSudahDiperbaiki = (clone $baseQuery)
            ->where('status', '!=', 'returned_to_verifikasi')
            ->where('current_handler', '!=', 'team_verifikasi')
            ->where('current_handler', '!=', 'perpajakan')
            ->count();

        $data = array(
            "title" => "Dokumen Kembali dari Perpajakan ke Team Verifikasi",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
            'dokumens' => $dokumens,
            'totalReturned' => $totalReturned,
            'totalMenungguPerbaikan' => $totalMenungguPerbaikan,
            'totalSudahDiperbaiki' => $totalSudahDiperbaiki,
        );
        return view('perpajakan.dokumens.pengembalianPerpajakan', $data);
    }

    /**
     * Return document to Team Verifikasi
     */
    public function returnDocument(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim kembali.'
            ], 403);
        }

        // Validate the return reason
        $validator = Validator::make($request->all(), [
            'return_reason' => 'required|string|min:10|max:500',
        ], [
            'return_reason.required' => 'Catatan penyerahan kembali harus diisi.',
            'return_reason.min' => 'Catatan penyerahan kembali minimal 10 karakter.',
            'return_reason.max' => 'Catatan penyerahan kembali maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            \DB::beginTransaction();

            // Log before update
            \Log::info('Returning document from perpajakan', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'return_reason_length' => strlen($request->return_reason)
            ]);

            // Update all fields in a single call to avoid multiple queries and potential issues
            // current_handler diubah ke 'team_verifikasi' agar dokumen muncul di daftar Team Verifikasi
            // Hand the document back to Team Verifikasi after Perpajakan processing
            $updateData = [
                'status' => 'returned_to_verifikasi',
                'current_handler' => 'team_verifikasi',
                // Unified return fields (Phase 3 complete)
                'return_source' => 'perpajakan',
                'return_reason' => $request->return_reason,
                'returned_at' => now(),
                // Reset tax status since document is being returned
                'status_perpajakan' => null,
                'tanggal_selesai_verifikasi_pajak' => null,
            ];

            $dokumen->update($updateData);

            $dokumen->setDisplayStatusForRole('perpajakan', 'terkirim_verifikasi');
            $dokumen->setDisplayStatusForRole('team_verifikasi', 'sedang_diproses');

            \DB::commit();

            // Log activity: dokumen dikirim kembali ke Team Verifikasi oleh Perpajakan
            try {
                \App\Helpers\ActivityLogHelper::log(
                    $dokumen,
                    'sent_back_to_verifikasi',
                    'Dokumen dikirim kembali ke Team Verifikasi',
                    'tax',
                    'perpajakan',
                    [
                        'to' => 'team_verifikasi',
                        'note' => $request->return_reason,
                    ]
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log activity for returnDocument (perpajakan): ' . $logException->getMessage());
            }

            \Log::info('Document successfully returned from perpajakan', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim kembali ke Ibu Yuni.'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error returning document from perpajakan', [
                'document_id' => $dokumen->id ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send document to akutansi via inbox system
     * @deprecated Use sendToNext instead
     */
    public function sendToAkutansi(Request $request, Dokumen $dokumen)
    {
        return $this->sendToNext($request, $dokumen);
    }

    /**
     * Send document to next handler (Akutansi or Pembayaran) via inbox
     */
    public function sendToNext(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim. Dokumen tidak sedang ditangani oleh perpajakan.'
            ], 403);
        }

        // Validate next handler
        $request->validate([
            'next_handler' => 'required|in:akutansi,pembayaran'
        ]);

        try {
            \DB::beginTransaction();

            // Map handler to inbox role format
            $inboxRoleMap = [
                'akutansi' => 'Akutansi',
                'pembayaran' => 'Pembayaran',
            ];

            $inboxRole = $inboxRoleMap[$request->next_handler] ?? $request->next_handler;

            // Simpan status original sebelum dikirim ke inbox
            $originalStatus = $dokumen->status;

            // IMPORTANT: Explicitly set processed_at for perpajakan role before sending to inbox
            // This freezes the deadline timer, preserving the time already spent in perpajakan
            // The issue was that sendToRoleInbox might not correctly detect the sender role
            $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
            if ($perpajakanRoleData && $perpajakanRoleData->received_at && !$perpajakanRoleData->processed_at) {
                $perpajakanRoleData->processed_at = now();
                $perpajakanRoleData->save();
                \Log::info('Set processed_at for perpajakan before sending to next role', [
                    'document_id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'received_at' => $perpajakanRoleData->received_at,
                    'processed_at' => $perpajakanRoleData->processed_at
                ]);
            }

            // Kirim ke inbox menggunakan sistem inbox yang sudah ada
            $dokumen->sendToInbox($inboxRole);

            // Set tanggal selesai verifikasi pajak (only for akutansi)
            if ($request->next_handler === 'akutansi') {
                $dokumen->tanggal_selesai_verifikasi_pajak = now();
                $dokumen->save();
            }

            \DB::commit();

            $handlerName = $request->next_handler === 'akutansi' ? 'Akutansi' : 'Pembayaran';

            // Log timeline tracking
            try {
                DocumentTracking::logAction(
                    $dokumen->id,
                    'sent_to_' . $request->next_handler,
                    'perpajakan',
                    ['nomor_agenda' => $dokumen->nomor_agenda, 'next_handler' => $request->next_handler]
                );
            } catch (\Exception $trackEx) {
                \Log::error('DocumentTracking logAction failed (sendToNext perpajakan): ' . $trackEx->getMessage());
            }

            // Log activity: dokumen dikirim dari Perpajakan ke handler berikutnya
            try {
                \App\Helpers\ActivityLogHelper::logSent($dokumen, $request->next_handler, 'perpajakan');
                \App\Helpers\ActivityLogHelper::logReceived($dokumen, $request->next_handler);
            } catch (\Exception $logException) {
                \Log::error('Failed to log activity for sendToNext (perpajakan): ' . $logException->getMessage());
            }

            \Log::info("Document #{$dokumen->id} sent to inbox {$handlerName} by Perpajakan");

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikirim ke inbox Team {$handlerName} dan menunggu persetujuan.",
                'next_handler' => $handlerName
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error sending document from perpajakan: ' . $e->getMessage());

            $handlerName = $request->next_handler === 'akutansi' ? 'Akutansi' : 'Pembayaran';
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan saat mengirim dokumen ke Team {$handlerName}."
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
    private function getSearchSuggestions($searchTerm, $year = null, $handler = 'perpajakan'): array
    {
        $suggestions = [];

        // Get all unique values from relevant fields
        $baseQuery = Dokumen::where(function ($q) use ($handler) {
            $q->where('current_handler', $handler)
                ->orWhere('status', 'sent_to_akutansi');
        });

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
        $dibayarKepadaValues = DibayarKepada::whereHas('dokumen', function ($q) use ($handler, $year) {
            $q->where(function ($subQ) use ($handler) {
                $subQ->where('current_handler', $handler)
                    ->orWhere('status', 'sent_to_akutansi');
            });
            if ($year) {
                $q->where('tahun', $year);
            }
        })
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





    private function exportToPDF($dokumens, $columns)
    {
        // Create a simple view for PDF
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'uraian_spp' => 'Uraian SPP',
            'dibayar_kepada' => 'Dibayar Kepada',
            'nilai_rupiah' => 'Nilai Rupiah',
            'tanggal_spp' => 'Tanggal SPP',
            'status' => 'Status',
            'deadline_at' => 'Deadline',
            'created_at' => 'Tanggal Masuk',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
        ];

        $data = [
            'dokumens' => $dokumens,
            'columns' => $columns,
            'availableColumns' => $availableColumns,
            'title' => 'Export Data Perpajakan',
            'date' => date('d/m/Y H:i')
        ];
        return view('perpajakan.export.pdf', $data);
    }
}
