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
    use \App\Http\Controllers\Concerns\ExportsDocuments;

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

        $bagianMap = \App\Support\HandlerOptions::bagianMap();
        $viewerRole = Auth::user()?->role;

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\AkutansiDocumentRow::fromDokumen(
                $d,
                \App\Support\HandlerOptions::forDokumen($d->bagian, $bagianMap),
                $viewerRole
            ))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /**
     * Export daftar dokumen akutansi (Excel/PDF) — Task 4 fitur export bersama,
     * lewat trait ExportsDocuments::respondDocumentExport() (App\Support\DocumentExporter).
     * Route: GET documents/akutansi/export (documents.akutansi.export), dipanggil
     * tombol Export toolbar Tabulator (CFG.exportUrl). Query & viewerRole SAMA dgn
     * datatable() — buildAkutansiQuery() + Auth::user()?->role — tanpa duplikasi
     * filter/scope, tanpa mengubah endpoint data yang ada.
     *
     * Kolom: katalog config('document_columns.base') di-intersect dgn columns[] dari
     * request (kolom terlihat di tabel saat export ditekan) — pertahanan thd field
     * asing/objek (mis. status_badge). Kosong → seluruh katalog.
     */
    public function exportDocuments(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $query = $this->buildAkutansiQuery($request);
        $bagianMap = \App\Support\HandlerOptions::bagianMap();
        $viewerRole = Auth::user()?->role;

        $rows = $query->get()
            ->map(fn (Dokumen $d) => \App\Support\AkutansiDocumentRow::fromDokumen(
                $d,
                \App\Support\HandlerOptions::forDokumen($d->bagian, $bagianMap),
                $viewerRole
            ))
            ->all();

        $catalog = config('document_columns.base');

        $requestedKeys = $request->get('columns', []);
        $requestedKeys = is_array($requestedKeys) ? array_map('strval', $requestedKeys) : [];
        $keys = array_values(array_intersect($requestedKeys, array_keys($catalog)));

        if (empty($keys)) {
            $keys = array_keys($catalog);
        }

        $columns = array_map(
            fn (string $key) => ['key' => $key, 'label' => $catalog[$key]],
            $keys
        );

        $options = [
            'title'     => 'Dokumen Akutansi',
            'total_key' => 'nilai_rupiah',
        ];

        return $this->respondDocumentExport($request, $rows, $columns, $options);
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

            // Preferensi tersimpan bisa memuat kunci kolom yang sudah DIHAPUS dari
            // katalog (mis. kolom yang dicabut lewat migrasi). Tanpa penyaringan ini
            // kunci basi tetap dirender sebagai kolom, dan judulnya jatuh ke kunci
            // MENTAH karena katalog tak lagi punya labelnya. Terjadi nyata pada
            // nomor_kontrak (2026-08-04): kolom sudah dihapus dari kode & database,
            // tapi tetap muncul di dua akun yang preferensinya masih menyimpannya.
            // Jalur ?columns[] sudah disaring sejak awal; jalur preferensi terlewat.
            $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($availableColumns)));

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['akutansi_dokumens_table_columns' => $selectedColumns]);
        }

        // === Konfigurasi kolom beku (frozen) ===
        // Dinormalkan ulang tiap request: kolom yang dibekukan bisa saja sudah
        // disembunyikan user lewat tab pertama modal.
        $pinnedColumns = ['nomor_agenda'];
        $frozenResolved = \App\Support\ColumnCustomization::resolveFrozen($request, Auth::user(), [
            'available'  => $availableColumns,
            'selected'   => $selectedColumns,
            'default'    => ['left' => $pinnedColumns, 'right' => []],
            'pinnedLeft' => $pinnedColumns,
            'prefKey'    => 'akutansi_frozen',
            'sessionKey' => 'akutansi_dokumens_frozen_columns',
        ]);
        $frozenColumns = ['left' => $frozenResolved['left'], 'right' => $frozenResolved['right']];
        // Urutan render tabel: beku kiri -> bebas -> beku kanan. $selectedColumns
        // sengaja TIDAK diubah agar tab pertama modal tetap menampilkan urutan
        // pilihan asli user.
        $renderColumns = $frozenResolved['render'];

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
            'frozenColumns' => $frozenColumns,
            'pinnedColumns' => $pinnedColumns,
            'renderColumns' => $renderColumns,
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
