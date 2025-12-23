<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Requests\SetDeadlineRequest;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\Bidang;
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
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;

class DashboardBController extends Controller
{
    public function index()
    {
        // Get statistics for IbuB (only documents with current_handler = ibuB)

        // 1. Total dokumen - semua dokumen yang terlihat oleh ibuB (same as dokumens() query)
        $totalDokumen = Dokumen::where(function ($q) {
            $q->where('current_handler', 'ibuB')
                ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
        })
            ->where('status', '!=', 'returned_to_bidang')
            ->count();

        // 2. Total dokumen proses - dokumen yang sedang diproses
        $totalDokumenProses = Dokumen::where('current_handler', 'ibuB')
            ->whereIn('status', ['sent_to_ibub', 'sedang diproses'])
            ->count();

        // 3. Total dokumen approved - dokumen yang disetujui ibuB
        $totalDokumenApproved = Dokumen::where('current_handler', 'ibuB')
            ->whereIn('status', ['approved_ibub', 'selesai'])
            ->count();

        // 4. Total dokumen rejected - dokumen yang ditolak ibuB (dibalikkan ke ibuA)
        $totalDokumenRejected = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'rejected_ibub')
            ->count();

        // 5. Total dokumen pengembalian ke bidang - dokumen yang dikembalikan ke bidang
        $totalDokumenPengembalianKeBidang = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'returned_to_bidang')
            ->count();

        // 6. Total dokumen pengembalian dari bagian - dokumen yang dikembalikan dari perpajakan/akutansi/pembayaran ke ibuB
        $totalDokumenPengembalianDariBagian = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'returned_to_department')
            ->count();

        // Get latest documents (5 most recent) for ibuB - same logic as dokumens() method
        $dokumenTerbaru = Dokumen::where(function ($q) {
            $q->where('current_handler', 'ibuB')
                ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
        })
            ->where('status', '!=', 'returned_to_bidang')
            ->orderByRaw("CASE
                WHEN current_handler = 'ibuB' AND status IN ('sent_to_ibub', 'sedang diproses') THEN 1
                WHEN current_handler = 'ibuB' THEN 2
                ELSE 3
            END ASC")
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Team Verifikasi",
            "module" => "ibuB",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'totalDokumen' => $totalDokumen,
            'totalDokumenProses' => $totalDokumenProses,
            'totalDokumenApproved' => $totalDokumenApproved,
            'totalDokumenRejected' => $totalDokumenRejected,
            'totalDokumenPengembalianKeBidang' => $totalDokumenPengembalianKeBidang,
            'totalDokumenPengembalianDariBagian' => $totalDokumenPengembalianDariBagian,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('ibuB.dashboardB', $data);
    }

    public function dokumens(Request $request)
    {
        // IbuB sees:
        // 1. Documents with current_handler = ibuB (active documents) - including approved via universal approval
        // 2. Documents with status sedang_diproses and current_handler = ibuB (from universal approval)
        // 3. Documents that were sent to perpajakan/akutansi (for tracking)
        // Exclude documents that are returned to bidang (they should appear in pengembalian ke bidang page)
        // Exclude pending approval documents (they should use inbox)
        // Optimized query - only load essential columns for list view
        // Base query - akan dimodifikasi oleh filter status jika ada
        $query = Dokumen::with('activityLogs')
            ->where('status', '!=', 'returned_to_bidang');

        // Apply base filter only if no status filter is specified
        // If status filter is specified, it will override base filter
        // Exclude CSV imported documents - they are exclusive to Pembayaran module
        if (!$request->has('status') || !$request->status) {
            $query->where(function ($q) {
                $q->where('current_handler', 'ibuB')
                    ->orWhere(function ($subQ) {
                        // Handle both status formats (with space and underscore) for backward compatibility
                        $subQ->where(function ($statusQ) {
                            $statusQ->where('status', 'sedang diproses')
                                ->orWhere('status', 'sedang_diproses');
                        })
                            ->where('current_handler', 'ibuB');
                    })
                    ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi', 'pending_approval_perpajakan', 'pending_approval_akutansi']) // Include documents sent to perpajakan/akutansi (exclude sent_to_pembayaran - those are CSV imports)
                    ->orWhere(function ($rejectQ) {
                        // FIX: Tampilkan dokumen yang direject dari Akutansi/Perpajakan
                        $rejectQ->where('status', 'returned_to_department')
                            ->whereIn('target_department', ['perpajakan', 'akutansi'])
                            ->where('current_handler', 'ibuB');
                    });
            })
            // Exclude CSV imported documents (only if column exists)
            ->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                      ->orWhereNull('imported_from_csv');
                });
            });
        } else {
            // Even when status filter is applied, exclude CSV imported documents
            $query->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                      ->orWhereNull('imported_from_csv');
                });
            });
        }

        $query->leftJoin('dokumen_role_data as ibub_data', function ($join) {
            $join->on('dokumens.id', '=', 'ibub_data.dokumen_id')
                ->where('ibub_data.role_code', '=', 'ibub');
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
                'dokumens.alasan_pengembalian',
                // Deadline fields are now in dokumen_role_data table - use aliases for easier access
                'ibub_data.deadline_at as deadline_at',
                'ibub_data.deadline_days as deadline_days',
                'ibub_data.deadline_note as deadline_note',
                'dokumens.current_handler',
                'dokumens.bulan',
                'dokumens.tahun',
                'dokumens.kategori',
                'dokumens.kebun',
                'dokumens.jenis_dokumen',
                'dokumens.updated_at',
                'dokumens.tanggal_spk',
                'dokumens.tanggal_berakhir_spk',
                'dokumens.no_spk',
                'dokumens.nomor_mirror',
                'dokumens.nama_pengirim',
                'dokumens.jenis_pembayaran',
                'dokumens.dibayar_kepada',
                'dokumens.no_berita_acara',
                'dokumens.tanggal_berita_acara',
                // 'dokumens.inbox_approval_responded_at', // REMOVED - now in dokumen_statuses
                // 'dokumens.inbox_approval_reason', // REMOVED
                // 'dokumens.inbox_approval_for', // REMOVED
                // 'dokumens.inbox_approval_status', // REMOVED
                'dokumens.created_by'
            ])
            ->orderByRaw("CASE 
                WHEN dokumens.nomor_agenda REGEXP '^[0-9]+$' THEN CAST(dokumens.nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC")
            ->orderBy('dokumens.nomor_agenda', 'DESC') // Secondary sort for non-numeric or same numeric values
            ->orderByRaw("
                COALESCE(ibub_data.received_at, dokumens.created_at) DESC,
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
                    ->orWhere('no_berita_acara', 'like', '%' . $search . '%')
                    ->orWhere('no_spk', 'like', '%' . $search . '%')
                    ->orWhere('nomor_mirror', 'like', '%' . $search . '%')
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

        // Filter by status - Apply strict filtering (override base filter)
        if ($request->has('status') && $request->status) {
            $statusFilter = $request->status;
            switch ($statusFilter) {
                case 'deadline':
                    // Dokumen yang memiliki deadline (deadline_at tidak null) dan masih dalam scope Ibu Yuni
                    $query->whereNotNull('ibub_data.deadline_at')
                        ->where('dokumens.current_handler', 'ibuB')
                        // Pastikan bukan status terkirim atau selesai
                        ->whereNotIn('status', [
                            'sent_to_perpajakan',
                            'sent_to_akutansi',
                            'sent_to_pembayaran',
                            'selesai',
                            'completed'
                        ]);
                    break;
                case 'sedang_proses':
                    // Dokumen yang sedang diproses oleh Ibu Yuni - hanya status spesifik
                    $query->where('current_handler', 'ibuB')
                        ->where(function ($q) {
                            // Status yang termasuk "sedang diproses"
                            $q->where('status', 'sedang diproses')
                                ->orWhere('status', 'sedang_diproses')
                                ->orWhere('status', 'waiting_reviewer_approval');
                        })
                        // Exclude dokumen yang sudah terkirim atau ditolak
                        ->whereNotIn('status', [
                            'sent_to_perpajakan',
                            'sent_to_akutansi',
                            'sent_to_pembayaran',
                            'returned_to_department',
                            'selesai',
                            'completed',
                            'approved_ibub'
                        ])
                        // Exclude dokumen yang ditolak
                        ->where(function ($inboxQ) {
                            $inboxQ->whereDoesntHave('roleStatuses', function ($q) {
                                // Exclude rejected by ibuB
                                $q->where('role_code', 'ibub')->where('status', 'rejected');
                            });
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
                    // Dokumen yang terkirim ke pembayaran - hanya status ini saja
                    $query->where('status', 'sent_to_pembayaran');
                    break;
                case 'ditolak':
                    // Dokumen yang ditolak - hanya status ditolak saja
                    $query->where(function ($q) {
                        $q->where(function ($rejectQ) {
                            $rejectQ->where('status', 'returned_to_department')
                                ->orWhereHas('roleStatuses', function ($rq) {
                                    $rq->where('role_code', 'ibub')->where('status', 'rejected');
                                });
                        })
                            ->where(function ($handlerQ) {
                                // Pastikan masih dalam scope Ibu Yuni
                                $handlerQ->where('current_handler', 'ibuB')
                                    ->orWhere(function ($subQ) {
                                    $subQ->where('status', 'returned_to_department')
                                        ->whereIn('target_department', ['perpajakan', 'akutansi'])
                                        ->where('current_handler', 'ibuB');
                                });
                            });
                    });
                    break;
            }
        }

        // Use eager loading for relations to prevent N+1 queries
        $dokumens = $query->with([
                'dibayarKepadas', 
                'roleData' => function($query) {
                    $query->where('role_code', 'ibub');
                },
                'roleStatuses' => function($query) {
                    // Load all role statuses to check for pending approvals
                    $query->whereIn('role_code', ['ibub', 'perpajakan', 'akutansi', 'pembayaran']);
                }
            ])
            ->withCount([
                'dokumenPos',
                'dokumenPrs'
            ]);
        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());
        
        // Cast deadline_at from alias to Carbon if it's a string
        $dokumens->getCollection()->transform(function($dokumen) {
            if ($dokumen->deadline_at && is_string($dokumen->deadline_at)) {
                try {
                    $dokumen->deadline_at = \Carbon\Carbon::parse($dokumen->deadline_at);
                } catch (\Exception $e) {
                    $dokumen->deadline_at = null;
                }
            }
            return $dokumen;
        });

        // Cache statistics for better performance
        $cacheKey = 'ibub_stats_' . md5($request->fullUrl());
        $statistics = \Cache::remember($cacheKey, 300, function () {
            return Dokumen::where('current_handler', 'ibuB')
                ->selectRaw('
                    COUNT(*) as total_dibaca,
                    SUM(CASE WHEN status = "returned_to_ibua" THEN 1 ELSE 0 END) as total_dikembalikan,
                    SUM(CASE WHEN status IN ("approved_ibub", "selesai") THEN 1 ELSE 0 END) as total_dikirim
                ')
                ->first();
        });

        $totalDibaca = $statistics->total_dibaca ?? 0;
        $totalDikembalikan = $statistics->total_dikembalikan ?? 0;
        $totalDikirim = $statistics->total_dikirim ?? 0;

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year, 'ibuB');
        }

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nomor_mirror' => 'Nomor Miro',
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
                $preferences['ibub'] = $selectedColumns;
                $user->table_columns_preferences = $preferences;
                $user->save();
            }
            // Also save to session for backward compatibility
            session(['ibub_dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from database first (permanent), then fallback to session, then default
            $user = Auth::user();
            $defaultColumns = [
                'nomor_agenda',
                'nomor_spp',
                'tanggal_masuk',
                'nilai_rupiah',
                'nomor_mirror'
            ];

            if ($user && isset($user->table_columns_preferences['ibub'])) {
                $selectedColumns = $user->table_columns_preferences['ibub'];
            } else {
                // Fallback to session if available
                $selectedColumns = session('ibub_dokumens_table_columns', $defaultColumns);
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
            session(['ibub_dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Dokumen Team Verifikasi",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'totalDibaca' => $totalDibaca,
            'totalDikembalikan' => $totalDikembalikan,
            'totalDikirim' => $totalDikirim,
            'suggestions' => $suggestions,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
        );
        return view('ibuB.dokumens.daftarDokumenB', $data);
    }

    public function createDokumen()
    {
        $data = array(
            "title" => "Tambah Dokumen Team Verifikasi",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        return view('ibuB.dokumens.tambahDokumenB', $data);
    }

    public function storeDokumen(Request $request)
    {
        // Implementation for storing document
        return redirect()->route('documents.verifikasi.index')->with('success', 'Dokumen berhasil ditambahkan');
    }

    public function editDokumen(Dokumen $dokumen)
    {
        // Only allow editing if current_handler is ibuB
        if ($dokumen->current_handler !== 'ibuB') {
            return redirect()->route('documents.verifikasi.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        // Load relationships including dibayarKepadas
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Ambil data dari database cash_bank_new untuk dropdown baru
        // Tambahkan try-catch untuk menangani error koneksi database
        try {
            $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = SubKriteria::all();
            $itemSubKriteria = ItemSubKriteria::all();
        } catch (\Exception $e) {
            \Log::error('Error fetching cash_bank data: ' . $e->getMessage());
            // Fallback: gunakan collection kosong jika error
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
        }

        // Cari ID dari nama yang tersimpan di database (untuk backward compatibility)
        $selectedKriteriaCfId = null;
        $selectedSubKriteriaId = null;
        $selectedItemSubKriteriaId = null;

        try {
            if ($dokumen->kategori) {
                $foundKategori = KategoriKriteria::where('nama_kriteria', $dokumen->kategori)->first();
                if ($foundKategori) {
                    $selectedKriteriaCfId = $foundKategori->id_kategori_kriteria;
                }
            }

            if ($dokumen->jenis_dokumen) {
                $foundSub = SubKriteria::where('nama_sub_kriteria', $dokumen->jenis_dokumen)->first();
                if ($foundSub) {
                    $selectedSubKriteriaId = $foundSub->id_sub_kriteria;
                }
            }

            if ($dokumen->jenis_sub_pekerjaan) {
                $foundItem = ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan)->first();
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
            "module" => "ibuB",
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
        );
        return view('ibuB.dokumens.editDokumenB', $data);
    }

    public function updateDokumen(Request $request, Dokumen $dokumen)
    {
        // Only allow updating if current_handler is ibuB
        if ($dokumen->current_handler !== 'ibuB') {
            return redirect()->route('documents.verifikasi.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengupdate dokumen ini.');
        }

        $validator = \Validator::make($request->all(), [
            'nomor_agenda' => 'required|string|unique:dokumens,nomor_agenda,' . $dokumen->id,
            'bulan' => 'required|string',
            'tahun' => 'required|integer|min:2020|max:2030',
            'tanggal_masuk' => 'required|date',
            'nomor_spp' => 'required|string',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'required|string',
            'nilai_rupiah' => 'required|string',
            'kriteria_cf' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!KategoriKriteria::where('id_kategori_kriteria', $value)->exists()) {
                    $fail('Kriteria CF yang dipilih tidak valid.');
                }
            }],
            'sub_kriteria' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!SubKriteria::where('id_sub_kriteria', $value)->exists()) {
                    $fail('Sub Kriteria yang dipilih tidak valid.');
                }
            }],
            'item_sub_kriteria' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!ItemSubKriteria::where('id_item_sub_kriteria', $value)->exists()) {
                    $fail('Item Sub Kriteria yang dipilih tidak valid.');
                }
            }],
            // Keep old fields as nullable for backward compatibility
            'kategori' => 'nullable|string',
            'jenis_dokumen' => 'nullable|string',
            'jenis_sub_pekerjaan' => 'nullable|string',
            'jenis_pembayaran' => 'nullable|string',
            'dibayar_kepada' => 'nullable|string',
            'no_berita_acara' => 'nullable|string',
            'tanggal_berita_acara' => 'nullable|date',
            'no_spk' => 'nullable|string',
            'tanggal_spk' => 'nullable|date',
            'tanggal_berakhir_spk' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'nomor_po' => 'array',
            'nomor_po.*' => 'nullable|string',
            'nomor_pr' => 'array',
            'nomor_pr.*' => 'nullable|string',
        ], [
            'nomor_agenda.unique' => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2030.',
            'kriteria_cf.required' => 'Kriteria CF wajib dipilih.',
            'sub_kriteria.required' => 'Sub Kriteria wajib dipilih.',
            'item_sub_kriteria.required' => 'Item Sub Kriteria wajib dipilih.',
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

            // Determine new status based on document state
            $newStatus = $dokumen->status;
            $resetInboxRejection = false;

            // If document was rejected from inbox or returned, reset to "sedang diproses"
            $ibuBStatus = $dokumen->getStatusForRole('ibub');
            $isRejectedByIbuB = $ibuBStatus && $ibuBStatus->status === 'rejected';

            if (
                $isRejectedByIbuB ||
                $dokumen->status === 'returned_to_department'
            ) {
                $newStatus = 'sedang diproses';
                $resetInboxRejection = true;
            }

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;
            
            if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
            }
            
            if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                $subKriteria = SubKriteria::find($request->sub_kriteria);
            }
            
            if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                $itemSubKriteria = ItemSubKriteria::find($request->item_sub_kriteria);
            }

            // Update dokumen
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
                'bagian' => $request->bagian,
                'nama_pengirim' => $request->nama_pengirim,
                'dibayar_kepada' => $request->dibayar_kepada,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'nomor_mirror' => $request->nomor_mirror,
                'status' => $newStatus, // Reset status to "sedang diproses" if was rejected/returned
                'keterangan' => $request->keterangan,
            ];

            // Reset inbox rejection status if needed
            if ($resetInboxRejection) {
                // Clear DokumenStatus rejection for IbuB if resetting
                $dokumenStatus = \App\Models\DokumenStatus::updateOrCreate(
                    ['dokumen_id' => $dokumen->id, 'role_code' => 'ibub'],
                    ['status' => 'pending'] // Atau status awal lain, e.g. 'pending' atau NULL jika perlu dihapus
                );

                // Reset role status fields - now handled by dokumen_statuses table
            }

            $dokumen->update($updateData);

            // Update PO numbers - delete existing and create new
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

            // Update PR numbers - delete existing and create new
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

            // Check if document is returned document and redirect accordingly
            $isReturnedDocument = ($dokumen->status === 'returned_to_department' ||
                $dokumen->department_returned_at);

            // Also check referer to be more accurate
            $referer = request()->header('referer');
            $fromPengembalian = $referer && str_contains($referer, 'pengembalian-dokumensB');

            if ($isReturnedDocument || $fromPengembalian) {
                session()->flash('success', 'Dokumen berhasil diperbarui.');
                return redirect()->route('pengembalianB.index');
            } else {
                session()->flash('success', 'Dokumen berhasil diperbarui.');
                return redirect()->route('documents.verifikasi.index');
            }

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error updating document in IbuB: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui dokumen. Silakan coba lagi.');
        }
    }

    /**
     * Get document detail for AJAX request
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        try {
            Log::info('Accessing document detail', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler ?? 'null',
                'status' => $dokumen->status ?? 'null',
                'user_agent' => request()->userAgent(),
                'wants_json' => request()->wantsJson(),
                'is_ajax' => request()->ajax(),
            ]);

            $allowedHandlers = ['ibuB', 'perpajakan', 'akutansi', 'ibuA', 'pembayaran'];
            $allowedStatuses = ['sent_to_ibub', 'sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran', 'approved_ibub', 'returned_to_department', 'returned_to_bidang', 'returned_to_ibua'];

            // Allow if rejected by IbuB
            $isInboxRejected = false;
            $ibuBStatus = $dokumen->getStatusForRole('ibub');
            if ($ibuBStatus && $ibuBStatus->status === 'rejected') {
                $isInboxRejected = true;
            }

            if (!in_array($dokumen->current_handler ?? '', $allowedHandlers) && !in_array($dokumen->status ?? '', $allowedStatuses) && !$isInboxRejected) {
                Log::warning('Access denied for document detail', [
                    'document_id' => $dokumen->id,
                    'current_handler' => $dokumen->current_handler ?? 'null',
                    'status' => $dokumen->status ?? 'null',
                ]);

                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Access denied'], 403);
                }
                return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
            }

            // Load required relationships
            try {
                $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);
            } catch (\Exception $e) {
                Log::error('Failed to load relationships', [
                    'document_id' => $dokumen->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue anyway, relationships might be optional
            }

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
                        'dibayar_kepada' => ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                            ? $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ')
                            : ($dokumen->dibayar_kepada ?? null),
                        'kebun' => $dokumen->kebun,
                        'no_spk' => $dokumen->no_spk,
                        'tanggal_spk' => $dokumen->tanggal_spk,
                        'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk,
                        'nomor_mirror' => $dokumen->nomor_mirror,
                        'no_berita_acara' => $dokumen->no_berita_acara,
                        'tanggal_berita_acara' => $dokumen->tanggal_berita_acara,
                        'dokumen_pos' => $dokumen->dokumenPos ? $dokumen->dokumenPos->map(function ($po) {
                            return ['nomor_po' => $po->nomor_po ?? ''];
                        })->values() : [],
                        'dokumen_prs' => $dokumen->dokumenPrs ? $dokumen->dokumenPrs->map(function ($pr) {
                            return ['nomor_pr' => $pr->nomor_pr ?? ''];
                        })->values() : [],
                    ]
                ]);
            }

            // Generate HTML with error handling
            try {
                $html = $this->generateDocumentDetailHtml($dokumen);
            } catch (\Exception $e) {
                Log::error('Failed to generate document detail HTML', [
                    'document_id' => $dokumen->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response('<div class="text-center p-4 text-danger">Error generating document view</div>', 500);
            }

            Log::info('Document detail generated successfully', [
                'document_id' => $dokumen->id,
                'html_length' => strlen($html),
            ]);

            return response($html);

        } catch (\Exception $e) {
            Log::error('Unexpected error in getDocumentDetail', [
                'document_id' => $dokumen->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }

            return response('<div class="text-center p-4 text-danger">Unexpected error occurred</div>', 500);
        }
    }

    /**
     * Generate HTML for document detail
     */
    private function generateDocumentDetailHtml($dokumen): string
    {
        $html = '<div class="detail-grid">';

        $detailItems = [
            'Tanggal Masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
            'Bulan' => $dokumen->bulan,
            'Tahun' => $dokumen->tahun,
            'No SPP' => $dokumen->nomor_spp,
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp,
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah,
            'Kriteria CF' => $dokumen->kategori ?? '-',
            'Sub Kriteria' => $dokumen->jenis_dokumen ?? '-',
            'Item Sub Kriteria' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Kebun' => $dokumen->kebun ?? '-',
            'Dibayar Kepada' => $dokumen->dibayarKepadas->count() > 0
                ? $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ')
                : ($dokumen->dibayar_kepada ?? '-'),
            'No Berita Acara' => $dokumen->no_berita_acara ?? '-',
            'Tanggal Berita Acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-',
            'No SPK' => $dokumen->no_spk ?? '-',
            'Tanggal SPK' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-',
            'Tanggal Akhir SPK' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-',
            'No Mirror' => $dokumen->nomor_mirror ?? '-',
            'Current Handler' => ucfirst($dokumen->current_handler),
        ];

        foreach ($detailItems as $label => $value) {
            $html .= sprintf('
                <div class="detail-item">
                    <span class="detail-label">%s</span>
                    <span class="detail-value">%s</span>
                </div>',
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->escapeHtml($value), ENT_QUOTES, 'UTF-8')
            );
        }

        // No PO
        $poHtml = $dokumen->dokumenPos->count() > 0
            ? $this->escapeHtml($dokumen->dokumenPos->pluck('nomor_po')->join(', '))
            : '-';
        $html .= sprintf('
            <div class="detail-item">
                <span class="detail-label">No PO</span>
                <span class="detail-value">%s</span>
            </div>', htmlspecialchars($poHtml, ENT_QUOTES, 'UTF-8'));

        // No PR
        $prHtml = $dokumen->dokumenPrs->count() > 0
            ? $this->escapeHtml($dokumen->dokumenPrs->pluck('nomor_pr')->join(', '))
            : '-';
        $html .= sprintf('
            <div class="detail-item">
                <span class="detail-label">No PR</span>
                <span class="detail-value">%s</span>
            </div>', htmlspecialchars($prHtml, ENT_QUOTES, 'UTF-8'));

        // Status badge
        $statusBadge = '';
        if ($dokumen->status == 'selesai' || $dokumen->status == 'approved_ibub') {
            $statusBadge = '<span class="badge badge-status badge-selesai">' . ($dokumen->status == 'approved_ibub' ? 'Approved' : 'Selesai') . '</span>';
        } elseif ($dokumen->status == 'rejected_ibub') {
            $statusBadge = '<span class="badge badge-status badge-dikembalikan">Rejected</span>';
        } elseif ($dokumen->status == 'sent_to_ibub') {
            $statusBadge = '<span class="badge badge-status badge-proses">Menunggu Review</span>';
        } else {
            $statusBadge = '<span class="badge badge-status badge-proses">' . ucfirst($dokumen->status) . '</span>';
        }

        $html .= sprintf('
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">%s</span>
            </div>', $statusBadge);

        // Inbox rejection information - check dokumen_statuses
        $rejectedStatus = $dokumen->roleStatuses()
            ->where('status', 'rejected')
            ->whereIn('role_code', ['perpajakan', 'akutansi'])
            ->first();
        if ($rejectedStatus && $rejectedStatus->notes) {
            $html .= sprintf('
                <div class="detail-item" style="grid-column: 1 / -1; background: #fff5f5; border: 2px solid #f56565;">
                    <span class="detail-label" style="color: #c53030;">
                        <i class="fa-solid fa-times-circle me-1"></i>Ditolak dari Inbox
                    </span>
                    <span class="detail-value" style="color: #742a2a; font-weight: 600;">
                        %s
                    </span>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #fed7d7;">
                        <small style="color: #718096;">
                            <strong>Tanggal Penolakan:</strong> %s<br>
                            <strong>Ditolak oleh:</strong> %s
                        </small>
                    </div>
                </div>',
                htmlspecialchars($this->escapeHtml($rejectedStatus->notes ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($rejectedStatus->status_changed_at ? $rejectedStatus->status_changed_at->format('d/m/Y H:i') : '-', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->getRejectedByDisplayName($dokumen), ENT_QUOTES, 'UTF-8')
            );
        }

        // Dates
        $dates = [
            'Tanggal Dikirim ke Team Verifikasi' => $dokumen->getDataForRole('ibub')?->received_at ? $dokumen->getDataForRole('ibub')->received_at->format('d-m-Y H:i') : null,
            'Tanggal Diproses' => $dokumen->getDataForRole('ibub')?->processed_at ? $dokumen->getDataForRole('ibub')->processed_at->format('d-m-Y H:i') : null,
            'Tanggal Dikembalikan' => $dokumen->returned_to_ibua_at ? $dokumen->returned_to_ibua_at->format('d-m-Y H:i') : null,
        ];

        foreach ($dates as $label => $value) {
            if ($value) {
                $html .= sprintf('
                    <div class="detail-item">
                        <span class="detail-label">%s</span>
                        <span class="detail-value">%s</span>
                    </div>',
                    htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($this->escapeHtml($value), ENT_QUOTES, 'UTF-8')
                );
            }
        }

        // Deadline
        if ($dokumen->deadline_at) {
            $html .= sprintf('
                <div class="detail-item">
                    <span class="detail-label">Deadline</span>
                    <span class="detail-value">
                        <strong>%s</strong>
                        <br>
                        <small style="color: #666;">(%s hari dari pengiriman)</small>
                    </span>
                </div>',
                htmlspecialchars($dokumen->deadline_at->format('d M Y, H:i'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->escapeHtml($dokumen->deadline_days), ENT_QUOTES, 'UTF-8')
            );
        }

        if ($dokumen->deadline_note) {
            $html .= sprintf('
                <div class="detail-item">
                    <span class="detail-label">Catatan Deadline</span>
                    <span class="detail-value" style="font-style: italic; color: #666;">%s</span>
                </div>',
                htmlspecialchars($this->escapeHtml($dokumen->deadline_note), ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '</div>';
        return $html;
    }

    public function destroyDokumen($id)
    {
        // Implementation for deleting document
        return redirect()->route('documents.verifikasi.index')->with('success', 'Dokumen berhasil dihapus');
    }

    public function pengembalian(Request $request)
    {
        // IbuB sees documents that were returned to department (unified return page)
        // Juga menampilkan dokumen yang di-reject dari inbox (Perpajakan atau Akutansi)
        $query = \App\Models\Dokumen::with(['dokumenPos', 'dokumenPrs', 'activityLogs', 'dibayarKepadas'])
            ->where(function ($q) {
                // Dokumen yang dikembalikan dari department/bagian
                $q->where(function ($subQ) {
                    $subQ->where('current_handler', 'ibuB')
                        ->where('status', 'returned_to_department');
                })
                    // Dokumen yang di-reject dari inbox (Perpajakan atau Akutansi) dan dikembalikan ke IbuB
                    // Check dokumen_statuses table for rejected status
                    ->orWhere(function ($inboxRejectQ) {
                        $inboxRejectQ->where('current_handler', 'ibuB')
                            ->whereHas('roleStatuses', function ($statusQuery) {
                                $statusQuery->whereIn('role_code', ['perpajakan', 'akutansi'])
                                    ->where('status', 'rejected');
                            });
                    });
            })
            ->orderByDesc('department_returned_at');

        // Filter by department (hanya untuk dokumen yang dikembalikan dari department, bukan dari inbox)
        if ($request->has('department') && $request->department) {
            $query->where(function ($q) use ($request) {
                $q->where('target_department', $request->department)
                    ->orWhereHas('roleStatuses', function ($statusQuery) {
                        // Dokumen yang di-reject dari inbox tidak memiliki target_department
                        $statusQuery->whereIn('role_code', ['perpajakan', 'akutansi'])
                            ->where('status', 'rejected');
                    });
            });
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

        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Get statistics
        $totalReturnedToDept = \App\Models\Dokumen::where(function ($q) {
            // Dokumen yang dikembalikan dari department/bagian
            $q->where(function ($subQ) {
                $subQ->where('current_handler', 'ibuB')
                    ->where('status', 'returned_to_department');
            })
                // Dokumen yang di-reject dari inbox dan dikembalikan ke IbuB
                ->orWhere(function ($inboxRejectQ) {
                    $inboxRejectQ->where('current_handler', 'ibuB')
                        ->whereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->whereIn('role_code', ['perpajakan', 'akutansi'])
                                ->where('status', 'rejected');
                        });
                });
        })
            ->count();

        $totalByDept = [
            'perpajakan' => \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where(function ($q) {
                    $q->where('status', 'returned_to_department')
                        ->where('target_department', 'perpajakan')
                        ->orWhereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'perpajakan')
                                ->where('status', 'rejected');
                        });
                })
                ->count(),
            'akutansi' => \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where(function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->where('status', 'returned_to_department')
                            ->where('target_department', 'akutansi');
                    })
                        ->orWhereHas('roleStatuses', function ($statusQuery) {
                            $statusQuery->where('role_code', 'akutansi')
                                ->where('status', 'rejected');
                        });
                })
                ->count(),
            'pembayaran' => \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where('status', 'returned_to_department')
                ->where('target_department', 'pembayaran')
                ->count(),
        ];

        $departments = ['perpajakan', 'akutansi', 'pembayaran'];
        $selectedDepartment = $request->department;

        $data = array(
            "title" => "Daftar Pengembalian Dokumen",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
            'dokumens' => $dokumens,
            'totalReturnedToDept' => $totalReturnedToDept,
            'totalByDept' => $totalByDept,
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
        );
        return view('ibuB.dokumens.pengembalianKeBagianB', $data);
    }

    public function diagram()
    {
        $data = array(
            "title" => "Diagram Team Verifikasi",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDiagram' => 'Active',
        );
        return view('ibuB.diagramB', $data);
    }

    /**
     * Send document back to perpajakan after repair
     */
    public function sendBackToPerpajakan(Dokumen $dokumen, Request $request)
    {
        try {
            // Validate current handler
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
                ], 403);
            }

            // Validate that this is a returned document from perpajakan
            $perpajakanStatus = $dokumen->getStatusForRole('perpajakan');
            if (!$perpajakanStatus || $perpajakanStatus->status !== 'rejected') {
                // Also check if status is returned_to_department with target_department = perpajakan
                if ($dokumen->status !== 'returned_to_department' || $dokumen->target_department !== 'perpajakan') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dokumen ini bukan dokumen yang dikembalikan dari perpajakan.'
                    ], 403);
                }
            }

            \DB::beginTransaction();

            // Update document data with current values from ibuB
            $dokumen->update([
                'current_handler' => 'perpajakan',
                'status' => 'sent_to_perpajakan', // Langsung kirim ke perpajakan
                // Note: processed_at, sent_to_perpajakan_at, deadline_perpajakan_* columns removed - now in dokumen_role_data
                'perpajakan_return_data' => [
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'uraian_spp' => $dokumen->uraian_spp,
                    'nilai_rupiah' => $dokumen->nilai_rupiah,
                    'bulan' => $dokumen->bulan,
                    'tahun' => $dokumen->tahun,
                    'kategori' => $dokumen->kategori,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'dibayar_kepada' => $dokumen->dibayar_kepada,
                    'no_berita_acara' => $dokumen->no_berita_acara,
                    'tanggal_berita_acara' => $dokumen->tanggal_berita_acara,
                    'no_spk' => $dokumen->no_spk,
                    'tanggal_spk' => $dokumen->tanggal_spk,
                    'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk,
                    'keterangan' => $dokumen->keterangan,
                ],
                'updated_at' => now()
            ]);

            // Update role data for perpajakan - Reset deadline so it can be set again
            $dokumen->setDataForRole('perpajakan', [
                'received_at' => now(),
                'deadline_at' => null, // Reset deadline so document must set deadline again
                'deadline_days' => null,
                'deadline_note' => null,
                'processed_at' => null, // Reset processed_at so document is locked until deadline is set
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim kembali ke Team Perpajakan.'
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error sending document back to perpajakan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen ke perpajakan.'
            ], 500);
        }
    }

    /**
     * Send document to next handler (Perpajakan or Akutansi) via inbox
     */
    public function sendToNextHandler(Dokumen $dokumen, Request $request)
    {
        try {
            // Validate current handler
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
                ], 403);
            }

            // Validate next handler
            $request->validate([
                'next_handler' => 'required|in:perpajakan,akutansi,pembayaran'
            ]);

            \DB::beginTransaction();

            // Map handler to inbox role format
            $inboxRoleMap = [
                'perpajakan' => 'Perpajakan',
                'akutansi' => 'Akutansi',
                'pembayaran' => 'Pembayaran',
            ];

            $inboxRole = $inboxRoleMap[$request->next_handler] ?? $request->next_handler;

            // Jika dokumen adalah dokumen yang dikembalikan (returned_to_department),
            // bersihkan status pengembalian sebelum dikirim
            $isReturnedDocument = $dokumen->status === 'returned_to_department';
            
            if ($isReturnedDocument) {
                // Clear return-related fields before sending
                $dokumen->update([
                    'target_department' => null,
                    'department_returned_at' => null,
                    'department_return_reason' => null,
                    'returned_from_perpajakan_at' => null,
                    'returned_from_akutansi_at' => null,
                    'pengembalian_awaiting_fix' => false,
                    'returned_from_perpajakan_fixed_at' => now(), // Mark as fixed
                ]);

                \Log::info('Cleared return status before sending document', [
                    'document_id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'next_handler' => $request->next_handler
                ]);
            }

            // Simpan status original sebelum dikirim ke inbox
            $originalStatus = $dokumen->status;

            // Kirim ke inbox menggunakan sistem inbox yang sudah ada
            $dokumen->sendToInbox($inboxRole);
            
            // Reset deadline for the target handler AFTER sending to inbox
            // This is important for returned documents that need to set deadline again
            if ($request->next_handler === 'perpajakan') {
                $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
                if ($perpajakanRoleData) {
                    // Reset deadline for returned documents so they must set deadline again
                    $perpajakanRoleData->deadline_at = null;
                    $perpajakanRoleData->deadline_days = null;
                    $perpajakanRoleData->deadline_note = null;
                    $perpajakanRoleData->processed_at = null; // Reset processed_at to lock document until deadline is set
                    $perpajakanRoleData->save();
                    
                    \Log::info('Reset deadline for returned document sent to perpajakan', [
                        'document_id' => $dokumen->id,
                        'nomor_agenda' => $dokumen->nomor_agenda
                    ]);
                } else {
                    // Create role data if it doesn't exist, ensuring deadline is null
                    $dokumen->setDataForRole('perpajakan', [
                        'received_at' => now(),
                        'deadline_at' => null,
                        'deadline_days' => null,
                        'deadline_note' => null,
                        'processed_at' => null,
                    ]);
                }
            }
            
            // Refresh dokumen untuk mendapatkan status terbaru
            $dokumen->refresh();

            // Set processed_at untuk tracking di dokumen_role_data (ibuB)
            $roleData = $dokumen->getDataForRole('ibub');
            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->save();
            } else {
                // Create role data if it doesn't exist
                $dokumen->setDataForRole('ibub', [
                    'processed_at' => now(),
                    'received_at' => $dokumen->getDataForRole('ibub')?->received_at ?? now(),
                ]);
            }

            \DB::commit();

            // Map handler name for success message
            $nextHandlerNameMap = [
                'perpajakan' => 'Team Perpajakan',
                'akutansi' => 'Team Akutansi',
                'pembayaran' => 'Team Pembayaran',
            ];
            $nextHandlerName = $nextHandlerNameMap[$request->next_handler] ?? $request->next_handler;

            \Log::info("Document #{$dokumen->id} sent to inbox {$inboxRole} by ibuB");

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikirim ke inbox {$nextHandlerName} dan menunggu persetujuan."
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error sending document to next handler: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen.'
            ], 500);
        }
    }

    /**
     * OLD METHOD - DEPRECATED: Send document to next handler (Perpajakan or Akutansi) - DIRECT SEND
     * This method is kept for backward compatibility but should not be used
     * Use sendToNextHandler instead which uses inbox system
     */
    public function sendToNextHandlerDirect(Dokumen $dokumen, Request $request)
    {
        try {
            // Validate current handler
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
                ], 403);
            }

            // Validate next handler
            $request->validate([
                'next_handler' => 'required|in:perpajakan,akutansi'
            ]);

            \DB::beginTransaction();

            $updateData = [
                'current_handler' => $request->next_handler,
                'status' => 'sent_to_' . $request->next_handler,
            ];

            // Set specific timestamp based on destination
            // Note: Deadline will be set by the destination department (perpajakan/akutansi) themselves
            if ($request->next_handler === 'perpajakan') {
                $dokumen->setDataForRole('perpajakan', [
                    'received_at' => now(),
                    'deadline_at' => null, // Reset deadline so document will be locked until perpajakan sets deadline
                    'deadline_days' => null,
                    'deadline_note' => null,
                ]);
            } elseif ($request->next_handler === 'akutansi') {
                $dokumen->setDataForRole('akutansi', [
                    'received_at' => now(),
                    'deadline_at' => null, // Reset deadline so document will be locked until akutansi sets deadline
                    'deadline_days' => null,
                    'deadline_note' => null,
                ]);
            }

            $dokumen->update($updateData);

            \DB::commit();

            // Log activity: dokumen dikirim ke perpajakan/akutansi oleh Ibu Yuni
            try {
                \App\Helpers\ActivityLogHelper::logSent(
                    $dokumen->fresh(),
                    $request->next_handler,
                    'ibuB'
                );

                // Log activity: dokumen masuk/diterima di stage penerima
                \App\Helpers\ActivityLogHelper::logReceived(
                    $dokumen->fresh(),
                    $request->next_handler
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log document sent: ' . $logException->getMessage());
            }

            $nextHandlerName = $request->next_handler === 'perpajakan' ? 'Team Perpajakan' : 'Team Akutansi';

            \Log::info("Document #{$dokumen->id} sent to {$nextHandlerName} by ibuB");

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikirim ke {$nextHandlerName}. Dokumen akan terkunci hingga {$nextHandlerName} menetapkan deadline.",
                'next_handler' => $nextHandlerName
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in sendToNextHandler: ' . json_encode($e->validator->errors()->all()));
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error sending to next handler: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set deadline for document verification
     */
    public function setDeadline(Dokumen $dokumen, SetDeadlineRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            // Enhanced logging with user context
            // Check deadline from dokumen_role_data
            $roleData = $dokumen->getDataForRole('ibub');
            Log::info('=== SET DEADLINE REQUEST START ===', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'deadline_exists' => $roleData && $roleData->deadline_at ? true : false,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role,
                'request_data' => $validatedData
            ]);

            // Validasi status dokumen
            if ($dokumen->current_handler !== 'ibuB') {
                Log::warning('Deadline set failed - Invalid current handler', [
                    'document_id' => $dokumen->id,
                    'expected_handler' => 'ibuB',
                    'actual_handler' => $dokumen->current_handler,
                    'user_role' => Auth::user()?->role
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak valid untuk menetapkan deadline. Dokumen harus berada di IbuB.',
                    'debug_info' => [
                        'current_handler' => $dokumen->current_handler,
                        'expected_handler' => 'ibuB'
                    ]
                ], 403);
            }

            // Check deadline from dokumen_role_data instead of direct column
            $roleData = $dokumen->getDataForRole('ibub');
            if ($roleData && $roleData->deadline_at) {
                Log::warning('Deadline set failed - Deadline already exists', [
                    'document_id' => $dokumen->id,
                    'existing_deadline' => $roleData->deadline_at,
                    'user_role' => Auth::user()?->role
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen sudah memiliki deadline. Deadline tidak dapat diubah.',
                    'debug_info' => [
                        'existing_deadline' => $roleData->deadline_at
                    ]
                ], 403);
            }

            // Valid statuses untuk set deadline: dokumen yang baru di-approve dari inbox atau sedang diproses
            $validStatuses = ['sent_to_ibub', 'sedang diproses', 'approved_data_sudah_terkirim', 'menunggu_approved_pengiriman'];
            if (!in_array($dokumen->status, $validStatuses)) {
                Log::warning('Deadline set failed - Invalid document status', [
                    'document_id' => $dokumen->id,
                    'current_status' => $dokumen->status,
                    'valid_statuses' => $validStatuses,
                    'user_role' => Auth::user()?->role
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Status dokumen tidak valid. Status saat ini: {$dokumen->status}.",
                    'debug_info' => [
                        'current_status' => $dokumen->status,
                        'valid_statuses' => $validStatuses
                    ]
                ], 403);
            }

            // Prepare update data
            $deadlineDays = (int) $validatedData['deadline_days'];
            $deadlineNote = $validatedData['deadline_note'] ?? null;
            $deadlineAt = now()->addDays($deadlineDays);

            // Update using transaction
            DB::transaction(function () use ($dokumen, $deadlineDays, $deadlineNote, $deadlineAt) {
                // Update dokumen_role_data with deadline
                $dokumen->setDataForRole('ibub', [
                    'deadline_at' => $deadlineAt,
                    'deadline_days' => $deadlineDays,
                    'deadline_note' => $deadlineNote,
                    'received_at' => $dokumen->getDataForRole('ibub')?->received_at ?? now(),
                ]);

                // Update dokumen status
                $dokumen->update([
                    'status' => 'sedang diproses',
                ]);
            });

            // Refresh dokumen to get updated data
            $dokumen->refresh();
            $updatedRoleData = $dokumen->getDataForRole('ibub');

            // Log activity: deadline diatur oleh Ibu Yuni
            try {
                \App\Helpers\ActivityLogHelper::logDeadlineSet(
                    $dokumen->fresh(),
                    'ibuB',
                    [
                        'deadline_days' => $deadlineDays,
                        'deadline_at' => $updatedRoleData?->deadline_at?->format('Y-m-d H:i:s'),
                        'deadline_note' => $deadlineNote,
                    ]
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log deadline set: ' . $logException->getMessage());
            }

            Log::info('Deadline successfully set', [
                'document_id' => $dokumen->id,
                'deadline_days' => $deadlineDays,
                'deadline_at' => $updatedRoleData?->deadline_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Deadline berhasil ditetapkan ({$deadlineDays} hari). Dokumen sekarang terbuka untuk diproses.",
                'deadline' => $updatedRoleData?->deadline_at?->format('d-m-Y H:i'),
            ]);

        } catch (QueryException $e) {
            Log::error('Database error setting deadline', [
                'document_id' => $dokumen->id,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database saat menetapkan deadline.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error setting deadline', [
                'document_id' => $dokumen->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return document to specific department (NEW FUNCTION)
     */
    public function returnToDepartment(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if current_handler is ibuB
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengembalikan dokumen ini ke bagian.'
                ], 403);
            }

            // Validate input
            $request->validate([
                'target_department' => 'required|in:perpajakan,akutansi,pembayaran',
                'department_return_reason' => 'required|string|min:5|max:1000'
            ], [
                'target_department.required' => 'Bagian tujuan wajib dipilih.',
                'target_department.in' => 'Bagian tujuan tidak valid.',
                'department_return_reason.required' => 'Alasan pengembalian ke bagian wajib diisi.',
                'department_return_reason.min' => 'Alasan pengembalian minimal 5 karakter.',
                'department_return_reason.max' => 'Alasan pengembalian maksimal 1000 karakter.'
            ]);

            \DB::beginTransaction();

            // Update document with department return information
            $dokumen->update([
                'status' => 'returned_to_department',
                'current_handler' => 'ibuB', // Tetap di ibuB untuk tracking
                'target_department' => $request->target_department,
                'department_returned_at' => now(),
                'department_return_reason' => $request->department_return_reason,
            ]);

            \DB::commit();

            \Log::info('Document returned to department', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'target_department' => $request->target_department,
                'reason' => $request->department_return_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikembalikan ke bagian " . ucfirst($request->target_department) . ".",
                'target_department' => $request->target_department,
                'reason' => $request->department_return_reason
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error returning document to department: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan dokumen ke bagian.'
            ], 500);
        }
    }

    /**
     * Send document to target department
     */
    public function sendToTargetDepartment(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if document is in returned_to_department status
            if ($dokumen->status !== 'returned_to_department' || $dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak valid untuk dikirim ke bagian.'
                ], 400);
            }

            $request->validate([
                'deadline_days' => 'nullable|integer|min:1|max:30',
                'deadline_note' => 'nullable|string|max:500'
            ]);

            \DB::beginTransaction();

            $targetDepartment = $dokumen->target_department;

            $updateData = [
                'current_handler' => $targetDepartment,
                'status' => 'sent_to_' . $targetDepartment,
            ];

            // Set processed_at in dokumen_role_data for ibuB
            $roleData = $dokumen->getDataForRole('ibub');
            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->save();
            } else {
                $dokumen->setDataForRole('ibub', [
                    'processed_at' => now(),
                    'received_at' => now(),
                ]);
            }

            // Set received_at for target department
            $dokumen->setDataForRole($targetDepartment, [
                'received_at' => now(),
            ]);

            // Add deadline if provided (in dokumen_role_data for target department)
            if ($request->deadline_days) {
                $dokumen->setDataForRole($targetDepartment, [
                    'received_at' => now(),
                    'deadline_at' => now()->addDays((int) $request->deadline_days),
                    'deadline_days' => (int) $request->deadline_days,
                    'deadline_note' => $request->deadline_note,
                ]);
            }

            $dokumen->update($updateData);

            \DB::commit();

            $departmentName = ucfirst($targetDepartment);

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikirim ke bagian {$departmentName}.",
                'target_department' => $departmentName
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error sending to target department: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen ke bagian.'
            ], 500);
        }
    }

    /**
     * Get statistics for pengembalian ke bagian
     */
    public function getPengembalianKeBagianStats()
    {
        try {
            $totalReturnedToDept = \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where('status', 'returned_to_department')
                ->count();

            return response()->json([
                'success' => true,
                'total' => $totalReturnedToDept
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik.'
            ], 500);
        }
    }

    /**
     * Daftar Pengembalian Dokumen ke Bidang
     */
    public function pengembalianKeBidang(Request $request)
    {
        // Get documents with status = 'returned_to_bidang' and current_handler = 'ibuB'
        $query = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'returned_to_bidang')
            ->latest('bidang_returned_at');

        // Filter by specific bidang if provided
        if ($request->has('bidang') && $request->bidang) {
            $query->where('target_bidang', $request->bidang);
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
            'target_bidang',
            'bidang_returned_at',
            'bidang_return_reason',
            'created_at',
            'updated_at',
            'bulan',
            'tahun'
        ]);
        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Get statistics
        $totalReturned = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'returned_to_bidang')
            ->count();

        // Map bidang codes to names (hardcoded)
        $bidangList = [
            'DPM' => 'Divisi Produksi dan Manufaktur',
            'SKH' => 'Sub Kontrak Hutan',
            'SDM' => 'Sumber Daya Manusia',
            'TEP' => 'Teknik dan Perencanaan',
            'KPL' => 'Keuangan dan Pelaporan',
            'AKN' => 'Akuntansi',
            'TAN' => 'Tanaman dan Perkebunan',
            'PMO' => 'Project Management Office'
        ];

        $bidangStats = [];
        foreach ($bidangList as $kode => $nama) {
            $count = Dokumen::where('current_handler', 'ibuB')
                ->where('status', 'returned_to_bidang')
                ->where('target_bidang', $kode)
                ->count();

            $bidangStats[] = [
                'kode_bidang' => $kode,
                'nama_bidang' => $nama,
                'count' => $count
            ];
        }

        $data = array(
            "title" => "Daftar Pengembalian Dokumen ke Bidang",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuPengembalianKeBidang' => "Active",
            'dokumens' => $dokumens,
            'totalReturned' => $totalReturned,
            'bidangStats' => $bidangStats,
            'selectedBidang' => $request->bidang
        );

        return view('ibuB.dokumens.pengembalianKeBidangB', $data);
    }

    /**
     * Return document to bidang
     */
    public function returnToBidang(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if current_handler is ibuB and status is appropriate
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengembalikan dokumen ini ke bidang.'
                ], 403);
            }

            // Validate input
            $request->validate([
                'target_bidang' => 'required|string|in:DPM,SKH,SDM,TEP,KPL,AKN,TAN,PMO',
                'bidang_return_reason' => 'required|string|min:5|max:1000'
            ], [
                'target_bidang.required' => 'Bidang tujuan wajib dipilih.',
                'target_bidang.in' => 'Bidang tujuan tidak valid. Pilih salah satu: DPM, SKH, SDM, TEP, KPL, AKN, TAN, PMO.',
                'bidang_return_reason.required' => 'Alasan pengembalian ke bidang wajib diisi.',
                'bidang_return_reason.min' => 'Alasan pengembalian minimal 5 karakter.',
                'bidang_return_reason.max' => 'Alasan pengembalian maksimal 1000 karakter.'
            ]);

            \DB::beginTransaction();

            // Update document with bidang return information
            $dokumen->update([
                'status' => 'returned_to_bidang',
                'current_handler' => 'ibuB', // Tetap di ibuB untuk tracking
                'target_bidang' => $request->target_bidang,
                'bidang_returned_at' => now(),
                'bidang_return_reason' => $request->bidang_return_reason,
            ]);

            \DB::commit();

            \Log::info('Document returned to bidang', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'target_bidang' => $request->target_bidang,
                'reason' => $request->bidang_return_reason
            ]);

            // Map bidang codes to names
            $bidangNames = [
                'DPM' => 'Divisi Produksi dan Manufaktur',
                'SKH' => 'Sub Kontrak Hutan',
                'SDM' => 'Sumber Daya Manusia',
                'TEP' => 'Teknik dan Perencanaan',
                'KPL' => 'Keuangan dan Pelaporan',
                'AKN' => 'Akuntansi',
                'TAN' => 'Tanaman dan Perkebunan',
                'PMO' => 'PMO'
            ];

            $bidangName = $bidangNames[$request->target_bidang] ?? $request->target_bidang;

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dikembalikan ke bidang {$bidangName}.",
                'target_bidang' => $request->target_bidang,
                'reason' => $request->bidang_return_reason
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
     * Send document back to main list from bidang returns
     */
    public function sendBackToMainList(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if document is in returned_to_bidang status
            if ($dokumen->status !== 'returned_to_bidang') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen ini tidak dapat dikirim kembali ke daftar utama.'
                ], 403);
            }

            \DB::beginTransaction();

            // Update document to return to main list
            $dokumen->update([
                'status' => 'sent_to_ibub',
                'target_bidang' => null,
                'bidang_returned_at' => null,
                'bidang_return_reason' => null,
            ]);

            \DB::commit();

            \Log::info('Document sent back to main list from bidang return', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim kembali ke daftar utama.'
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error sending document back to main list: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen kembali ke daftar utama.'
            ], 500);
        }
    }

    /**
     * Return document to IbuA
     */
    public function returnToIbuA(Dokumen $dokumen, Request $request)
    {
        try {
            // Only allow if current_handler is ibuB
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengembalikan dokumen ini ke Ibu Tarapul.'
                ], 403);
            }

            // Validate input
            $request->validate([
                'alasan_pengembalian' => 'required|string|min:5|max:1000'
            ], [
                'alasan_pengembalian.required' => 'Alasan pengembalian wajib diisi.',
                'alasan_pengembalian.min' => 'Alasan pengembalian minimal 5 karakter.',
                'alasan_pengembalian.max' => 'Alasan pengembalian maksimal 1000 karakter.'
            ]);

            \DB::beginTransaction();

            // Update document with return to IbuA information
            $dokumen->update([
                'status' => 'returned_to_ibua',
                'current_handler' => 'ibuA',
                'alasan_pengembalian' => $request->alasan_pengembalian,
                'returned_to_ibua_at' => now(),
                // Clear bidang return fields if they exist
                'target_bidang' => null,
                'bidang_returned_at' => null,
                'bidang_return_reason' => null,
            ]);

            \DB::commit();

            \Log::info('Document returned to IbuA', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'reason' => $request->alasan_pengembalian
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikembalikan ke Ibu Tarapul.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error returning document to IbuA: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan dokumen ke Ibu Tarapul.'
            ], 500);
        }
    }

    /**
     * Change document status (approve/reject)
     */
    public function changeDocumentStatus(Dokumen $dokumen, Request $request)
    {
        try {
            // FIX: Validasi document ID untuk mencegah cross-interference
            if ($request->has('document_id') && $dokumen->id != $request->input('document_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID mismatch detected! Cross-document interference prevented.'
                ], 403);
            }

            // Only allow if current_handler is ibuB
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah status dokumen ini.'
                ], 403);
            }

            // Validate status
            $request->validate([
                'status' => 'required|in:approved,rejected',
                'document_id' => 'sometimes|integer|exists:dokumens,id'
            ], [
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid. Pilih approved atau rejected.',
                'document_id.exists' => 'Document ID tidak valid.'
            ]);

            $newStatus = $request->status === 'approved' ? 'approved_ibub' : 'rejected_ibub';

            \DB::beginTransaction();

            // Prepare milestone data for approved documents
            $updateData = [
                'status' => $newStatus,
                'updated_at' => now()
            ];
            
            // Set processed_at in dokumen_role_data for ibuB
            $roleData = $dokumen->getDataForRole('ibub');
            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->save();
            } else {
                $dokumen->setDataForRole('ibub', [
                    'processed_at' => now(),
                    'received_at' => now(),
                ]);
            }

            // Set milestone if approved
            if ($newStatus === 'approved_ibub') {
                $updateData['approved_by_ibub_at'] = now();
                $updateData['approved_by_ibub_by'] = 'ibuB';
            }

            // FIX: Atomic update spesifik per document ID untuk mencegah cross-interference
            $affectedRows = \DB::table('dokumens')
                ->where('id', $dokumen->id)
                ->where('current_handler', 'ibuB') // Double check
                ->update($updateData);

            // Jika tidak ada row yang terupdate, ada kemungkinan race condition
            if ($affectedRows === 0) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak dapat diperbarui. Kemungkinan telah diubah oleh user lain.'
                ], 409);
            }

            \DB::commit();

            \Log::info('Document status changed', [
                'document_id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'old_status' => $dokumen->getOriginal('status'),
                'new_status' => $newStatus,
                'changed_by' => 'ibuB'
            ]);

            $statusText = $newStatus === 'approved_ibub' ? 'disetujui (approved)' : 'ditolak (rejected)';

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil {$statusText}.",
                'new_status' => $newStatus,
                'status_text' => $newStatus === 'approved_ibub' ? 'Approved' : 'Rejected'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error changing document status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status dokumen.'
            ], 500);
        }
    }

    /**
     * Terima dokumen yang pending approval
     */
    public function acceptDocument(Request $request, Dokumen $dokumen)
    {
        try {
            // Validasi: harus pending approval untuk role ini
            if ($dokumen->status !== 'pending_approval_ibub') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak dalam status pending approval.'
                ], 400);
            }

            if ($dokumen->pending_approval_for !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen ini bukan untuk IbuB.'
                ], 403);
            }

            DB::beginTransaction();

            // Update dokumen: pindah ke status accepted
            $dokumen->update([
                'status' => 'sent_to_ibub',
                'current_handler' => 'ibuB',           // BARU PINDAH ke penerima
                'pending_approval_for' => null,
                'approval_responded_at' => now(),
                'approval_responded_by' => auth()->user()->username ?? 'ibuB',
                'approval_rejection_reason' => null,
            ]);

            $dokumen->refresh();
            DB::commit();

            // Broadcast event (opsional)
            try {
                broadcast(new \App\Events\DocumentApprovedInbox($dokumen, 'ibub'));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast acceptance: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diterima dan masuk ke sistem Team Verifikasi.'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error accepting document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menerima dokumen.'
            ], 500);
        }
    }

    /**
     * Tolak dokumen yang pending approval
     */
    public function rejectDocument(Request $request, Dokumen $dokumen)
    {
        try {
            // Validasi input
            $request->validate([
                'rejection_reason' => 'required|string|min:10',
            ], [
                'rejection_reason.required' => 'Alasan penolakan harus diisi.',
                'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
            ]);

            // Validasi: harus pending approval untuk role ini
            if ($dokumen->status !== 'pending_approval_ibub') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak dalam status pending approval.'
                ], 400);
            }

            if ($dokumen->pending_approval_for !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen ini bukan untuk IbuB.'
                ], 403);
            }

            DB::beginTransaction();

            // Update dokumen: kembalikan ke pengirim
            $dokumen->update([
                'status' => 'draft',                   // Kembali ke draft
                'current_handler' => 'ibuA',           // Kembali ke pengirim
                'pending_approval_for' => null,
                'approval_responded_at' => now(),
                'approval_responded_by' => auth()->user()->username ?? 'ibuB',
                'approval_rejection_reason' => $request->rejection_reason,
            ]);

            $dokumen->refresh();
            DB::commit();

            // Broadcast event (opsional)
            try {
                broadcast(new \App\Events\DocumentRejectedInbox($dokumen, $request->rejection_reason, 'ibub'));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast rejection: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditolak dan dikembalikan ke Ibu Tarapul.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error rejecting document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menolak dokumen.'
            ], 500);
        }
    }

    /**
     * Menampilkan halaman pending approval
     */
    public function pendingApproval(Request $request)
    {
        // Get dokumen yang pending approval untuk IbuB
        $dokumensPending = Dokumen::where('status', 'pending_approval_ibub')
            ->where('pending_approval_for', 'ibuB')
            ->latest('pending_approval_at')
            ->get();

        $data = [
            'title' => 'Dokumen Menunggu Persetujuan',
            'module' => 'ibuB',
            'menuDokumen' => 'active',
            'menuPendingApproval' => 'active',
            'dokumensPending' => $dokumensPending,
        ];

        return view('ibuB.dokumens.pendingApproval', $data);
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
     * Display the rekapan page for IbuB (same as IbuA but for viewing only)
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

        // Base query for documents created by Ibu Tarapul (Ibu Yuni can see all documents from Ibu Tarapul)
        $baseQuery = Dokumen::where('created_by', 'ibuA')
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

        // Get available years
        $availableYears = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('tanggal_masuk')
            ->selectRaw('DISTINCT YEAR(tanggal_masuk) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        // Get document count per bagian for the selected year
        $bagianCounts = [];
        foreach (self::BAGIAN_LIST as $bagianCode => $bagianName) {
            $countQuery = Dokumen::where('created_by', 'ibuA')
                ->whereYear('tanggal_masuk', $selectedYear)
                ->where('bagian', $bagianCode);
            $bagianCounts[$bagianCode] = $countQuery->count();
        }

        $data = [
            'title' => 'Analitik Dokumen',
            'module' => 'ibuB',
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

        return view('ibuB.dokumens.analytics', $data);
    }

    /**
     * Get statistics for rekapan documents (same as IbuA)
     */
    private function getRekapanStatistics(string $filterBagian = ''): array
    {
        $query = Dokumen::where('created_by', 'ibuA');

        if ($filterBagian && in_array($filterBagian, array_keys(self::BAGIAN_LIST))) {
            $query->where('bagian', $filterBagian);
        }

        $total = $query->count();

        $bagianStats = [];
        foreach (self::BAGIAN_LIST as $bagianCode => $bagianName) {
            $bagianQuery = Dokumen::where('created_by', 'ibuA')->where('bagian', $bagianCode);
            $bagianStats[$bagianCode] = [
                'name' => $bagianName,
                'total' => $bagianQuery->count()
            ];
        }

        return [
            'total_documents' => $total,
            'by_bagian' => $bagianStats,
            'by_status' => [
                'draft' => $query->where('status', 'draft')->count(),
                'sent_to_ibub' => $query->where('status', 'sent_to_ibub')->count(),
                'sedang diproses' => $query->where('status', 'sedang diproses')->count(),
                'selesai' => $query->where('status', 'selesai')->count(),
                'returned_to_ibua' => $query->where('status', 'returned_to_ibua')->count(),
            ]
        ];
    }

    /**
     * Display analytics page for Team Verifikasi (similar to Ibu Tarapul)
     */
    public function rekapanAnalytics(Request $request): View
    {
        // Get selected year and bagian from request
        $selectedYear = $request->get('year', date('Y'));
        $selectedBagian = $request->get('bagian', '');
        $selectedMonth = $request->get('month', null);

        // Validate year
        if (!is_numeric($selectedYear) || $selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = date('Y');
        }

        // Base query for documents created by Ibu Tarapul (Ibu Yuni can see all documents from Ibu Tarapul)
        $baseQuery = Dokumen::where('created_by', 'ibuA')
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

        // Get available years
        $availableYears = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('tanggal_masuk')
            ->selectRaw('DISTINCT YEAR(tanggal_masuk) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        $data = [
            'title' => 'Analitik Dokumen',
            'module' => 'ibuB',
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
        ];

        return view('ibuB.dokumens.analytics', $data);
    }

    /**
     * Helper method to safely escape HTML content with type casting
     */
    /**
     * Get rejected by display name from activity log
     */
    private function getRejectedByDisplayName($dokumen): string
    {
        // Check dokumen_statuses for rejected status
        $rejectedStatus = $dokumen->roleStatuses()
            ->where('status', 'rejected')
            ->whereIn('role_code', ['perpajakan', 'akutansi'])
            ->first();

        if ($rejectedStatus) {
            // Cari dari activity log
            $rejectLog = $dokumen->activityLogs()
                ->where('action', 'inbox_rejected')
                ->latest('action_at')
                ->first();

            if ($rejectLog) {
                $rejectedBy = $rejectLog->performed_by ?? $rejectLog->details['rejected_by'] ?? null;

                if ($rejectedBy) {
                    $nameMap = [
                        'IbuB' => 'Team Verifikasi',
                        'ibuB' => 'Team Verifikasi',
                        'Perpajakan' => 'Team Perpajakan',
                        'perpajakan' => 'Team Perpajakan',
                        'Akutansi' => 'Team Akutansi',
                        'akutansi' => 'Team Akutansi',
                    ];
                    return $nameMap[$rejectedBy] ?? $rejectedBy;
                }
            }

            // Fallback ke role_code dari dokumen_statuses
            $nameMap = [
                'perpajakan' => 'Team Perpajakan',
                'akutansi' => 'Team Akutansi',
            ];
            return $nameMap[$rejectedStatus->role_code] ?? ucfirst($rejectedStatus->role_code);
        }

        return '-';
    }

    private function escapeHtml(mixed $value): string
    {
        // Handle different data types safely
        if (is_null($value)) {
            return '-';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            // Format numbers nicely
            if (is_int($value)) {
                return (string) $value;
            }

            // Handle floating point numbers with proper formatting
            return number_format((float) $value, 2, '.', ',');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i:s');
        }

        // Handle arrays and objects by converting to string representation
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Fallback: cast to string
        return (string) $value;
    }

    /**
     * Get search suggestions when no results found
     */
    private function getSearchSuggestions($searchTerm, $year = null, $handler = 'ibuB'): array
    {
        $suggestions = [];

        // Get all unique values from relevant fields
        $baseQuery = Dokumen::where(function ($q) use ($handler) {
            $q->where('current_handler', $handler)
                ->orWhere(function ($subQ) {
                    $subQ->where('status', 'sedang_diproses')
                        ->where('current_handler', 'ibuB');
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
            'nomor_mirror',
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
                            ->where('current_handler', 'ibuB');
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
     * API endpoint untuk check dokumen yang di-reject dari inbox untuk IbuB
     */
    public function checkRejectedDocuments(Request $request)
    {
        try {
            $user = auth()->user();

            // Hanya allow IbuB
            if (!$user || !in_array(strtolower($user->role), ['ibub', 'ibu b', 'ibu yuni'])) {
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
            $checkFrom = $lastCheckTime ? \Carbon\Carbon::parse($lastCheckTime) : $checkFrom24Hours;

            // Gunakan waktu yang lebih lama untuk memastikan tidak ada yang terlewat
            if ($checkFrom->gt($checkFrom24Hours)) {
                $checkFrom = $checkFrom24Hours;
            }

            \Log::info('IbuB checkRejectedDocuments called', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'last_check_time' => $lastCheckTime,
                'check_from' => $checkFrom->toIso8601String(),
            ]);

            // Cari dokumen yang di-reject dari inbox Perpajakan atau Akutansi dalam 24 jam terakhir
            // Menggunakan dokumen_statuses table yang baru
            $rejectedDocuments = Dokumen::where('current_handler', 'ibuB')
                ->whereHas('roleStatuses', function($query) use ($checkFrom) {
                    $query->whereIn('role_code', ['perpajakan', 'akutansi'])
                          ->where('status', 'rejected')
                          ->where('status_changed_at', '>=', $checkFrom);
                })
                ->with(['roleStatuses' => function($query) {
                    $query->whereIn('role_code', ['perpajakan', 'akutansi'])
                          ->where('status', 'rejected')
                          ->latest('status_changed_at');
                }, 'activityLogs'])
                ->get()
                ->filter(function($doc) {
                    // Filter to only include documents with rejection status
                    return $doc->roleStatuses->where('status', 'rejected')->isNotEmpty();
                })
                ->sortByDesc(function($doc) {
                    $rejectedStatus = $doc->roleStatuses->where('status', 'rejected')->first();
                    return $rejectedStatus?->status_changed_at ?? now();
                })
                ->take(50)
                ->values();

            // Hitung total rejected
            $totalRejected = Dokumen::where('current_handler', 'ibuB')
                ->whereHas('roleStatuses', function($query) {
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
                        'url' => route('ibub.rejected.show', $doc->id),
                    ];
                }),
                'current_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking rejected documents for IbuB: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa dokumen yang ditolak'
            ], 500);
        }
    }

    /**
     * Menampilkan detail dokumen yang di-reject dari inbox Perpajakan/Akutansi untuk IbuB
     */
    public function showRejectedDocument(Dokumen $dokumen)
    {
        try {
            $user = auth()->user();

            // Hanya allow IbuB
            if (!$user || !in_array(strtolower($user->role), ['ibub', 'ibu b', 'ibu yuni'])) {
                abort(403, 'Unauthorized access');
            }

            // Validasi: dokumen harus di-reject dari inbox Perpajakan/Akutansi dan dikembalikan ke IbuB
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->whereIn('role_code', ['perpajakan', 'akutansi'])
                ->first();
            
            if (
                !$rejectedStatus ||
                strtolower($dokumen->current_handler) !== 'ibub'
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
                "module" => "IbuB",
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "dokumen" => $dokumen,
                "rejectedBy" => $rejectedBy,
                "rejectionReason" => $rejectedStatus->notes ?? '-',
                "rejectedAt" => $rejectedStatus->status_changed_at ?? null,
            ];

            return view('ibuB.rejected-detail', $data);

        } catch (\Exception $e) {
            \Log::error('Error showing rejected document for IbuB: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail dokumen yang ditolak');
        }
    }
}

