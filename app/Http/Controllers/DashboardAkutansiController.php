<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Helpers\DokumenHelper;
use App\Helpers\SearchHelper;
use App\Models\DibayarKepada;
use App\Models\DocumentTracking;

class DashboardAkutansiController extends Controller
{
    public function index()
    {
        // Get all documents that have been assigned to akutansi at any point
        // Exclude CSV imported documents - they are meant only for pembayaran
        // Note: Removed 'sent_to_pembayaran' status because CSV imports use this status
        // and should not appear in Akutansi module. Only documents that went through
        // Akutansi workflow (sent_to_akutansi) should appear here.
        $akutansiDocs = Dokumen::where(function ($query) {
            $query->where('current_handler', 'akutansi')
                ->orWhere('status', 'sent_to_akutansi');
                // Removed: ->orWhere('status', 'sent_to_pembayaran')
                // Reason: CSV imported documents have this status and should be exclusive to Pembayaran
        })
            ->excludeCsvImports()
            ->get();

        // Calculate accurate statistics based on actual workflow using existing fields
        $totalDokumen = $akutansiDocs->count();

        $totalSelesai = $akutansiDocs
            ->where('status', 'selesai')
            ->count();

        $totalProses = $akutansiDocs
            ->where('status', 'sedang diproses')
            ->where('current_handler', 'akutansi')
            ->count();

        $totalBelumDiproses = $akutansiDocs
            ->where('status', 'sent_to_akutansi')
            ->where('current_handler', 'akutansi')
            ->count();

        $totalDikembalikan = $akutansiDocs
            ->where(function ($doc) {
                return in_array($doc->status, ['returned_to_ibua', 'returned_to_department', 'dikembalikan']);
            })
            ->count();

        // Total Dikirim: Documents that have been completed and are no longer handled by akutansi
        $totalDikirim = Dokumen::where('status', 'selesai')
            ->where(function ($query) {
                $query->where('current_handler', '!=', 'akutansi')
                    ->orWhereNull('current_handler');
            })
            ->where(function ($query) {
                $query->where('status', 'sent_to_akutansi')
                    ->orWhere('current_handler', 'akutansi');
            })
            ->count();

        // Get latest documents currently handled by akutansi
        // Exclude CSV imported documents - they are exclusive to Pembayaran
        $dokumenTerbaru = Dokumen::where('current_handler', 'akutansi')
            ->excludeCsvImports()
            ->with(['dokumenPos', 'dokumenPrs'])
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard Team Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'totalDokumen' => $totalDokumen,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalBelumDiproses' => $totalBelumDiproses,
            'totalDikembalikan' => $totalDikembalikan,
            'totalDikirim' => $totalDikirim,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('akutansi.dashboardAkutansi', $data);
    }

