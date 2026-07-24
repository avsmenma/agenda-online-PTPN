<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Helpers\DokumenHelper;
use App\Helpers\SearchHelper;
use App\Models\DibayarKepada;

class DashboardAkutansiController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsRoleDashboard;

    /**
     * Halaman dashboard Team Akutansi.
     */
    public function dashboard()
    {
        $data = $this->buildRoleDashboardData('akutansi');
        $data['title'] = 'Dashboard Team Akutansi';
        $data['module'] = 'akutansi';
        $data['menuDashboard'] = 'Active';

        return view('dashboard.workflow', $data);
    }


    /**
     * Endpoint JSON untuk tabel Tabulator akutansi. Membalas {last_page,total,data}
     * (nama field cocok progressiveLoad Tabulator). Memakai ulang query & eager-load
     * yang SAMA dengan dokumens() lewat buildAkutansiQuery(), lalu memetakan tiap
     * baris via AkutansiDocumentRow (badge Status & Deadline dihitung server).
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildAkutansiQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        $handlerOptions = $this->buildAkutansiHandlerOptions();
        $viewerRole = Auth::user()?->role;

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\AkutansiDocumentRow::fromDokumen($d, $handlerOptions, $viewerRole))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /**
     * Opsi pengurus dokumen (handler_options) SEKALI per-request: 5 peran base +
     * optgroup Bagian bila ada. Ditanam apa adanya oleh AkutansiDocumentRow.
     * Bentuk identik DokumenController::buildHandlerOptions() (sumber tunggal bentuk).
     */
    private function buildAkutansiHandlerOptions(): array
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
                'options'  => $bagian->map(fn ($b) => [
                    'value' => 'bagian_' . strtolower($b->kode),
                    'label' => $b->nama ?: $b->kode,
                ])->all(),
            ];
        }

        return $handlerOptions;
    }

    /**
     * Pembangun query daftar dokumen akutansi (cross-role visibility) —
     * SUMBER TUNGGAL dipakai dokumens() (view) & datatable() (JSON). Meliputi
     * base query, search, filter (dari/tanggal/nilai), switch status 5 bucket,
     * eager-load (roleData akutansi-only + roleStatuses semua role terkait +
     * dokumenPos/dokumenPrs/dibayarKepadas), dan sort natural nomor_agenda.
     *
     * PENTING: roleData sengaja di-load HANYA role_code='akutansi' (paritas
     * tampilan lama; AkutansiDocumentRow bergantung padanya). Jangan diperluas.
     */
    private function buildAkutansiQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
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
                Log::warning('Akutansi ignored invalid tanggal_masuk filter', [
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
                    // Dokumen yang sedang diproses oleh akutansi
                    $query->where('current_handler', 'akutansi')
                        ->whereNotIn('status', [
                            'sent_to_pembayaran',
                            'pending_approval_pembayaran',
                            'completed',
                            'selesai'
                        ])
                        // Exclude dokumen yang pending approval dari akutansi
                        ->whereDoesntHave('roleStatuses', function ($statusQ) {
                            $statusQ->where('role_code', 'akutansi')
                                ->where('status', DokumenStatus::STATUS_PENDING);
                        })
                        // Exclude dokumen yang ditolak oleh akutansi
                        ->whereDoesntHave('roleStatuses', function ($statusQ) {
                            $statusQ->where('role_code', 'akutansi')
                                ->where('status', DokumenStatus::STATUS_REJECTED);
                        });
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
                        $statusQ->whereIn('status', ['sent_to_pembayaran', 'completed', 'selesai']);
                    })
                        ->where('current_handler', '!=', 'akutansi');
                    // Only exclude CSV imports if column exists
                    if ($hasImportedFromCsvColumn) {
                        $query->where(function ($csvQ) {
                            $csvQ->where('imported_from_csv', false)
                                ->orWhereNull('imported_from_csv');
                        });
                    }
                    break;
                case 'ditolak':
                    // Dokumen yang ditolak (rejected di dokumen_statuses untuk role akutansi)
                    $query->whereHas('roleStatuses', function ($q) {
                        $q->where('role_code', 'akutansi')
                            ->where('status', DokumenStatus::STATUS_REJECTED);
                    });
                    break;
            }
        }

        // Eager load roleData and roleStatuses for akutansi to access deadline_at and status
        $query->with([
            'roleData' => function ($q) {
                $q->where('role_code', 'akutansi');
            },
            'roleStatuses' => function ($q) {
                // Muat status semua role agar pengecekan di view (pembayaran dll) tidak memicu query per baris (N+1)
                $q->whereIn('role_code', ['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran']);
            }
        ]);

        // === Sort/Order handling ===
        if ($request->has('sort') || $request->has('order')) {
            $sortColumn = $request->get('sort', 'nomor_agenda');
            $sortOrder = $request->get('order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
            session(['akutansi_sort_column' => $sortColumn, 'akutansi_sort_order' => $sortOrder]);
        } else {
            $sortColumn = session('akutansi_sort_column', 'nomor_agenda');
            $sortOrder = session('akutansi_sort_order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        }

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

        return $query;
    }

    public function dokumens(Request $request)
    {
        // Akutansi sees ALL documents (cross-role visibility)
        // Action buttons are disabled for documents not yet at this role (controlled in blade view)
        // Exclude documents that are returned to bidang and CSV imports
        $query = $this->buildAkutansiQuery($request);

        // Sort/order sudah diterapkan & ditetapkan ke session di dalam
        // buildAkutansiQuery() (baik dari request maupun sesi sebelumnya) — baca
        // ulang di sini agar $data (dipakai view) tetap punya nilai yang sama
        // persis dengan yang dipakai untuk sorting, tanpa menduplikasi logikanya.
        $sortColumn = session('akutansi_sort_column', 'nomor_agenda');
        $sortOrder = session('akutansi_sort_order', 'desc');

        $perPage = $request->get('per_page', 'all');
        $showAllRows = $perPage === 'all';
        if ($showAllRows) {
            $perPage = 100;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['akutansi_per_page' => $showAllRows ? 'all' : $perPage]);
        $dokumens = $query->orderBy('dokumens.id', 'DESC')
            ->paginate($perPage)
            ->appends($request->query());

        // Add lock status to each document - use getCollection() to modify items while keeping Paginator
        $dokumens->getCollection()->transform(function ($dokumen) {
            // Ensure roleData is loaded for akutansi - reload if not loaded or empty
            if (!$dokumen->relationLoaded('roleData') || $dokumen->roleData->isEmpty()) {
                $dokumen->load([
                    'roleData' => function ($q) {
                        $q->where('role_code', 'akutansi');
                    }
                ]);
            }

            // Also ensure roleStatuses is loaded
            if (!$dokumen->relationLoaded('roleStatuses')) {
                $dokumen->load([
                    'roleStatuses' => function ($q) {
                        $q->where('role_code', 'akutansi');
                    }
                ]);
            }

            $dokumen->is_locked = DokumenHelper::isDocumentLocked($dokumen);
            $dokumen->lock_status_message = DokumenHelper::getLockedStatusMessage($dokumen);
            $dokumen->can_edit = DokumenHelper::canEditDocument($dokumen, 'akutansi');
            $dokumen->can_set_deadline = DokumenHelper::canSetDeadline($dokumen)['can_set'];
            $dokumen->lock_status_class = DokumenHelper::getLockStatusClass($dokumen);

            // Cross-role visibility: determine if document is at Akutansi's role
            // Documents are "at my role" if:
            // - current_handler is akutansi
            // - status indicates it was sent/processed by akutansi (sent_to_pembayaran, etc.)
            // - status is completed/selesai with status_pembayaran set (went through full workflow)
            $dokumen->is_at_my_role = in_array($dokumen->current_handler, ['akutansi'])
                || in_array($dokumen->status, [
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran',
                    'waiting_approval_pembayaran',
                    'menunggu_di_approve',
                ])
                || (in_array($dokumen->status, ['completed', 'selesai']) && !empty($dokumen->status_pembayaran));

            return $dokumen;
        });

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'akutansi');
        }

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        // Kolom tersedia = base terpusat tanpa 'status' (Akutansi punya kolom
        // Status tetap). Sumber: config/document_columns.php.
        $availableColumns = \Illuminate\Support\Arr::except(config('document_columns.base'), ['status']);

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Filter out 'status' and 'nomor_mirror' from selectedColumns if present
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'status' && $col !== 'nomor_mirror';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array

        // If columns are provided in request, save to database and session
        if ($request->has('columns') && !empty($selectedColumns)) {
            // Save to database (permanent)
            $user = Auth::user();
            if ($user) {
                $preferences = $user->table_columns_preferences ?? [];
                $preferences['akutansi'] = $selectedColumns;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }
            // Also save to session for backward compatibility
            session(['akutansi_dokumens_table_columns' => $selectedColumns]);
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

            if ($user && isset($user->table_columns_preferences['akutansi'])) {
                $selectedColumns = $user->table_columns_preferences['akutansi'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('akutansi_dokumens_table_columns', $defaultColumns);
            }

            // Filter out 'status' and 'nomor_mirror' if they exist
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'status' && $col !== 'nomor_mirror';
            });
            $selectedColumns = array_values($selectedColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['akutansi_dokumens_table_columns' => $selectedColumns]);
        }

        // Calculate 4 dashboard-style stats + delay stats + total rupiah for bento grid
        // 1. Total Dokumen Agenda - semua dokumen dalam sistem (exclude CSV imports)
        $totalDokumenAgenda = Dokumen::excludeCsvImports()->count();

        // 2. Total Dokumen Akutansi - dokumen yang terlihat oleh Akutansi
        $totalDokumenAkutansi = Dokumen::where(function ($query) {
            $query->where('current_handler', 'akutansi')
                ->orWhere('status', 'sent_to_akutansi');
        })
            ->excludeCsvImports()
            ->count();

        // 3. Total Terkirim - dikirim ke pembayaran atau selesai
        $totalTerkirim = Dokumen::whereIn('status', ['sent_to_pembayaran', 'selesai'])
            ->where('current_handler', '!=', 'akutansi')
            ->excludeCsvImports()
            ->count();

        // 4. Total Nilai Rupiah - sum semua dokumen yang dikerjakan akutansi
        $totalNilaiRupiah = Dokumen::where(function ($query) {
            $query->where('current_handler', 'akutansi')
                ->orWhereIn('status', ['sent_to_pembayaran', 'selesai']);
        })
            ->excludeCsvImports()
            ->sum('nilai_rupiah');

        // 6. Delay stats - based on roleData received_at for keterlambatan cards
        $now = Carbon::now();
        $akutansiDocsForDelay = Dokumen::where(function ($query) {
            $query->where('current_handler', 'akutansi')
                ->orWhereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran']);
        })
            ->excludeCsvImports()
            ->with([
                'roleData' => function ($q) {
                    $q->where('role_code', 'akutansi');
                }
            ])
            ->get();

        $dokumenLessThan24h = 0;
        $dokumen24to72h = 0;
        $dokumenMoreThan72h = 0;

        foreach ($akutansiDocsForDelay as $doc) {
            $roleData = $doc->roleData->first();
            if ($roleData && $roleData->received_at) {
                $receivedAt = Carbon::parse($roleData->received_at);
                $isSent = in_array($doc->status, ['sent_to_pembayaran', 'selesai']) || $doc->current_handler !== 'akutansi';
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
                // Tanpa received_at (belum diterima) → NETRAL: tidak dihitung aman/peringatan/terlambat.
                // (dulu bypass dihitung <24 jam, sisanya >72 jam — menyesatkan)
            }
        }

        // 7. Apply keterlambatan filter if requested
        $filterKeterlambatan = $request->get('keterlambatan');
        if ($filterKeterlambatan) {
            $akutansiDocsFiltered = Dokumen::where(function ($query) {
                $query->where('current_handler', 'akutansi')
                    ->orWhereIn('status', ['sent_to_akutansi', 'sent_to_pembayaran']);
            })
                ->excludeCsvImports()
                ->with(['roleData' => function ($q) { $q->where('role_code', 'akutansi'); }])
                ->get();

            $filteredIds = $akutansiDocsFiltered->filter(function ($doc) use ($filterKeterlambatan, $now) {
                $roleData = $doc->roleData->first();
                if ($roleData && $roleData->received_at) {
                    $receivedAt = Carbon::parse($roleData->received_at);
                    $isSent = in_array($doc->status, ['sent_to_pembayaran', 'selesai']) || $doc->current_handler !== 'akutansi';
                    $hoursDiff = ($isSent && $roleData->processed_at)
                        ? $receivedAt->diffInHours(Carbon::parse($roleData->processed_at))
                        : $receivedAt->diffInHours($now);
                    if ($filterKeterlambatan === 'aman') return $hoursDiff < 24;
                    if ($filterKeterlambatan === 'peringatan') return $hoursDiff >= 24 && $hoursDiff < 72;
                    if ($filterKeterlambatan === 'terlambat') return $hoursDiff >= 72;
                }
                return false;
            })->pluck('id')->toArray();

            if (!empty($filteredIds)) {
                $dokumens = $dokumens->paginator ?? $dokumens;
            }
        }

        // Load IE dropdown data
        $ieKategoriList = $ieSubKriteriaList = $ieItemSubKriteriaList = $ieJenisPembayaranList = [];
        try {
            $ieKategoriList = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get(['id_kategori_kriteria as id', 'nama_kriteria'])->toArray();
            $ieSubKriteriaList = \App\Models\SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])->toArray();
            $ieItemSubKriteriaList = \App\Models\ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])->toArray();
            $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])->toArray();
        } catch (\Exception $e) {
            \Log::error('IE dropdown load error (akutansi): ' . $e->getMessage());
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
            "title" => "Daftar Dokumen Team Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'totalDokumenAgenda' => $totalDokumenAgenda,
            'totalDokumenAkutansi' => $totalDokumenAkutansi,
            'totalTerkirim' => $totalTerkirim,
            'totalNilaiRupiah' => $totalNilaiRupiah,
            'dokumenLessThan24h' => $dokumenLessThan24h,
            'dokumen24to72h' => $dokumen24to72h,
            'dokumenMoreThan72h' => $dokumenMoreThan72h,
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
        return view('akutansi.dokumens.daftarAkutansiTabulator', $data);
    }

    public function pengembalian(Request $request)
    {
        // Get all documents that have been returned to akutansi
        // Includes: documents returned from akutansi to verifikasi AND documents rejected by pembayaran
        $query = Dokumen::where(function ($q) {
            // Documents returned from akutansi to verifikasi (new status)
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_verifikasi')
                    ->where('return_source', 'akutansi');
            })
                // Legacy: documents with old returned_to_department status
                ->orWhere(function ($legacyQ) {
                    $legacyQ->where('status', 'returned_to_department')
                        ->where('return_source', 'akutansi');
                })
                // Documents rejected by pembayaran (from inbox)
                ->orWhere(function ($pembayaranRejectQ) {
                    $pembayaranRejectQ->where('current_handler', 'akutansi')
                        ->whereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'pembayaran')
                                ->where('status', 'rejected');
                        });
                });
        })
            ->with(['dokumenPos', 'dokumenPrs', 'roleStatuses'])
            ->orderByDesc('returned_at');

        $perPage = $request->get('per_page', session('akutansi_returned_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['akutansi_returned_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Calculate statistics for returned documents
        // Include both: documents returned from akutansi to verifikasi AND documents rejected by pembayaran
        $baseQuery = Dokumen::where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_verifikasi')
                    ->where('return_source', 'akutansi');
            })
                ->orWhere(function ($legacyQ) {
                    $legacyQ->where('status', 'returned_to_department')
                        ->where('return_source', 'akutansi');
                })
                ->orWhere(function ($pembayaranRejectQ) {
                    $pembayaranRejectQ->where('current_handler', 'akutansi')
                        ->whereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'pembayaran')
                                ->where('status', 'rejected');
                        });
                });
        });

        // Total dokumen dikembalikan
        $totalReturned = (clone $baseQuery)->count();

        // Menunggu perbaikan: dokumen yang dikembalikan dan masih menunggu (belum diperbaiki)
        // Logika: status returned_to_verifikasi (masih menunggu) ATAU ditolak oleh pembayaran
        $totalMenungguPerbaikan = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status', 'returned_to_verifikasi')
                    ->orWhere('current_handler', 'team_verifikasi')
                    ->orWhere(function ($pembayaranQ) {
                        $pembayaranQ->where('current_handler', 'akutansi')
                            ->whereHas('roleStatuses', function ($statusQuery) {
                                $statusQuery->where('role_code', 'pembayaran')
                                    ->where('status', 'rejected');
                            });
                    });
            })
            ->count();

        // Sudah diperbaiki: dokumen yang sudah diperbaiki dan dikirim kembali ke akutansi
        // Logika: status bukan returned_to_verifikasi lagi DAN sudah kembali ke akutansi tanpa reject pembayaran
        $totalSudahDiperbaiki = (clone $baseQuery)
            ->where('status', '!=', 'returned_to_verifikasi')
            ->where('current_handler', 'akutansi')
            ->whereDoesntHave('roleStatuses', function ($statusQuery) {
                $statusQuery->where('role_code', 'pembayaran')
                    ->where('status', 'rejected');
            })
            ->count();

        $data = array(
            "title" => "Dokumen Kembali dari Akuntansi ke Team Verifikasi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
            'dokumens' => $dokumens,
            'totalReturned' => $totalReturned,
            'totalMenungguPerbaikan' => $totalMenungguPerbaikan,
            'totalSudahDiperbaiki' => $totalSudahDiperbaiki,
        );
        return view('akutansi.dokumens.pengembalianAkutansi', $data);
    }



    /**
     * Get document detail for Akutansi view
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow access if document is handled by akutansi or sent to akutansi
        $allowedHandlers = ['akutansi', 'perpajakan', 'team_verifikasi'];
        $allowedStatuses = ['sent_to_akutansi', 'sedang diproses', 'selesai', 'sent_to_pembayaran', 'returned_to_verifikasi'];

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
     * Generate HTML for document detail with separated perpajakan data
     */
    private function generateDocumentDetailHtml($dokumen)
    {
        $html = '<div class="detail-grid">';

        // Document Information Section (Basic Data)
        $detailItems = [
            'Tanggal Masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
            'Bulan' => $dokumen->bulan,
            'Tahun' => $dokumen->tahun,
            'No SPP' => $dokumen->nomor_spp,
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah,
            'Kategori' => $dokumen->kategori ?? '-',
            'Jenis Dokumen' => $dokumen->jenis_dokumen ?? '-',
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
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

        if ($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi') {
            // Visual Separator for Perpajakan Data
            $html .= '<div class="detail-section-separator">
                <div class="separator-content">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Data Perpajakan</span>
                    <span class="tax-badge">DITAMBAHKAN OLEH PERPAJAKAN</span>
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

        // Data Akutansi Section - Always show for documents in akutansi
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
            'Tanggal MIRO' => $dokumen->tanggal_miro ? $dokumen->tanggal_miro->format('d/m/Y') : '<span class="empty-field">Belum diisi</span>',
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
     * Get search suggestions when no results found
     */
    private function getSearchSuggestions($searchTerm, $year = null, $handler = 'akutansi'): array
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
}
