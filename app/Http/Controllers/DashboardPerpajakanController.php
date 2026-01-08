<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use App\Models\DokumenStatus;
use App\Helpers\SearchHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardPerpajakanController extends Controller
{
    public function index()
    {
        // Get all documents that perpajakan can see (same as dokumens() query)
        // Exclude CSV imported documents - they should only appear in pembayaran
        // Note: Removed 'sent_to_pembayaran' status because CSV imports use this status
        // and should not appear in Perpajakan module
        $perpajakanDocs = Dokumen::query()
            ->where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    // Include documents rejected by akutansi and returned to perpajakan
                    ->orWhere(function ($rejectedQ) {
                        $rejectedQ->where('status', 'returned_to_department')
                            ->where('target_department', 'akutansi')
                            ->where('current_handler', 'perpajakan')
                            ->whereHas('roleStatuses', function ($statusQ) {
                                $statusQ->where('role_code', 'akutansi')
                                    ->where('status', 'rejected');
                            });
                    });
                // Removed: ->orWhere('status', 'sent_to_pembayaran')
                // Reason: CSV imported documents have this status and should be exclusive to Pembayaran
            })
            ->excludeCsvImports()
            ->get();

        // Calculate accurate statistics based on actual workflow
        $totalDokumen = $perpajakanDocs->count();

        $totalSelesai = $perpajakanDocs
            ->where('status_perpajakan', 'selesai')
            ->count();

        $totalDiproses = $perpajakanDocs
            ->where('status_perpajakan', 'sedang_diproses')
            ->count();

        $totalBelumDiproses = $perpajakanDocs
            ->where(function ($doc) {
                return empty($doc->status_perpajakan) || $doc->status_perpajakan === '';
            })
            ->count();

        $totalDikembalikan = $perpajakanDocs
            ->where('status', 'returned_to_perpajakan')
            ->count();

        // Total Dikirim: Documents that have been completed by perpajakan and sent to next stage
        // Since there's no "kirim" button yet, this should be documents that:
        // 1. Have status_perpajakan = 'selesai' AND
        // 2. Are no longer handled by perpajakan (moved to next stage like akutansi)
        $totalDikirim = Dokumen::where('status_perpajakan', 'selesai')
            ->where('current_handler', '!=', 'perpajakan')
            ->whereNotNull('current_handler')
            ->count();

        // Get latest documents for perpajakan - same logic as dokumens() method
        // Exclude CSV imported documents
        $dokumenTerbaru = Dokumen::query()
            ->where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_akutansi');
                // Removed: ->orWhere('status', 'sent_to_pembayaran')
                // Reason: CSV imported documents have this status and should be exclusive to Pembayaran
            })
            ->excludeCsvImports()
            ->with(['dokumenPos', 'dokumenPrs'])
            ->leftJoin('dokumen_role_data as perpajakan_data', function ($join) {
                $join->on('dokumens.id', '=', 'perpajakan_data.dokumen_id')
                    ->where('perpajakan_data.role_code', '=', 'perpajakan');
            })
            ->select('dokumens.*')
            ->orderByRaw("CASE
                WHEN current_handler = 'perpajakan' AND status != 'sent_to_akutansi' THEN 1
                WHEN status = 'sent_to_akutansi' THEN 2
                ELSE 3
            END")
            ->orderByDesc('perpajakan_data.received_at')
            ->orderByDesc('dokumens.updated_at')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard Team Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'totalDokumen' => $totalDokumen,
            'totalSelesai' => $totalSelesai,
            'totalDiproses' => $totalDiproses,
            'totalBelumDiproses' => $totalBelumDiproses,
            'totalDikembalikan' => $totalDikembalikan,
            'totalDikirim' => $totalDikirim,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('perpajakan.dashboardPerpajakan', $data);
    }

    public function dokumens(Request $request)
    {
        // Perpajakan sees:
        // 1. Documents with current_handler = perpajakan (active documents)
        // 2. Documents that were sent to akutansi (for tracking)
        // Exclude CSV imported documents - they are meant only for pembayaran
        // Note: Removed 'sent_to_pembayaran' status because CSV imports use this status
        $hasImportedFromCsvColumn = \Schema::hasColumn('dokumens', 'imported_from_csv');

        $query = Dokumen::query()
            ->where(function ($q) use ($hasImportedFromCsvColumn) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    // Include documents rejected by akutansi and returned to perpajakan
                    ->orWhere(function ($rejectedQ) {
                        $rejectedQ->where('status', 'returned_to_department')
                            ->where('target_department', 'akutansi')
                            ->where('current_handler', 'perpajakan')
                            ->whereHas('roleStatuses', function ($statusQ) {
                                $statusQ->where('role_code', 'akutansi')
                                    ->where('status', 'rejected');
                            });
                    })
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
                    // Dokumen yang sedang diproses oleh perpajakan
                    // Sesuai dengan logika di view yang menampilkan "Sedang Diproses" untuk dokumen yang:
                    // - current_handler = 'perpajakan'
                    // - Bukan sent_to_akutansi, sent_to_pembayaran
                    // - Bukan pending approval (pending_approval_*, menunggu_di_approve)
                    // - Bukan rejected atau pending di roleStatuses
                    // Note: Dokumen yang locked akan ditampilkan sebagai "Terkunci" di view, bukan "Sedang Diproses"
                    $query->where(function ($q) {
                        $q->where('current_handler', 'perpajakan')
                            ->whereNotIn('status', ['sent_to_akutansi', 'sent_to_pembayaran', 'pending_approval_akutansi', 'pending_approval_pembayaran', 'menunggu_di_approve'])
                            ->whereDoesntHave('roleStatuses', function ($statusQ) {
                                $statusQ->where('role_code', 'perpajakan')
                                    ->whereIn('status', ['pending', 'rejected']);
                            });
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

        $perPage = $request->get('per_page', 10);
        $dokumens = $query
            ->leftJoin('dokumen_role_data as perpajakan_data', function ($join) {
                $join->on('dokumens.id', '=', 'perpajakan_data.dokumen_id')
                    ->where('perpajakan_data.role_code', '=', 'perpajakan');
            })
            ->select('dokumens.*')
            ->orderByRaw("CASE 
                WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC")
            ->orderBy('dokumens.nomor_agenda', 'DESC') // Secondary sort for non-numeric or same numeric values
            ->orderByRaw("CASE
                WHEN current_handler = 'perpajakan' AND status NOT IN ('sent_to_akutansi', 'sent_to_pembayaran') THEN 1
                WHEN status = 'sent_to_akutansi' THEN 2
                WHEN status = 'sent_to_pembayaran' THEN 3
                ELSE 4
            END")
            ->orderByDesc('perpajakan_data.received_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage)->appends($request->query());

        // Eager load roleData and roleStatuses for perpajakan
        $dokumens->loadMissing([
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            },
            'roleStatuses' => function ($q) {
                $q->where('role_code', 'perpajakan');
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
            return $dokumen;
        });

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'perpajakan');
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
            'kategori' => 'Kriteria CF',
            'kebun' => 'Kebun',
            'jenis_dokumen' => 'Sub Kriteria',
            'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
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

        // Filter out 'status' and 'keterangan' from selectedColumns if present
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'status' && $col !== 'keterangan';
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
                'nomor_miro'
            ];

            if ($user && isset($user->table_columns_preferences['perpajakan'])) {
                $selectedColumns = $user->table_columns_preferences['perpajakan'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('perpajakan_dokumens_table_columns', $defaultColumns);
            }

            // Filter out 'status' and 'keterangan' if they exist
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'status' && $col !== 'keterangan';
            });
            $selectedColumns = array_values($selectedColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['perpajakan_dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Dokumen Team Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'suggestions' => $suggestions,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
        );
        return view('perpajakan.dokumens.daftarPerpajakan', $data);
    }

    public function editDokumen(Dokumen $dokumen)
    {
        // Only allow editing if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return redirect()->route('documents.perpajakan.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        // Load relationships including dibayarKepadas
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Ambil data dari database cash_bank_new untuk dropdown baru
        // Tambahkan try-catch untuk menangani error koneksi database
        $isDropdownAvailable = false;
        try {
            $kategoriKriteria = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = \App\Models\SubKriteria::all();
            $itemSubKriteria = \App\Models\ItemSubKriteria::all();
            $isDropdownAvailable = $kategoriKriteria->count() > 0;
        } catch (\Exception $e) {
            \Log::error('Error fetching cash_bank data: ' . $e->getMessage());
            // Fallback: gunakan collection kosong jika error
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
            $isDropdownAvailable = false;
        }

        // Ambil data jenis pembayaran dari database cash_bank_new
        $jenisPembayaranList = collect([]);
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            \Log::info('Jenis Pembayaran fetched (perpajakan): ' . $jenisPembayaranList->count() . ' records');
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran data (perpajakan): ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            // Fallback: gunakan collection kosong jika error
            $jenisPembayaranList = collect([]);
        }

        // Cari ID dari nama yang tersimpan di database (untuk backward compatibility)
        $selectedKriteriaCfId = null;
        $selectedSubKriteriaId = null;
        $selectedItemSubKriteriaId = null;

        try {
            if ($dokumen->kategori) {
                $foundKategori = \App\Models\KategoriKriteria::where('nama_kriteria', $dokumen->kategori)->first();
                if ($foundKategori) {
                    $selectedKriteriaCfId = $foundKategori->id_kategori_kriteria;
                }
            }

            if ($dokumen->jenis_dokumen) {
                $foundSub = \App\Models\SubKriteria::where('nama_sub_kriteria', $dokumen->jenis_dokumen)->first();
                if ($foundSub) {
                    $selectedSubKriteriaId = $foundSub->id_sub_kriteria;
                }
            }

            if ($dokumen->jenis_sub_pekerjaan) {
                $foundItem = \App\Models\ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan)->first();
                if ($foundItem) {
                    $selectedItemSubKriteriaId = $foundItem->id_item_sub_kriteria;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error finding IDs from names: ' . $e->getMessage());
            // Continue dengan null values jika error
        }

        $data = array(
            "title" => "Edit Dokumen",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumen' => $dokumen,
            'kategoriKriteria' => $kategoriKriteria ?? collect([]),
            'subKriteria' => $subKriteria ?? collect([]),
            'itemSubKriteria' => $itemSubKriteria ?? collect([]),
            'selectedKriteriaCfId' => $selectedKriteriaCfId ?? null,
            'selectedSubKriteriaId' => $selectedSubKriteriaId ?? null,
            'selectedItemSubKriteriaId' => $selectedItemSubKriteriaId ?? null,
            'isDropdownAvailable' => $isDropdownAvailable,
            'jenisPembayaranList' => $jenisPembayaranList,
        );
        return view('perpajakan.dokumens.editPerpajakan', $data);
    }

    public function updateDokumen(Request $request, Dokumen $dokumen)
    {
        $id = $dokumen->id;

        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return redirect()->route('documents.perpajakan.index')
                ->with('error', 'Dokumen ini tidak dapat diakses.');
        }

        // Check if using dropdown mode (cash_bank available) or manual mode
        $isDropdownMode = $request->filled('kriteria_cf') && $request->filled('sub_kriteria') && $request->filled('item_sub_kriteria');
        $isManualMode = $request->filled('kategori') && $request->filled('jenis_dokumen') && $request->filled('jenis_sub_pekerjaan');

        $rules = [
            'nomor_agenda' => 'nullable|string|unique:dokumens,nomor_agenda,' . $id,
            'bulan' => 'nullable|string',
            'tahun' => 'nullable|integer|min:2020|max:2030',
            'tanggal_masuk' => 'nullable|date',
            'nomor_spp' => 'nullable|string',
            'tanggal_spp' => 'nullable|date',
            'uraian_spp' => 'nullable|string',
            'nilai_rupiah' => 'nullable|string',
            'jenis_pembayaran' => 'nullable|string',
            'dibayar_kepada' => 'nullable|string',
            'no_berita_acara' => 'nullable|string',
            'tanggal_berita_acara' => 'nullable|date',
            'no_spk' => 'nullable|string',
            'tanggal_spk' => 'nullable|date',
            'tanggal_berakhir_spk' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'kebun' => 'nullable|string',
            'nomor_po' => 'array',
            'nomor_po.*' => 'nullable|string',
            'nomor_pr' => 'array',
            'nomor_pr.*' => 'nullable|string',
            // Perpajakan fields
            'npwp' => 'nullable|string',
            'no_faktur' => 'nullable|string',
            'tanggal_faktur' => 'nullable|date',
            'tanggal_selesai_verifikasi_pajak' => 'nullable|date',
            'jenis_pph' => 'nullable|string',
            'dpp_pph' => 'nullable|string',
            'ppn_terhutang' => 'nullable|string',
            'link_dokumen_pajak' => 'nullable|string',
            // Perpajakan Extended Fields
            'komoditi_perpajakan' => 'nullable|string',
            'alamat_pembeli' => 'nullable|string',
            'no_kontrak' => 'nullable|string',
            'no_invoice' => 'nullable|string',
            'tanggal_invoice' => 'nullable|date',
            'dpp_invoice' => 'nullable|string',
            'ppn_invoice' => 'nullable|string',
            'dpp_ppn_invoice' => 'nullable|string',
            'tanggal_pengajuan_pajak' => 'nullable|date',
            'dpp_faktur' => 'nullable|string',
            'ppn_faktur' => 'nullable|string',
            'selisih_pajak' => 'nullable|string',
            'keterangan_pajak' => 'nullable|string',
            'penggantian_pajak' => 'nullable|string',
            'dpp_penggantian' => 'nullable|string',
            'ppn_penggantian' => 'nullable|string',
            'selisih_ppn' => 'nullable|string',
        ];

        // Semua field optional (tidak wajib)
        $rules['kriteria_cf'] = 'nullable|integer';
        $rules['sub_kriteria'] = 'nullable|integer';
        $rules['item_sub_kriteria'] = 'nullable|integer';
        $rules['kategori'] = 'nullable|string|max:255';
        $rules['jenis_dokumen'] = 'nullable|string|max:255';
        $rules['jenis_sub_pekerjaan'] = 'nullable|string|max:255';

        $validator = Validator::make($request->all(), $rules, [
            'nomor_agenda.unique' => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2030.',
            'kriteria_cf.required' => 'Kriteria CF wajib dipilih.',
            'sub_kriteria.required' => 'Sub Kriteria wajib dipilih.',
            'item_sub_kriteria.required' => 'Item Sub Kriteria wajib dipilih.',
            'kategori.required' => 'Kategori wajib diisi.',
            'jenis_dokumen.required' => 'Jenis Dokumen wajib diisi.',
            'jenis_sub_pekerjaan.required' => 'Jenis Sub Pekerjaan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada input data. Silakan periksa kembali.');
        }

        try {
            \DB::beginTransaction();

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text
            $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
            if (empty($nilaiRupiah) || $nilaiRupiah <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nilai rupiah harus lebih dari 0.');
            }
            $nilaiRupiah = (float) $nilaiRupiah;

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
                \Log::error('Error fetching cash_bank data for update (DashboardPerpajakan): ' . $e->getMessage());
                // Continue dengan null values, akan menggunakan fallback ke request->kategori/jenis_dokumen/jenis_sub_pekerjaan
            }

            // Format dpp_pph (remove formatting dots)
            $dppPph = null;
            if (!empty($request->dpp_pph)) {
                $dppPph = preg_replace('/[^0-9]/', '', $request->dpp_pph);
                $dppPph = !empty($dppPph) ? (float) $dppPph : null;
            }

            // Format ppn_terhutang (remove formatting dots)
            $ppnTerhutang = null;
            if (!empty($request->ppn_terhutang)) {
                $ppnTerhutang = preg_replace('/[^0-9]/', '', $request->ppn_terhutang);
                $ppnTerhutang = !empty($ppnTerhutang) ? (float) $ppnTerhutang : null;
            }

            // Format new perpajakan extended fields (remove formatting dots)
            $dppInvoice = null;
            if (!empty($request->dpp_invoice)) {
                $dppInvoice = preg_replace('/[^0-9]/', '', $request->dpp_invoice);
                $dppInvoice = !empty($dppInvoice) ? (float) $dppInvoice : null;
            }

            $ppnInvoice = null;
            if (!empty($request->ppn_invoice)) {
                $ppnInvoice = preg_replace('/[^0-9]/', '', $request->ppn_invoice);
                $ppnInvoice = !empty($ppnInvoice) ? (float) $ppnInvoice : null;
            }

            $dppPpnInvoice = null;
            if (!empty($request->dpp_ppn_invoice)) {
                $dppPpnInvoice = preg_replace('/[^0-9]/', '', $request->dpp_ppn_invoice);
                $dppPpnInvoice = !empty($dppPpnInvoice) ? (float) $dppPpnInvoice : null;
            }

            $dppFaktur = null;
            if (!empty($request->dpp_faktur)) {
                $dppFaktur = preg_replace('/[^0-9]/', '', $request->dpp_faktur);
                $dppFaktur = !empty($dppFaktur) ? (float) $dppFaktur : null;
            }

            $ppnFaktur = null;
            if (!empty($request->ppn_faktur)) {
                $ppnFaktur = preg_replace('/[^0-9]/', '', $request->ppn_faktur);
                $ppnFaktur = !empty($ppnFaktur) ? (float) $ppnFaktur : null;
            }

            $selisihPajak = null;
            if (!empty($request->selisih_pajak)) {
                $selisihPajak = preg_replace('/[^0-9]/', '', $request->selisih_pajak);
                $selisihPajak = !empty($selisihPajak) ? (float) $selisihPajak : null;
            }

            $penggantianPajak = null;
            if (!empty($request->penggantian_pajak)) {
                $penggantianPajak = preg_replace('/[^0-9]/', '', $request->penggantian_pajak);
                $penggantianPajak = !empty($penggantianPajak) ? (float) $penggantianPajak : null;
            }

            $dppPenggantian = null;
            if (!empty($request->dpp_penggantian)) {
                $dppPenggantian = preg_replace('/[^0-9]/', '', $request->dpp_penggantian);
                $dppPenggantian = !empty($dppPenggantian) ? (float) $dppPenggantian : null;
            }

            $ppnPenggantian = null;
            if (!empty($request->ppn_penggantian)) {
                $ppnPenggantian = preg_replace('/[^0-9]/', '', $request->ppn_penggantian);
                $ppnPenggantian = !empty($ppnPenggantian) ? (float) $ppnPenggantian : null;
            }

            $selisihPpn = null;
            if (!empty($request->selisih_ppn)) {
                $selisihPpn = preg_replace('/[^0-9]/', '', $request->selisih_ppn);
                $selisihPpn = !empty($selisihPpn) ? (float) $selisihPpn : null;
            }

            // Update dokumen
            // Simpan nama dari ID untuk backward compatibility
            $updateData = [
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'tanggal_masuk' => $request->tanggal_masuk,
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                // Simpan nama dari ID untuk backward compatibility
                'kategori' => $kategoriKriteria ? $kategoriKriteria->nama_kriteria : ($request->kategori ?? $dokumen->kategori),
                'jenis_dokumen' => $subKriteria ? $subKriteria->nama_sub_kriteria : ($request->jenis_dokumen ?? $dokumen->jenis_dokumen),
                'jenis_sub_pekerjaan' => $itemSubKriteria ? $itemSubKriteria->nama_item_sub_kriteria : ($request->jenis_sub_pekerjaan ?? $dokumen->jenis_sub_pekerjaan),
                'jenis_pembayaran' => $request->jenis_pembayaran,
                'kebun' => $request->kebun,
                'dibayar_kepada' => $request->dibayar_kepada,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'keterangan' => $request->keterangan,
                // Perpajakan fields
                'npwp' => $request->npwp,
                'no_faktur' => $request->no_faktur,
                'tanggal_faktur' => $request->tanggal_faktur,
                'tanggal_selesai_verifikasi_pajak' => $request->tanggal_selesai_verifikasi_pajak,
                'jenis_pph' => $request->jenis_pph,
                'dpp_pph' => $dppPph,
                'ppn_terhutang' => $ppnTerhutang,
                'link_dokumen_pajak' => $request->link_dokumen_pajak,
                // Perpajakan Extended Fields
                'komoditi_perpajakan' => $request->komoditi_perpajakan,
                'alamat_pembeli' => $request->alamat_pembeli,
                'no_kontrak' => $request->no_kontrak,
                'no_invoice' => $request->no_invoice,
                'tanggal_invoice' => $request->tanggal_invoice,
                'dpp_invoice' => $dppInvoice,
                'ppn_invoice' => $ppnInvoice,
                'dpp_ppn_invoice' => $dppPpnInvoice,
                'tanggal_pengajuan_pajak' => $request->tanggal_pengajuan_pajak,
                'dpp_faktur' => $dppFaktur,
                'ppn_faktur' => $ppnFaktur,
                'selisih_pajak' => $selisihPajak,
                'keterangan_pajak' => $request->keterangan_pajak,
                'penggantian_pajak' => $penggantianPajak,
                'dpp_penggantian' => $dppPenggantian,
                'ppn_penggantian' => $ppnPenggantian,
                'selisih_ppn' => $selisihPpn,
            ];

            // Store old values for logging
            $oldValues = [
                'npwp' => $dokumen->npwp,
                'no_faktur' => $dokumen->no_faktur,
                'tanggal_faktur' => $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('Y-m-d') : null,
                'tanggal_selesai_verifikasi_pajak' => $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('Y-m-d') : null,
                'jenis_pph' => $dokumen->jenis_pph,
                'dpp_pph' => $dokumen->dpp_pph,
                'ppn_terhutang' => $dokumen->ppn_terhutang,
                'link_dokumen_pajak' => $dokumen->link_dokumen_pajak,
            ];

            $dokumen->update($updateData);
            $dokumen->refresh();

            // Log changes for perpajakan-specific fields
            $perpajakanFields = [
                'npwp' => 'NPWP',
                'no_faktur' => 'No Faktur',
                'tanggal_faktur' => 'Tanggal Faktur',
                'tanggal_selesai_verifikasi_pajak' => 'Tanggal Selesai Verifikasi Pajak',
                'jenis_pph' => 'Jenis PPh',
                'dpp_pph' => 'DPP PPh',
                'ppn_terhutang' => 'PPN Terhutang',
                'link_dokumen_pajak' => 'Link Dokumen Pajak',
            ];

            foreach ($perpajakanFields as $field => $fieldName) {
                $oldValue = $oldValues[$field];
                $newValue = null;

                if ($field === 'tanggal_faktur' || $field === 'tanggal_selesai_verifikasi_pajak') {
                    $newValue = $dokumen->$field ? $dokumen->$field->format('Y-m-d') : null;
                } elseif ($field === 'dpp_pph' || $field === 'ppn_terhutang') {
                    $newValue = $dokumen->$field ? number_format($dokumen->$field, 0, ',', '.') : null;
                } else {
                    $newValue = $dokumen->$field;
                }

                // Only log if value actually changed
                if ($oldValue != $newValue) {
                    try {
                        \App\Helpers\ActivityLogHelper::logDataEdited(
                            $dokumen,
                            $field,
                            $oldValue,
                            $newValue,
                            'perpajakan'
                        );
                    } catch (\Exception $logException) {
                        \Log::error('Failed to log data edit for ' . $field . ': ' . $logException->getMessage());
                    }
                }
            }

            // Update PO numbers
            $dokumen->dokumenPos()->delete();
            if ($request->has('nomor_po')) {
                foreach ($request->nomor_po as $nomorPO) {
                    if (!empty($nomorPO)) {
                        DokumenPO::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_po' => $nomorPO,
                        ]);
                    }
                }
            }

            // Update PR numbers
            $dokumen->dokumenPrs()->delete();
            if ($request->has('nomor_pr')) {
                foreach ($request->nomor_pr as $nomorPR) {
                    if (!empty($nomorPR)) {
                        DokumenPR::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_pr' => $nomorPR,
                        ]);
                    }
                }
            }

            \DB::commit();

            // Check if we should redirect to a custom URL (e.g., returns page)
            if ($request->has('redirect_to') && $request->redirect_to) {
                return redirect($request->redirect_to)
                    ->with('success', 'Dokumen berhasil diperbarui.');
            }

            return redirect()->route('documents.perpajakan.index')
                ->with('success', 'Dokumen berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error updating document in Perpajakan: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui dokumen. Silakan coba lagi.');
        }
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
        $allowedHandlers = ['perpajakan', 'ibuB', 'akutansi'];
        $allowedStatuses = ['sent_to_perpajakan', 'returned_to_department', 'sent_to_akutansi'];

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
            // Documents returned from perpajakan to verifikasi (original logic)
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_department')
                    ->where('target_department', 'perpajakan');
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
            ->orderByDesc('department_returned_at');

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nomor_agenda', 'like', "%{$searchTerm}%")
                    ->orWhere('nomor_spp', 'like', "%{$searchTerm}%")
                    ->orWhere('uraian_spp', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Calculate statistics for returned documents
        // Include both: documents returned from perpajakan to verifikasi AND documents rejected by akutansi
        $baseQuery = Dokumen::where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('status', 'returned_to_department')
                    ->where('target_department', 'perpajakan');
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

        // Menunggu perbaikan: dokumen yang dikembalikan dan masih di verifikasi (belum diperbaiki)
        // Logika: masih di ibuB (belum dikirim kembali) ATAU ditolak oleh akutansi dan masih di perpajakan
        $totalMenungguPerbaikan = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('current_handler', 'ibuB')
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
        // Logika: sudah tidak di ibuB lagi DAN tidak ada rejected status dari akutansi
        $totalSudahDiperbaiki = (clone $baseQuery)
            ->where('current_handler', '!=', 'ibuB')
            ->where('current_handler', '!=', 'perpajakan')
            ->count();

        $data = array(
            "title" => "Daftar Pengembalian Dokumen Perpajakan ke team verifikasi",
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
     * Return document to IbuB
     */
    public function returnDocument(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
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
            \Log::info('Returning document from perpajakan', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'return_reason_length' => strlen($request->return_reason)
            ]);

            // Update all fields in a single call to avoid multiple queries and potential issues
            $updateData = [
                'status' => 'returned_to_department',
                'current_handler' => 'verifikasi',
                'target_department' => 'perpajakan',
                'department_returned_at' => now(),
                'department_return_reason' => $request->return_reason,
                'alasan_pengembalian' => $request->return_reason,
                // Reset tax status since document is being returned
                'status_perpajakan' => null,
                'tanggal_selesai_verifikasi_pajak' => null,
            ];

            // Only set sent_to_ibub_at if it's null (first time entering IbuB)
            // This preserves the original entry time for consistent ordering
            if (is_null($dokumen->sent_to_ibub_at)) {
                $updateData['sent_to_ibub_at'] = now();
            }

            $dokumen->update($updateData);

            \DB::commit();

            \Log::info('Document successfully returned from perpajakan', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikembalikan ke Ibu Yuni.'
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

            // Kirim ke inbox menggunakan sistem inbox yang sudah ada
            $dokumen->sendToInbox($inboxRole);

            // Set tanggal selesai verifikasi pajak (only for akutansi)
            if ($request->next_handler === 'akutansi') {
                $dokumen->tanggal_selesai_verifikasi_pajak = now();
                $dokumen->save();
            }

            \DB::commit();

            $handlerName = $request->next_handler === 'akutansi' ? 'Akutansi' : 'Pembayaran';
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
     * Check for new documents assigned to perpajakan
     */
    public function checkUpdates(Request $request)
    {
        try {
            $lastChecked = $request->input('last_checked', 0);
            $lastCheckedDate = $lastChecked > 0
                ? \Carbon\Carbon::createFromTimestamp($lastChecked)
                : \Carbon\Carbon::now()->subDays(1);

            // Cek dokumen baru yang dikirim ke perpajakan menggunakan dokumen_role_data
            // Exclude documents imported from CSV to prevent notification spam
            $newDocuments = Dokumen::where(function ($query) use ($lastCheckedDate) {
                $query->where(function ($q) {
                    $q->where('current_handler', 'perpajakan')
                        ->orWhere('status', 'sent_to_perpajakan');
                })
                    ->where(function ($q) use ($lastCheckedDate) {
                        // Check if received_at in roleData is newer
                        $q->whereHas('roleData', function ($subQ) use ($lastCheckedDate) {
                            $subQ->where('role_code', 'perpajakan')
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
                        $query->where('role_code', 'perpajakan');
                    }
                ])
                ->latest('updated_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_perpajakan');
            })->count();

            return response()->json([
                'has_updates' => $newDocuments->count() > 0,
                'new_count' => $newDocuments->count(),
                'total_documents' => $totalDocuments,
                'new_documents' => $newDocuments->map(function ($doc) {
                    $roleData = $doc->roleData->firstWhere('role_code', 'perpajakan');
                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => $doc->uraian_spp,
                        'nilai_rupiah' => $doc->nilai_rupiah,
                        'status' => $doc->status,
                        'status_perpajakan' => $doc->status_perpajakan,
                        'sent_at' => $roleData?->received_at?->format('d/m/Y H:i') ?? $doc->updated_at->format('d/m/Y H:i'),
                        'deadline_at' => $roleData?->deadline_at?->format('d/m/Y H:i'),
                    ];
                }),
                'last_checked' => time()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in perpajakan/check-updates: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => true,
                'message' => 'Failed to check updates: ' . $e->getMessage()
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
     * Display the rekapan page for Perpajakan (same as IbuB)
     */
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

        // Base query - only documents that have reached Perpajakan
        // Same filter logic as index() method
        $baseQuery = Dokumen::query()
            ->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_akutansi');
            })
            ->whereYear('tanggal_masuk', $selectedYear);

        // Filter by bagian if selected
        if ($selectedBagian && in_array($selectedBagian, array_keys(self::BAGIAN_LIST))) {
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

        // Get available years (only for documents that reached perpajakan)
        $availableYears = Dokumen::query()
            ->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_akutansi');
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

        // Get document count per bagian for the selected year (only documents that reached perpajakan)
        $bagianCounts = [];
        foreach (self::BAGIAN_LIST as $bagianCode => $bagianName) {
            $countQuery = Dokumen::query()
                ->where(function ($q) {
                    $q->where('current_handler', 'perpajakan')
                        ->orWhere('status', 'sent_to_akutansi');
                })
                ->whereYear('tanggal_masuk', $selectedYear)
                ->where('bagian', $bagianCode);
            $bagianCounts[$bagianCode] = $countQuery->count();
        }

        $data = [
            'title' => 'Analitik Dokumen',
            'module' => 'perpajakan',
            'menuDokumen' => 'active',
            'menuRekapan' => 'active',
            'selectedYear' => (int) $selectedYear,
            'selectedBagian' => $selectedBagian,
            'selectedMonth' => $selectedMonth ? (int) $selectedMonth : null,
            'yearlySummary' => $yearlySummary,
            'monthlyStats' => $monthlyStats,
            'dokumens' => $tableDokumens,
            'availableYears' => $availableYears,
            'bagianList' => self::BAGIAN_LIST,
            'bagianCounts' => $bagianCounts,
        ];

        return view('perpajakan.analytics', $data);
    }

    /**
     * Get statistics for rekapan documents (same as exportView)
     */
    private function getRekapanStatistics(string $filterBagian = ''): array
    {
        // Base Query for Stats - same logic as exportView
        $statsQuery = Dokumen::where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere(function ($subQ) {
                    // Documents sent to perpajakan (not still at ibuB) - check dokumen_role_data
                    $subQ->where('status', 'sent_to_perpajakan')
                        ->where('current_handler', '!=', 'ibuB')
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('received_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents being processed by perpajakan
                    $subQ->where('status', 'sedang diproses')
                        ->where('current_handler', 'perpajakan');
                })
                ->orWhere(function ($subQ) {
                    // Documents that have been processed by perpajakan and moved forward - check dokumen_role_data
                    $subQ->whereIn('status', ['selesai', 'sent_to_akutansi'])
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('processed_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents returned from perpajakan (for tracking) - check status and target_department
                    $subQ->where('status', 'returned_to_department')
                        ->where('target_department', 'perpajakan');
                });
        });

        // Ensure roleData relationship is loaded for whereHas/whereDoesntHave
        $statsQuery->with([
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            }
        ]);

        $countTotal = (clone $statsQuery)->count();
        $countTerkunci = (clone $statsQuery)
            ->where('current_handler', 'perpajakan')
            ->where('status', 'sent_to_perpajakan')
            ->whereDoesntHave('roleData', function ($roleQ) {
                $roleQ->where('role_code', 'perpajakan')
                    ->whereNotNull('deadline_at');
            })
            ->count();
        $countProses = (clone $statsQuery)
            ->where('current_handler', 'perpajakan')
            ->whereNotIn('status', ['selesai', 'sent_to_akutansi'])
            ->whereHas('roleData', function ($roleQ) {
                $roleQ->where('role_code', 'perpajakan')
                    ->whereNotNull('deadline_at');
            })
            ->count();
        $countSelesai = (clone $statsQuery)->where(function ($q) {
            $q->where('status', 'selesai')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('current_handler', 'akutansi');
        })->count();

        // Get bagian stats for backward compatibility
        $bagianStats = [];
        foreach (self::BAGIAN_LIST as $bagianCode => $bagianName) {
            $bagianQuery = Dokumen::where('created_by', 'ibuA')->where('bagian', $bagianCode);
            $bagianStats[$bagianCode] = [
                'name' => $bagianName,
                'total' => $bagianQuery->count()
            ];
        }

        return [
            'total_documents' => $countTotal,
            'terkunci' => $countTerkunci,
            'sedang_diproses' => $countProses,
            'selesai' => $countSelesai,
            'by_bagian' => $bagianStats,
            'by_status' => [
                'draft' => Dokumen::where('created_by', 'ibuA')->where('status', 'draft')->count(),
                'sent_to_ibub' => Dokumen::where('created_by', 'ibuA')->where('status', 'sent_to_ibub')->count(),
                'sedang diproses' => Dokumen::where('created_by', 'ibuA')->where('status', 'sedang diproses')->count(),
                'selesai' => Dokumen::where('created_by', 'ibuA')->where('status', 'selesai')->count(),
                'returned_to_ibua' => Dokumen::where('created_by', 'ibuA')->where('status', 'returned_to_ibua')->count(),
            ]
        ];
    }

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

    /**
     * Export View for Perpajakan
     * Mimmics Pembayaran Rekapan but for Perpajakan
     */
    public function exportView(Request $request)
    {
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');
        $mode = $request->get('mode', 'normal'); // normal or rekapan_table
        $selectedColumns = $request->get('columns', []);

        // Base query - only documents that reached Perpajakan
        // Only show documents where current_handler is 'perpajakan' OR 
        // documents that have been sent to perpajakan (status = 'sent_to_perpajakan' AND current_handler is not 'ibuB')
        // OR documents that have been processed by perpajakan and moved forward
        $query = Dokumen::where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere(function ($subQ) {
                    // Documents sent to perpajakan (not still at ibuB) - check dokumen_role_data
                    $subQ->where('status', 'sent_to_perpajakan')
                        ->where('current_handler', '!=', 'ibuB')
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('received_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents being processed by perpajakan
                    $subQ->where('status', 'sedang diproses')
                        ->where('current_handler', 'perpajakan');
                })
                ->orWhere(function ($subQ) {
                    // Documents that have been processed by perpajakan and moved forward - check dokumen_role_data
                    $subQ->whereIn('status', ['selesai', 'sent_to_akutansi'])
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('processed_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents returned from perpajakan (for tracking) - check status and target_department
                    $subQ->where('status', 'returned_to_department')
                        ->where('target_department', 'perpajakan');
                });
        });

        if ($year) {
            // Filter by year using tanggal_invoice if available, otherwise fallback to created_at
            $query->where(function ($q) use ($year) {
                $q->whereYear('tanggal_invoice', $year)
                    ->orWhere(function ($subQ) use ($year) {
                        $subQ->whereNull('tanggal_invoice')
                            ->whereYear('created_at', $year);
                    });
            });
        }

        if ($month) {
            // Filter by month using tanggal_invoice
            // Only show documents that have tanggal_invoice and match the selected month
            $query->whereNotNull('tanggal_invoice')
                ->whereMonth('tanggal_invoice', $month);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%")
                    ->orWhere('dibayar_kepada', 'like', "%{$search}%");
            });
        }

        // Get documents for display (paginated)
        $perPage = $request->get('per_page', session('perpajakan_export_per_page', 10)); // Default 10, bisa diubah user
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10; // Validate per_page value
        session(['perpajakan_export_per_page' => $perPage]); // Save to session

        $dokumens = $query->with([
            'dokumenPos',
            'dokumenPrs',
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            }
        ])->orderBy('created_at', 'desc')->paginate($perPage);
        $dokumens->appends($request->all());

        // Helper for stats

        // Base Query for Stats (Respects Year/Month, ignores Status Filter to show breakdown)
        // Same logic as main query to ensure consistency
        $statsQuery = Dokumen::where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere(function ($subQ) {
                    // Documents sent to perpajakan (not still at ibuB) - check dokumen_role_data
                    $subQ->where('status', 'sent_to_perpajakan')
                        ->where('current_handler', '!=', 'ibuB')
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('received_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents being processed by perpajakan
                    $subQ->where('status', 'sedang diproses')
                        ->where('current_handler', 'perpajakan');
                })
                ->orWhere(function ($subQ) {
                    // Documents that have been processed by perpajakan and moved forward - check dokumen_role_data
                    $subQ->whereIn('status', ['selesai', 'sent_to_akutansi'])
                        ->whereHas('roleData', function ($roleQ) {
                        $roleQ->where('role_code', 'perpajakan')
                            ->whereNotNull('processed_at');
                    });
                })
                ->orWhere(function ($subQ) {
                    // Documents returned from perpajakan (for tracking) - check status and target_department
                    $subQ->where('status', 'returned_to_department')
                        ->where('target_department', 'perpajakan');
                });
        });
        if ($year) {
            // Filter by year using tanggal_invoice if available, otherwise fallback to created_at
            $statsQuery->where(function ($q) use ($year) {
                $q->whereYear('tanggal_invoice', $year)
                    ->orWhere(function ($subQ) use ($year) {
                        $subQ->whereNull('tanggal_invoice')
                            ->whereYear('created_at', $year);
                    });
            });
        }
        if ($month) {
            // Filter by month using tanggal_invoice
            // Only show documents that have tanggal_invoice and match the selected month
            $statsQuery->whereNotNull('tanggal_invoice')
                ->whereMonth('tanggal_invoice', $month);
        }

        // Ensure roleData relationship is loaded for whereHas/whereDoesntHave
        $statsQuery->with([
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            }
        ]);

        $countTotal = (clone $statsQuery)->count();
        $countTerkunci = (clone $statsQuery)
            ->where('current_handler', 'perpajakan')
            ->where('status', 'sent_to_perpajakan')
            ->whereDoesntHave('roleData', function ($roleQ) {
                $roleQ->where('role_code', 'perpajakan')
                    ->whereNotNull('deadline_at');
            })
            ->count();
        $countProses = (clone $statsQuery)
            ->where('current_handler', 'perpajakan')
            ->whereNotIn('status', ['selesai', 'sent_to_akutansi'])
            ->whereHas('roleData', function ($roleQ) {
                $roleQ->where('role_code', 'perpajakan')
                    ->whereNotNull('deadline_at');
            })
            ->count();
        $countSelesai = (clone $statsQuery)->where(function ($q) {
            $q->where('status', 'selesai')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('current_handler', 'akutansi');
        })->count();

        $statistics = [
            'total_documents' => $countTotal,
            'terkunci' => $countTerkunci,
            'sedang_diproses' => $countProses,
            'selesai' => $countSelesai
        ];

        // Available Years
        $availableYears = Dokumen::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Column Definitions - Extended with all Perpajakan-specific fields
        // Column Definitions - Extended with all Perpajakan-specific fields
        $availableColumns = $this->getAvailableColumns('normal');

        // Filter out nomor_mirror and nomor_miro from selectedColumns if present
        if (is_array($selectedColumns)) {
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'nomor_mirror' && $col !== 'nomor_miro';
            });
            $selectedColumns = array_values($selectedColumns);
        } elseif (is_string($selectedColumns) && !empty($selectedColumns)) {
            $columns = explode(',', $selectedColumns);
            $columns = array_map('trim', $columns);
            $selectedColumns = array_filter($columns, function ($col) {
                return $col !== 'nomor_mirror' && $col !== 'nomor_miro';
            });
            $selectedColumns = array_values($selectedColumns);
        }

        // Ensure selectedColumns only contains valid columns from availableColumns
        if (is_array($selectedColumns)) {
            $selectedColumns = array_filter($selectedColumns, function ($col) use ($availableColumns) {
                return isset($availableColumns[$col]);
            });
            $selectedColumns = array_values($selectedColumns);
        }

        return view('perpajakan.export.index', [
            'title' => 'Export Data Perpajakan',
            'module' => 'perpajakan',
            'dokumens' => $dokumens,
            'statistics' => $statistics,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'search' => $search,
            'availableYears' => $availableYears,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
            'mode' => $mode,
            'perPage' => $perPage
        ]);
    }

    /**
     * Get available columns for export/view
     */
    private function getAvailableColumns($mode)
    {
        return [
            // Basic Document Info
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_spp' => 'Tanggal SPP',
            'uraian_spp' => 'Uraian SPP',
            'dibayar_kepada' => 'Dibayar Kepada',
            'nilai_rupiah' => 'Nilai Rupiah',
            'kategori' => 'Kriteria CF',
            'jenis_dokumen' => 'Sub Kriteria',
            'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'kebun' => 'Kebun',
            'bagian' => 'Bagian',
            'nama_pengirim' => 'Nama Pengirim',
            // Contract Info
            'no_berita_acara' => 'No Berita Acara',
            'tanggal_berita_acara' => 'Tanggal Berita Acara',
            'no_spk' => 'No SPK',
            'tanggal_spk' => 'Tanggal SPK',
            'tanggal_berakhir_spk' => 'Tanggal Berakhir SPK',
            // Status & Workflow
            'status' => 'Status',
            'current_handler' => 'Handler Saat Ini',
            'deadline_at' => 'Deadline',
            'keterangan' => 'Keterangan',
            // Perpajakan Specific Fields
            'npwp' => 'NPWP',
            'status_perpajakan' => 'Status Perpajakan',
            'no_faktur' => 'No Faktur Pajak',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tgl Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPN Terhutang',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
            'deadline_perpajakan_at' => 'Deadline Perpajakan',
            // Perpajakan Extended Fields
            'komoditi_perpajakan' => 'Komoditi Perpajakan',
            'alamat_pembeli' => 'Alamat Pembeli',
            'no_kontrak' => 'No Kontrak',
            'no_invoice' => 'No Invoice',
            'tanggal_invoice' => 'Tanggal Invoice',
            'dpp_invoice' => 'DPP Invoice',
            'ppn_invoice' => 'PPN Invoice',
            'dpp_ppn_invoice' => 'DPP + PPN Invoice',
            'tanggal_pengajuan_pajak' => 'Tanggal Pengajuan Pajak',
            'dpp_faktur' => 'DPP Faktur',
            'ppn_faktur' => 'PPN Faktur',
            'selisih_pajak' => 'Selisih Pajak',
            'keterangan_pajak' => 'Keterangan Pajak',
            'penggantian_pajak' => 'Penggantian Pajak',
            'dpp_penggantian' => 'DPP Penggantian',
            'ppn_penggantian' => 'PPN Penggantian',
            'selisih_ppn' => 'Selisih PPN',
            // Timestamps
            'created_at' => 'Tanggal Masuk',
            // Note: sent_to_perpajakan_at and processed_perpajakan_at are now in dokumen_role_data
            // They can be accessed via $dokumen->getDataForRole('perpajakan')->received_at and processed_at
        ];
    }

    /**
     * Build the query for export and view based on filters
     */
    private function buildQuery($mode, Request $request)
    {
        $status = $request->get('status');
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');

        // Base query - only documents that reached Perpajakan
        $query = Dokumen::where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'sedang diproses')
                ->orWhere('status', 'selesai')
                ->orWhere('status', 'sent_to_akutansi');
        });

        if ($year) {
            // Filter by year using tanggal_invoice if available, otherwise fallback to created_at
            $query->where(function ($q) use ($year) {
                $q->whereYear('tanggal_invoice', $year)
                    ->orWhere(function ($subQ) use ($year) {
                        $subQ->whereNull('tanggal_invoice')
                            ->whereYear('created_at', $year);
                    });
            });
        }

        if ($month) {
            // Filter by month using tanggal_invoice
            // Only show documents that have tanggal_invoice and match the selected month
            $query->whereNotNull('tanggal_invoice')
                ->whereMonth('tanggal_invoice', $month);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%")
                    ->orWhere('dibayar_kepada', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function exportData(Request $request)
    {
        $mode = $request->query('mode', 'dashboard');
        $query = $this->buildQuery($mode, $request);

        // Handle columns from request - can be array or comma-separated string
        $selectedColumns = $request->input('columns', '');
        $availableColumns = $this->getAvailableColumns($mode);

        if ($selectedColumns) {
            // Handle both array and string formats
            if (is_array($selectedColumns)) {
                $columns = $selectedColumns;
            } else {
                $columns = explode(',', $selectedColumns);
                $columns = array_map('trim', $columns);
            }

            // Filter out invalid columns and nomor_mirror/nomor_miro
            $columns = array_filter($columns, function ($col) use ($availableColumns) {
                return isset($availableColumns[$col]) && $col !== 'nomor_mirror' && $col !== 'nomor_miro';
            });
            // Re-index array
            $columns = array_values($columns);

            // If no valid columns left, use all available columns
            if (empty($columns)) {
                $columns = array_keys($availableColumns);
            }
        } else {
            $columns = array_keys($availableColumns);
        }

        // Load necessary relationships for export
        $docs = $query->with([
            'dibayarKepadas',
            'dokumenPos',
            'dokumenPrs',
            'roleData' => function ($q) {
                $q->where('role_code', 'perpajakan');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($docs->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk diexport dengan filter saat ini.');
        }

        $filename = 'export-perpajakan-' . date('Y-m-d-H-i-s') . '.xls';

        // Date, Currency, Text field definitions
        // Note: sent_to_perpajakan_at and processed_perpajakan_at are now in dokumen_role_data
        $dateFields = ['tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk', 'tanggal_faktur', 'tanggal_selesai_verifikasi_pajak', 'tanggal_invoice', 'tanggal_pengajuan_pajak', 'created_at', 'deadline_at', 'deadline_perpajakan_at'];
        $currencyFields = ['nilai_rupiah', 'dpp_pph', 'ppn_terhutang', 'dpp_invoice', 'ppn_invoice', 'dpp_ppn_invoice', 'dpp_faktur', 'ppn_faktur', 'selisih_pajak', 'penggantian_pajak', 'dpp_penggantian', 'ppn_penggantian', 'selisih_ppn'];
        $textFields = ['npwp', 'no_faktur', 'no_invoice', 'no_kontrak', 'nomor_agenda', 'nomor_spp', 'no_berita_acara', 'no_spk'];

        // Build HTML Table for Excel
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Data Perpajakan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>table { border-collapse: collapse; } th, td { border: 1px solid #000; padding: 5px; } th { background-color: #083E40; color: #FFFFFF; font-weight: bold; text-align: center; }</style>';
        $html .= '</head><body><table>';

        // Header row
        $html .= '<tr>';
        foreach ($columns as $col) {
            $html .= '<th>' . strtoupper($availableColumns[$col] ?? ucwords(str_replace('_', ' ', $col))) . '</th>';
        }
        $html .= '</tr>';

        // Data rows
        foreach ($docs as $doc) {
            $html .= '<tr>';
            foreach ($columns as $col) {
                // Safely get column value
                try {
                    // Handle special columns that come from relationships
                    if ($col === 'dibayar_kepada') {
                        // Get from relationship if available
                        if ($doc->dibayarKepadas && $doc->dibayarKepadas->count() > 0) {
                            $value = $doc->dibayarKepadas->pluck('nama_penerima')->join(', ');
                        } else {
                            $value = $doc->dibayar_kepada ?? null;
                        }
                    } elseif ($col === 'nomor_po' || $col === 'no_po') {
                        // Get PO numbers from relationship
                        if ($doc->dokumenPos && $doc->dokumenPos->count() > 0) {
                            $value = $doc->dokumenPos->pluck('nomor_po')->join(', ');
                        } else {
                            $value = null;
                        }
                    } elseif ($col === 'nomor_pr' || $col === 'no_pr') {
                        // Get PR numbers from relationship
                        if ($doc->dokumenPrs && $doc->dokumenPrs->count() > 0) {
                            $value = $doc->dokumenPrs->pluck('nomor_pr')->join(', ');
                        } else {
                            $value = null;
                        }
                    } elseif ($col === 'sent_to_perpajakan_at') {
                        // Get from dokumen_role_data
                        $roleData = $doc->getDataForRole('perpajakan');
                        $value = $roleData?->received_at;
                    } elseif ($col === 'processed_perpajakan_at') {
                        // Get from dokumen_role_data
                        $roleData = $doc->getDataForRole('perpajakan');
                        $value = $roleData?->processed_at;
                    } elseif ($col === 'deadline_at' || $col === 'deadline_perpajakan_at') {
                        // Get from dokumen_role_data
                        $roleData = $doc->getDataForRole('perpajakan');
                        $value = $roleData?->deadline_at;
                    } else {
                        // Regular column access - use getAttribute for safe access
                        try {
                            $value = $doc->getAttribute($col);
                        } catch (\Exception $e) {
                            $value = null;
                        }
                    }
                } catch (\Exception $e) {
                    $value = null;
                }

                if (in_array($col, $textFields) && $value) {
                    // Force text format in Excel
                    $html .= '<td style="mso-number-format:\@">' . htmlspecialchars((string) $value) . '</td>';
                } elseif (in_array($col, $dateFields) && $value) {
                    // Handle date fields safely
                    try {
                        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
                            $html .= '<td>' . $value->format('d/m/Y') . '</td>';
                        } elseif (is_string($value)) {
                            $date = \Carbon\Carbon::parse($value);
                            $html .= '<td>' . $date->format('d/m/Y') . '</td>';
                        } else {
                            $html .= '<td>' . htmlspecialchars($value ?? '-') . '</td>';
                        }
                    } catch (\Exception $e) {
                        $html .= '<td>' . htmlspecialchars($value ?? '-') . '</td>';
                    }
                } elseif (in_array($col, $currencyFields) && is_numeric($value)) {
                    $html .= '<td style="mso-number-format:#,##0">' . number_format((float) $value, 0, ',', '.') . '</td>';
                } else {
                    $html .= '<td>' . htmlspecialchars($value ?? '-') . '</td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $exportType = $request->input('export', 'excel');

        if ($exportType === 'pdf') {
            // Get filter values for PDF
            $year = $request->get('year');
            $month = $request->get('month');
            $search = $request->get('search');

            // Prepare data for PDF view
            $pdfData = [
                'dokumens' => $docs,
                'columns' => $columns,
                'availableColumns' => $availableColumns,
                'title' => 'Export Data Perpajakan',
                'year' => $year,
                'month' => $month,
                'search' => $search,
                'dateFields' => $dateFields,
                'currencyFields' => $currencyFields,
            ];

            // Return view that can be printed as PDF using browser print
            return view('perpajakan.export.pdf', $pdfData);
        }

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
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