    /**
     * Check for new documents assigned to akutansi
     */
    public function checkUpdates(Request $request)
    {
        try {
            $lastChecked = $request->input('last_checked', 0);

            // Convert timestamp to Carbon instance for proper comparison
            // If lastChecked is 0 or very old, use current time as baseline
            // This ensures we only show notifications for documents sent AFTER the page loads
            $lastCheckedDate = $lastChecked > 0
                ? \Carbon\Carbon::createFromTimestamp($lastChecked)
                : \Carbon\Carbon::now(); // Use current time as baseline for first load

            // Cek semua dokumen akutansi yang baru dikirim menggunakan dokumen_role_data
            // Exclude documents imported from CSV to prevent notification spam
            $newDocuments = Dokumen::where(function ($query) use ($lastCheckedDate) {
                $query->where(function ($q) {
                    $q->where('current_handler', 'akutansi')
                        ->orWhere('status', 'sent_to_akutansi');
                })
                    ->where(function ($q) use ($lastCheckedDate) {
                        // Check if received_at in roleData is newer
                        $q->whereHas('roleData', function ($subQ) use ($lastCheckedDate) {
                            $subQ->where('role_code', 'akutansi')
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
                        $query->where('role_code', 'akutansi');
                    }
                ])
                ->latest('updated_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function ($query) {
                $query->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi');
            })->count();

            return response()->json([
                'has_updates' => $newDocuments->count() > 0,
                'new_count' => $newDocuments->count(),
                'total_documents' => $totalDocuments,
                'new_documents' => $newDocuments->map(function ($doc) {
                    // Use sent_to_akutansi_at if available, otherwise use updated_at
                    $roleData = $doc->getDataForRole('akutansi');
                    $sentAt = $roleData?->received_at
                        ? $roleData->received_at->format('d/m/Y H:i')
                        : $doc->updated_at->format('d/m/Y H:i');

                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => $doc->uraian_spp,
                        'nilai_rupiah' => $doc->nilai_rupiah,
                        'status' => $doc->status,
                        'sent_at' => $sentAt,
                        'sent_from' => 'Perpajakan',
                    ];
                }),
                'last_checked' => time()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in akutansi/check-updates: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => true,
                'message' => 'Failed to check updates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dokumens(Request $request)
    {
        // Akutansi sees:
        // 1. Documents currently handled by Akutansi (active)
        // 2. Documents that have been sent to Akutansi (tracking)
        // Exclude CSV imported documents - they are meant only for pembayaran
        // Note: Removed 'sent_to_pembayaran' and related statuses because CSV imports use these statuses
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');
        
        $query = Dokumen::where(function ($q) use ($hasImportedFromCsvColumn) {
            $q->where('current_handler', 'akutansi')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere(function ($pembayaranQ) use ($hasImportedFromCsvColumn) {
                    // Include documents sent to pembayaran or completed after payment, but exclude CSV imports
                    $pembayaranQ->where(function ($statusQ) {
                        $statusQ->where('status', 'sent_to_pembayaran')
                            ->orWhere(function ($completedQ) {
                                // Include completed documents that have status_pembayaran (indicating they went through pembayaran)
                                $completedQ->whereIn('status', ['completed', 'selesai'])
                                    ->whereNotNull('status_pembayaran');
                            });
                    });
                    // Only exclude CSV imports if column exists
                    if ($hasImportedFromCsvColumn) {
                        $pembayaranQ->where(function ($csvQ) {
                            $csvQ->where('imported_from_csv', false)
                                ->orWhereNull('imported_from_csv');
                        });
                    }
                });
        })
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

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'sedang_proses':
                    // Dokumen yang sedang diproses oleh akutansi
                    // Sesuai dengan logika di view yang menampilkan "Sedang Diproses" untuk dokumen yang:
                    // - current_handler = 'akutansi'
                    // - Bukan sent_to_pembayaran
                    // - Bukan pending approval (pending_approval_*, menunggu_di_approve)
                    // - Bukan rejected atau pending di roleStatuses
                    $query->where(function ($q) {
                        $q->where('current_handler', 'akutansi')
                            ->whereNotIn('status', ['sent_to_pembayaran', 'pending_approval_pembayaran', 'menunggu_di_approve'])
                            ->whereDoesntHave('roleStatuses', function ($statusQ) {
                                $statusQ->where('role_code', 'akutansi')
                                    ->whereIn('status', ['pending', 'rejected']);
                            });
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
                            'pending_approval_ibub',
                            'pending_approval_perpajakan',
                            'pending_approval_akutansi',
                            'pending_approval_pembayaran',
                            'waiting_reviewer_approval',
                            'menunggu_di_approve'
                        ]);
                    });
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
                $q->where('role_code', 'akutansi');
            }
        ]);

        // Order by nomor_agenda descending (numerically) first, then by deadline status, then by received_at
        // This ensures documents are sorted by nomor_agenda (2010, 2009, 2006, 2005, etc.)
        // Priority: 1 = locked (no deadline), 2 = has deadline or sent to pembayaran, 3 = others
        $dokumens = $query->orderByRaw("CASE 
                WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC")
            ->orderBy('dokumens.nomor_agenda', 'DESC') // Secondary sort for non-numeric or same numeric values
            ->orderByRaw("CASE
                -- Kategori 1: Dokumen yang locked (belum set deadline)
                WHEN dokumens.status = 'sent_to_akutansi' 
                AND (
                    SELECT deadline_at FROM dokumen_role_data 
                    WHERE dokumen_id = dokumens.id AND role_code = 'akutansi' 
                    LIMIT 1
                ) IS NULL THEN 1
                -- Kategori 2: Dokumen yang sudah punya deadline atau sudah terkirim ke pembayaran
                WHEN (
                    SELECT deadline_at FROM dokumen_role_data 
                    WHERE dokumen_id = dokumens.id AND role_code = 'akutansi' 
                    LIMIT 1
                ) IS NOT NULL 
                OR dokumens.status IN ('menunggu_di_approve', 'pending_approval_pembayaran', 'sent_to_pembayaran') THEN 2
                -- Kategori 3: Lainnya
                ELSE 3
            END")
            ->orderByRaw("COALESCE(
                (SELECT received_at FROM dokumen_role_data 
                 WHERE dokumen_id = dokumens.id AND role_code = 'akutansi' 
                 LIMIT 1),
                dokumens.created_at
            ) DESC")
            ->orderBy('dokumens.id', 'DESC') // Secondary sort by ID untuk konsistensi
            ->paginate(10);

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
            return $dokumen;
        });

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'akutansi');
        }

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nomor_miro' => 'Nomor Miro',
            'tanggal_spp' => 'Tanggal SPP',
            'uraian_spp' => 'Uraian SPP',
            'kategori' => 'Kategori',
            'kebun' => 'Kebun',
            'jenis_dokumen' => 'Jenis Dokumen',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'nama_pengirim' => 'Nama Pengirim',
            'dibayar_kepada' => 'Dibayar Kepada',
            'no_berita_acara' => 'No Berita Acara',
            'tanggal_berita_acara' => 'Tanggal Berita Acara',
            'no_spk' => 'No SPK',
            'tanggal_spk' => 'Tanggal SPK',
            'tanggal_berakhir_spk' => 'Tanggal Berakhir SPK',
            // Kolom Pajak
            'npwp' => 'NPWP',
            'no_faktur' => 'No Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tanggal Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPN Terhutang',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
        ];

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Filter out 'status', 'keterangan', and 'nomor_mirror' from selectedColumns if present
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'status' && $col !== 'keterangan' && $col !== 'nomor_mirror';
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
                'nomor_miro'
            ];

            if ($user && isset($user->table_columns_preferences['akutansi'])) {
                $selectedColumns = $user->table_columns_preferences['akutansi'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('akutansi_dokumens_table_columns', $defaultColumns);
            }

            // Filter out 'status', 'keterangan', and 'nomor_mirror' if they exist
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'status' && $col !== 'keterangan' && $col !== 'nomor_mirror';
            });
            $selectedColumns = array_values($selectedColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['akutansi_dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Team Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'suggestions' => $suggestions,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
        );
        return view('akutansi.dokumens.daftarAkutansi', $data);
    }

    public function createDokumen()
    {
        $data = array(
            "title" => "Tambah Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        return view('akutansi.dokumens.tambahAkutansi', $data);
    }

    public function storeDokumen(Request $request)
    {
        // Implementation for storing document
        return redirect()->route('documents.akutansi.index')->with('success', 'Akutansi berhasil ditambahkan');
    }

    public function editDokumen($id)
    {
        // Find the document
        $dokumen = Dokumen::findOrFail($id);

        // Validate that user can edit this document
        if (!DokumenHelper::canEditDocument($dokumen, 'akutansi')) {
            return redirect()->route('documents.akutansi.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        // Load document relationships if needed
        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        // Check if document has been to perpajakan
        $perpajakanData = $dokumen->getDataForRole('perpajakan');
        $hasPerpajakanData = ($perpajakanData && $perpajakanData->received_at) ||
            !empty($dokumen->processed_perpajakan_at) ||
            !empty($dokumen->no_faktur) ||
            !empty($dokumen->npwp);

        // Ambil data jenis pembayaran dari database cash_bank_new
        $jenisPembayaranList = collect([]);
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            \Log::info('Jenis Pembayaran fetched (akutansi): ' . $jenisPembayaranList->count() . ' records');
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran data (akutansi): ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            // Fallback: gunakan collection kosong jika error
            $jenisPembayaranList = collect([]);
        }

        // Ambil data untuk dropdown kriteria (jika database cash_bank tersedia)
        $isDropdownAvailable = false;
        $kategoriKriteria = collect([]);
        $subKriteria = collect([]);
        $itemSubKriteria = collect([]);
        $selectedKriteriaCfId = null;
        $selectedSubKriteriaId = null;
        $selectedItemSubKriteriaId = null;

        try {
            $kategoriKriteria = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = \App\Models\SubKriteria::all();
            $itemSubKriteria = \App\Models\ItemSubKriteria::all();
            $isDropdownAvailable = $kategoriKriteria->count() > 0;

            // Get selected values if document has kategori/jenis_dokumen/jenis_sub_pekerjaan
            if ($dokumen->kategori) {
                $selectedKategori = \App\Models\KategoriKriteria::where('nama_kriteria', $dokumen->kategori)->first();
                if ($selectedKategori) {
                    $selectedKriteriaCfId = $selectedKategori->id_kategori_kriteria;
                }
            }
            if ($dokumen->jenis_dokumen) {
                $selectedSub = \App\Models\SubKriteria::where('nama_sub_kriteria', $dokumen->jenis_dokumen)->first();
                if ($selectedSub) {
                    $selectedSubKriteriaId = $selectedSub->id_sub_kriteria;
                }
            }
            if ($dokumen->jenis_sub_pekerjaan) {
                $selectedItem = \App\Models\ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan)->first();
                if ($selectedItem) {
                    $selectedItemSubKriteriaId = $selectedItem->id_item_sub_kriteria;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching cash_bank data (edit akutansi): ' . $e->getMessage());
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
            $isDropdownAvailable = false;
        }

        $data = array(
            "title" => "Edit Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumen' => $dokumen,
            'hasPerpajakanData' => $hasPerpajakanData, // Flag untuk menampilkan section perpajakan
            'jenisPembayaranList' => $jenisPembayaranList,
            'kategoriKriteria' => $kategoriKriteria ?? collect([]),
            'subKriteria' => $subKriteria ?? collect([]),
            'itemSubKriteria' => $itemSubKriteria ?? collect([]),
            'isDropdownAvailable' => $isDropdownAvailable,
            'selectedKriteriaCfId' => $selectedKriteriaCfId,
            'selectedSubKriteriaId' => $selectedSubKriteriaId,
            'selectedItemSubKriteriaId' => $selectedItemSubKriteriaId,
        );
        return view('akutansi.dokumens.editAkutansi', $data);
    }

    public function updateDokumen(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        try {
            // Find the document
            $dokumen = Dokumen::findOrFail($id);

            // Validate that user can edit this document
            if (!DokumenHelper::canEditDocument($dokumen, 'akutansi')) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.')
                    ->withInput();
            }

            // Enhanced logging
            Log::info('=== UPDATE DOKUMEN AKUTANSI REQUEST ===', [
                'document_id' => $dokumen->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role,
                'request_data' => $request->except(['_token', '_method'])
            ]);

            // Merge request data with existing document data to ensure all required fields are present
            // Use existing document values as defaults for required fields if not provided
            if (empty($request->nomor_spp)) {
                $request->merge(['nomor_spp' => $dokumen->nomor_spp ?? '']);
            }
            if (empty($request->nomor_agenda)) {
                $request->merge(['nomor_agenda' => $dokumen->nomor_agenda ?? '']);
            }
            if (empty($request->uraian_spp)) {
                $request->merge(['uraian_spp' => $dokumen->uraian_spp ?? '']);
            }
            // Jangan merge nilai_rupiah jika empty, biarkan menggunakan nilai dari dokumen yang sudah ada
            // Format nilai rupiah akan ditangani di bawah

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text
            $nilaiRupiah = null;
            if ($request->filled('nilai_rupiah')) {
                $nilaiRupiahRaw = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
                if (!empty($nilaiRupiahRaw) && $nilaiRupiahRaw > 0) {
                    $nilaiRupiah = (float) $nilaiRupiahRaw;
                }
            }
            // Jika nilai rupiah tidak diisi atau kosong, gunakan nilai dari dokumen yang sudah ada
            if ($nilaiRupiah === null || $nilaiRupiah <= 0) {
                $nilaiRupiah = $dokumen->nilai_rupiah ?? 0;
            }

            // Validate request data
            $validated = $request->validate([
                // MIRO Fields - khusus Akutansi
                'nomor_miro' => 'nullable|string|max:255',
                'tanggal_miro' => 'nullable|date',

                // Basic document fields
                'nomor_agenda' => 'nullable|string|max:255',
                'nomor_spp' => 'nullable|string|max:255',
                'uraian_spp' => 'nullable|string|max:1000',
                'nilai_rupiah' => 'nullable|string', // Changed to string to accept formatted input
                'tanggal_masuk' => 'nullable|date',
                'tanggal_spp' => 'nullable|date',
                'bulan' => 'nullable|string',
                'tahun' => 'nullable|integer|min:2020|max:2030',
                'dibayar_kepada' => 'nullable|string',
                'no_berita_acara' => 'nullable|string',
                'tanggal_berita_acara' => 'nullable|date',
                'no_spk' => 'nullable|string',
                'tanggal_spk' => 'nullable|date',
                'tanggal_berakhir_spk' => 'nullable|date',
                'bagian' => 'nullable|string',
                'nama_pengirim' => 'nullable|string',
                'kebun' => 'nullable|string|max:255',
                'jenis_pembayaran' => 'nullable|string|max:255',
                'kategori' => 'nullable|string|max:255',
                'jenis_dokumen' => 'nullable|string|max:255',
                'jenis_sub_pekerjaan' => 'nullable|string|max:255',
                'kriteria_cf' => 'nullable|integer',
                'sub_kriteria' => 'nullable|integer',
                'item_sub_kriteria' => 'nullable|integer',
                'nomor_po' => 'array',
                'nomor_po.*' => 'nullable|string',
                'nomor_pr' => 'array',
                'nomor_pr.*' => 'nullable|string',

                // Tax fields (read-only, tidak akan diupdate)
                'status_perpajakan' => 'nullable|string|max:255',
                'no_faktur' => 'nullable|string|max:255',
                'tanggal_faktur' => 'nullable|date',
                'tanggal_selesai_verifikasi_pajak' => 'nullable|date',
                'jenis_pph' => 'nullable|string|max:50',
                'dpp_pph' => 'nullable|numeric|min:0',
                'ppn_terhutang' => 'nullable|numeric|min:0',
            ], [
                'nomor_miro.max' => 'Nomor MIRO maksimal 255 karakter.',
                'tahun.integer' => 'Tahun harus berupa angka.',
                'tahun.min' => 'Tahun minimal 2020.',
                'tahun.max' => 'Tahun maksimal 2030.',
            ]);

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;
            
            try {
                if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                    $kategoriKriteria = \App\Models\KategoriKriteria::find($request->kriteria_cf);
                }
                
                if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                    $subKriteria = \App\Models\SubKriteria::find($request->sub_kriteria);
                }
                
                if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                    $itemSubKriteria = \App\Models\ItemSubKriteria::find($request->item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cash_bank data for update (Akutansi): ' . $e->getMessage());
            }

            // Prepare update data
            $updateData = [
                // MIRO fields
                'nomor_miro' => $validated['nomor_miro'] ?? $dokumen->nomor_miro,
                'tanggal_miro' => $validated['tanggal_miro'] ?? $dokumen->tanggal_miro,

                // Basic fields
                'nomor_agenda' => $validated['nomor_agenda'] ?? $dokumen->nomor_agenda,
                'nomor_spp' => $validated['nomor_spp'] ?? $dokumen->nomor_spp,
                'uraian_spp' => $validated['uraian_spp'] ?? $dokumen->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah, // Use formatted value
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? $dokumen->tanggal_masuk,
                'tanggal_spp' => $validated['tanggal_spp'] ?? $dokumen->tanggal_spp,
                'bulan' => $validated['bulan'] ?? $dokumen->bulan,
                'tahun' => $validated['tahun'] ?? $dokumen->tahun,
                'dibayar_kepada' => $validated['dibayar_kepada'] ?? $dokumen->dibayar_kepada,
                'no_berita_acara' => $validated['no_berita_acara'] ?? $dokumen->no_berita_acara,
                'tanggal_berita_acara' => $validated['tanggal_berita_acara'] ?? $dokumen->tanggal_berita_acara,
                'no_spk' => $validated['no_spk'] ?? $dokumen->no_spk,
                'tanggal_spk' => $validated['tanggal_spk'] ?? $dokumen->tanggal_spk,
                'tanggal_berakhir_spk' => $validated['tanggal_berakhir_spk'] ?? $dokumen->tanggal_berakhir_spk,
                'bagian' => $validated['bagian'] ?? $dokumen->bagian,
                'nama_pengirim' => $validated['nama_pengirim'] ?? $dokumen->nama_pengirim,
                'kebun' => $validated['kebun'] ?? $dokumen->kebun,
                'jenis_pembayaran' => $validated['jenis_pembayaran'] ?? $dokumen->jenis_pembayaran,
                // Simpan nama dari ID untuk backward compatibility
                'kategori' => $kategoriKriteria ? $kategoriKriteria->nama_kriteria : ($validated['kategori'] ?? $dokumen->kategori),
                'jenis_dokumen' => $subKriteria ? $subKriteria->nama_sub_kriteria : ($validated['jenis_dokumen'] ?? $dokumen->jenis_dokumen),
                'jenis_sub_pekerjaan' => $itemSubKriteria ? $itemSubKriteria->nama_item_sub_kriteria : ($validated['jenis_sub_pekerjaan'] ?? $dokumen->jenis_sub_pekerjaan),
            ];

            // Store old value for logging
            $oldNomorMiro = $dokumen->nomor_miro;

            // Update document using transaction
            DB::transaction(function () use ($dokumen, $updateData, $request) {
                $dokumen->update($updateData);

                // Update PO numbers - delete existing and create new
                $dokumen->dokumenPos()->delete();
                if ($request->has('nomor_po')) {
                    foreach ($request->nomor_po as $nomorPO) {
                        if (!empty($nomorPO)) {
                            \App\Models\DokumenPO::create([
                                'dokumen_id' => $dokumen->id,
                                'nomor_po' => $nomorPO,
                            ]);
                        }
                    }
                }

                // Update PR numbers - delete existing and create new
                $dokumen->dokumenPrs()->delete();
                if ($request->has('nomor_pr')) {
                    foreach ($request->nomor_pr as $nomorPR) {
                        if (!empty($nomorPR)) {
                            \App\Models\DokumenPR::create([
                                'dokumen_id' => $dokumen->id,
                                'nomor_pr' => $nomorPR,
                            ]);
                        }
                    }
                }
            });

            $dokumen->refresh();

            // Log changes for akutansi-specific fields
            if ($oldNomorMiro != $dokumen->nomor_miro) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'nomor_miro',
                        $oldNomorMiro,
                        $dokumen->nomor_miro,
                        'akutansi'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log data edit for nomor_miro: ' . $logException->getMessage());
                }
            }

            Log::info('Dokumen Akutansi updated successfully', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_miro' => $dokumen->nomor_miro,
                'updated_by' => Auth::user()?->name
            ]);

            return redirect()->route('documents.akutansi.index')
                ->with('success', 'Dokumen Akutansi berhasil diperbarui!' .
                    ($updateData['nomor_miro'] ? ' Nomor MIRO: ' . $updateData['nomor_miro'] : ''));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error updating Akutansi document: ' . json_encode($e->errors()));

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Terdapat kesalahan pada input data.');

        } catch (\Exception $e) {
            Log::error('Error updating Akutansi document: ' . $e->getMessage(), [
                'document_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function destroyDokumen($id)
    {
        // Implementation for deleting document
        return redirect()->route('documents.akutansi.index')->with('success', 'Akutansi berhasil dihapus');
    }

    /**
     * Set deadline for Akutansi to unlock document processing
     */
    public function setDeadline(Request $request, Dokumen $dokumen): JsonResponse
    {
        try {
            // Enhanced logging with user context
            $roleData = $dokumen->getDataForRole('akutansi');
            Log::info('=== SET DEADLINE AKUTANSI REQUEST START ===', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'deadline_exists' => $roleData && $roleData->deadline_at ? true : false,
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
                'deadline_days' => 'required|integer|min:1|max:3',
                'deadline_note' => 'nullable|string|max:500',
            ], [
                'deadline_days.required' => 'Periode deadline wajib dipilih.',
                'deadline_days.integer' => 'Periode deadline harus berupa angka.',
                'deadline_days.min' => 'Deadline minimal 1 hari.',
                'deadline_days.max' => 'Deadline maksimal 3 hari.',
                'deadline_note.max' => 'Catatan maksimal 500 karakter.',
            ]);

            $deadlineDays = (int) $validated['deadline_days'];
            $deadlineNote = isset($validated['deadline_note']) && trim($validated['deadline_note']) !== ''
                ? trim($validated['deadline_note'])
                : null;

            // Calculate deadline using Asia/Jakarta timezone to match user's local time (WIB)
            // Important: Carbon will automatically convert to UTC when saving to database
            // When retrieved, we need to convert back to Asia/Jakarta for display
            $currentTime = \Carbon\Carbon::now('Asia/Jakarta');
            $deadlineAt = $currentTime->copy()->addDays($deadlineDays);

            // Ensure deadline_at is in UTC for database storage (Carbon will handle this automatically)
            // But we need to make sure the timezone is set correctly before saving
            $deadlineAtForDB = $deadlineAt->utc();

            Log::info('Deadline calculation for Akutansi', [
                'document_id' => $dokumen->id,
                'current_time_wib' => $currentTime->format('Y-m-d H:i:s T'),
                'current_time_utc' => $currentTime->utc()->format('Y-m-d H:i:s T'),
                'deadline_days' => $deadlineDays,
                'deadline_at_wib' => $deadlineAt->format('Y-m-d H:i:s T'),
                'deadline_at_utc' => $deadlineAtForDB->format('Y-m-d H:i:s T'),
                'deadline_at_utc_to_wib' => $deadlineAtForDB->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s T'),
            ]);

            DB::transaction(function () use ($dokumen, $deadlineDays, $deadlineNote, $deadlineAtForDB) {
                // Update dokumen_role_data with deadline
                // Note: Carbon will automatically convert to UTC when saving to database
                $dokumen->setDataForRole('akutansi', [
                    'deadline_at' => $deadlineAtForDB,
                    'deadline_days' => $deadlineDays,
                    'deadline_note' => $deadlineNote,
                    'received_at' => $dokumen->getDataForRole('akutansi')?->received_at ?? now(),
                    'processed_at' => now(),
                ]);

                // Update dokumen status
                $dokumen->update([
                    'status' => 'sedang diproses',
                ]);
            });

            // Refresh dokumen to get updated data and reload relationships
            $dokumen->refresh();
            // Reload roleData relationship to ensure getDataForRole() works correctly
            $dokumen->load([
                'roleData' => function ($q) {
                    $q->where('role_code', 'akutansi');
                }
            ]);
            $updatedRoleData = $dokumen->getDataForRole('akutansi');

            // Log activity: deadline diatur oleh Team Akutansi
            try {
                \App\Helpers\ActivityLogHelper::logDeadlineSet(
                    $dokumen->fresh(),
                    'akutansi',
                    [
                        'deadline_days' => $deadlineDays,
                        'deadline_at' => $updatedRoleData?->deadline_at?->format('Y-m-d H:i:s'),
                        'deadline_note' => $deadlineNote,
                    ]
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log deadline set: ' . $logException->getMessage());
            }

            \Log::info('Deadline successfully set for Akutansi', [
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

                Log::info('Deadline formatted for display', [
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error setting Akutansi deadline: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database query error setting Akutansi deadline: ' . $e->getMessage());
            \Log::error('SQL: ' . $e->getSql());
            \Log::error('Bindings: ' . json_encode($e->getBindings()));
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database saat menetapkan deadline. Pastikan semua kolom yang diperlukan ada di database.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error setting Akutansi deadline: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pengembalian(Request $request)
    {
        // Get all documents that have been returned to akutansi
        // Includes: documents returned from akutansi to verifikasi AND documents rejected by pembayaran
        $query = Dokumen::where(function ($q) {
                // Documents returned from akutansi to verifikasi
                $q->where(function ($subQ) {
                    $subQ->where('status', 'returned_to_department')
                        ->where('target_department', 'akutansi');
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
            ->orderByDesc('department_returned_at');

        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Calculate statistics for returned documents
        // Include both: documents returned from akutansi to verifikasi AND documents rejected by pembayaran
        $baseQuery = Dokumen::where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_department')
                    ->where('target_department', 'akutansi');
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

        // Menunggu perbaikan: dokumen yang dikembalikan dan masih di verifikasi (belum diperbaiki)
        // Logika: masih di ibuB (belum dikirim kembali ke akutansi) ATAU ditolak oleh pembayaran dan masih di akutansi
        $totalMenungguPerbaikan = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('current_handler', 'ibuB')
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
        // Logika: sudah kembali ke akutansi (current_handler == 'akutansi') DAN bukan ditolak oleh pembayaran
        $totalSudahDiperbaiki = (clone $baseQuery)
            ->where('current_handler', 'akutansi')
            ->whereDoesntHave('roleStatuses', function ($statusQuery) {
                $statusQuery->where('role_code', 'pembayaran')
                    ->where('status', 'rejected');
            })
            ->count();

        $data = array(
            "title" => "Daftar Pengembalian Dokumen Akutansi ke team verifikasi",
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

    public function rekapan(Request $request)
    {
        // Get selected year and bagian from request
        $selectedYear = $request->get('year', date('Y'));
        $selectedBagian = $request->get('bagian', '');
        $selectedMonth = $request->get('month', null);

        // Validate year
        if (!is_numeric($selectedYear) || $selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = date('Y');
        }

        // Base query - only documents that have reached Akutansi
        // Same filter logic as dokumens() method
        $baseQuery = Dokumen::query()
            ->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'sent_to_pembayaran'); // Tetap tampilkan dokumen yang sudah dikirim ke pembayaran
            })
            ->whereYear('tanggal_masuk', $selectedYear);

        // Filter by bagian if selected
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

        if ($selectedBagian && in_array($selectedBagian, array_keys($bagianList))) {
            $baseQuery->where('bagian', $selectedBagian);
        }

        // Get yearly summary
        $yearlySummary = [
            'total_dokumen' => (clone $baseQuery)->count(),
            'total_nominal' => (clone $baseQuery)->sum('nilai_rupiah') ?? 0,
        ];

        // Get monthly statistics
        $monthlyStats = [];
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

        for ($month = 1; $month <= 12; $month++) {
            $monthQuery = (clone $baseQuery)->whereMonth('tanggal_masuk', $month);
            $monthStats = [
                'name' => $monthNames[$month],
                'count' => $monthQuery->count(),
                'total_nominal' => $monthQuery->sum('nilai_rupiah') ?? 0,
            ];
            $monthlyStats[$month] = $monthStats;
        }

        // Get documents for table (filter by month if selected)
        $tableQuery = (clone $baseQuery);
        if ($selectedMonth && $selectedMonth >= 1 && $selectedMonth <= 12) {
            $tableQuery->whereMonth('tanggal_masuk', $selectedMonth);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $tableDokumens = $tableQuery->latest('tanggal_masuk')->paginate($perPage)->appends($request->query());

        // Get available years (only for documents that reached akutansi)
        $availableYears = Dokumen::query()
            ->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'sent_to_pembayaran');
            })
            ->whereNotNull('tanggal_masuk')
            ->selectRaw('DISTINCT YEAR(tanggal_masuk) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        // Get document count per bagian for the selected year (only documents that reached akutansi)
        $bagianCounts = [];
        foreach ($bagianList as $bagianCode => $bagianName) {
            $countQuery = Dokumen::query()
                ->where(function ($q) {
                    $q->where('current_handler', 'akutansi')
                        ->orWhere('status', 'sent_to_akutansi')
                        ->orWhere('status', 'sent_to_pembayaran');
                })
                ->whereYear('tanggal_masuk', $selectedYear)
                ->where('bagian', $bagianCode);
            $bagianCounts[$bagianCode] = $countQuery->count();
        }

        $data = [
            'title' => 'Analitik Dokumen',
            'module' => 'akutansi',
            'menuDokumen' => 'active',
            'menuRekapan' => 'active',
            'selectedYear' => (int) $selectedYear,
            'selectedBagian' => $selectedBagian,
            'selectedMonth' => $selectedMonth ? (int) $selectedMonth : null,
            'yearlySummary' => $yearlySummary,
            'monthlyStats' => $monthlyStats,
            'dokumens' => $tableDokumens,
            'availableYears' => $availableYears,
            'bagianList' => $bagianList,
            'bagianCounts' => $bagianCounts,
        ];

        return view('akutansi.analytics', $data);
    }


    /**
     * Get document detail for Akutansi view
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow access if document is handled by akutansi or sent to akutansi
        $allowedHandlers = ['akutansi', 'perpajakan', 'ibuB'];
        $allowedStatuses = ['sent_to_akutansi', 'sedang diproses', 'selesai', 'sent_to_pembayaran'];

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
     * Send document to Pembayaran
     */
    public function sendToPembayaran(Dokumen $dokumen)
    {
        try {
            // Validate that document is currently handled by Akutansi
            if ($dokumen->current_handler !== 'akutansi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak valid untuk dikirim. Dokumen tidak sedang ditangani oleh Akutansi.'
                ], 403);
            }

            // Removed validation: Allow sending document in any condition
            // Previously checked: status must be 'sedang diproses' and nomor_miro must be filled

            // Store previous status for tracking
            $previousStatus = $dokumen->status;

            // Send document to Pembayaran inbox (this will create pending status in dokumen_statuses)
            // This method will:
            // 1. Create pending status in dokumen_statuses for pembayaran
            // 2. Set received_at in dokumen_role_data for pembayaran
            // 3. Update status to 'menunggu_di_approve'
            // Note: current_handler tetap 'akutansi' agar dokumen tetap muncul di halaman Akutansi
            $dokumen->sendToInbox('Pembayaran');

            // Also explicitly call sendToRoleInbox to ensure status is created
            $dokumen->sendToRoleInbox('pembayaran', 'akutansi');

            // Pastikan current_handler tetap 'akutansi' agar dokumen tetap muncul di halaman Akutansi
            // current_handler akan berubah menjadi 'pembayaran' setelah dokumen di-approve di inbox pembayaran
            if ($dokumen->current_handler !== 'akutansi') {
                $dokumen->current_handler = 'akutansi';
                $dokumen->save();
            }

            // Set processed_at in dokumen_role_data for akutansi
            $roleData = $dokumen->getDataForRole('akutansi');
            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->save();
            } else {
                $dokumen->setDataForRole('akutansi', [
                    'processed_at' => now(),
                    'received_at' => now(),
                ]);
            }

            // Refresh dokumen setelah update
            $dokumen->refresh();

            // Log tracking action untuk pembayaran: marked ready for payment
            try {
                DocumentTracking::logAction(
                    $dokumen->id,
                    'sent_to_pembayaran', // Action type
                    'akutansi', // Actor (yang melakukan action)
                    [
                        'previous_status' => $previousStatus,
                        'marked_ready_at' => now()->toDateTimeString(),
                        'nomor_miro' => $dokumen->nomor_miro
                    ]
                );
            } catch (\Exception $trackingException) {
                \Log::error('Failed to log tracking action: ' . $trackingException->getMessage());
            }

            // Log activity: dokumen dikirim ke pembayaran oleh Team Akutansi
            try {
                \App\Helpers\ActivityLogHelper::logSent(
                    $dokumen,
                    'pembayaran',
                    'akutansi'
                );

                // Log activity: dokumen masuk/diterima di stage pembayaran
                \App\Helpers\ActivityLogHelper::logReceived(
                    $dokumen,
                    'pembayaran'
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log document sent: ' . $logException->getMessage());
            }

            // Log the activity
            \Log::info('Document sent from Akutansi to Pembayaran', [
                'document_id' => $dokumen->id,
                'nomor_spp' => $dokumen->nomor_spp,
                'sent_by' => 'akutansi',
                'sent_to' => 'pembayaran',
                'sent_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim ke Pembayaran untuk diproses pembayaran.',
                'redirect_url' => route('documents.akutansi.index')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending document from Akutansi to Pembayaran: ' . $e->getMessage(), [
                'document_id' => $dokumen->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen ke Pembayaran. Silakan coba lagi.'
            ], 500);
        }
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

    /**
     * Return document to IbuB
     */
    public function returnDocument(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is akutansi
        if ($dokumen->current_handler !== 'akutansi') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikembalikan.'
            ], 403);
        }

        // Validate the return reason
        $validator = Validator::make($request->all(), [
            'return_reason' => 'required|string|min:10|max:500',
        ], [
            'return_reason.required' => 'Alasan pengembalian harus diisi.',
            'return_reason.min' => 'Alasan pengembalian minimal 10 karakter.',
            'return_reason.max' => 'Alasan pengembalian maksimal 500 karakter.',
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
            \Log::info('Returning document from akutansi', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'return_reason_length' => strlen($request->return_reason)
            ]);

            // Update all fields in a single call to avoid multiple queries and potential issues
            $updateData = [
                'status' => 'returned_to_department',
                'current_handler' => 'ibuB',
                'target_department' => 'akutansi',
                'department_returned_at' => now(),
                'department_return_reason' => $request->return_reason,
                'alasan_pengembalian' => $request->return_reason,
                // Reset akutansi status since document is being returned
                'nomor_miro' => null,
            ];

            // Clear deadline from dokumen_role_data for akutansi
            $akutansiRoleData = $dokumen->getDataForRole('akutansi');
            if ($akutansiRoleData) {
                $akutansiRoleData->deadline_at = null;
                $akutansiRoleData->deadline_days = null;
                $akutansiRoleData->deadline_note = null;
                $akutansiRoleData->save();
            }

            // Only set sent_to_ibub_at if it's null (first time entering IbuB)
            // This preserves the original entry time for consistent ordering
            if (is_null($dokumen->sent_to_ibub_at)) {
                $updateData['sent_to_ibub_at'] = now();
            }

            $dokumen->update($updateData);

            \DB::commit();

            \Log::info('Document successfully returned from akutansi', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikembalikan ke Team Verifikasi.'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error returning document from akutansi', [
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
}

