<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDokumenRequest;
use App\Http\Requests\UpdateDokumenRequest;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\Bagian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;
use Carbon\Carbon;
use App\Helpers\SearchHelper;
use App\Helpers\ActivityLogHelper;

class DokumenController extends Controller
{
    public function index(Request $request)
    {
        // Operator sees:
        // 1. Documents created by Operator
        // 2. Documents sent from Bagian where current_handler is 'operator'
        // 3. Documents with pending/approved status for operator role in dokumen_statuses
        // Order by nomor_agenda descending (numerically) - so new documents with lower numbers appear in correct position
        // Example: 2010, 2009, 2006 (new), 2005, 2004, 2003
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'activityLogs', 'roleStatuses'])
            ->where(function ($q) {
                // Documents created by Operator
                $q->whereRaw('LOWER(created_by) IN (?, ?, ?)', ['operator', 'Operator', 'operator'])
                    ->orWhere('created_by', 'operator')
                    // Documents sent from Bagian with current_handler = 'operator'
                    ->orWhere('current_handler', 'operator')
                    // Documents with status record for operator role
                    ->orWhereHas('roleStatuses', function ($subQ) {
                    $subQ->where('role_code', 'operator');
                });
            });

        // === Sort/Order handling ===
        if ($request->has('sort') || $request->has('order')) {
            $sortColumn = $request->get('sort', 'nomor_agenda');
            $sortOrder = $request->get('order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
            session(['operator_sort_column' => $sortColumn, 'operator_sort_order' => $sortOrder]);
        } else {
            $sortColumn = session('operator_sort_column', 'nomor_agenda');
            $sortOrder = session('operator_sort_order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        }

        // Apply sorting based on column
        if ($sortColumn === 'nomor_agenda') {
            $query->orderByRaw("CASE 
                WHEN nomor_agenda REGEXP '^[0-9]+(_[0-9]+)?$' THEN 
                    CAST(SUBSTRING_INDEX(nomor_agenda, '_', 1) AS UNSIGNED)
                WHEN nomor_agenda REGEXP '^[0-9]+' THEN 
                    CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END {$sortOrder}")
                ->orderBy('nomor_agenda', $sortOrder);
        } else {
            // Allow sorting by any valid column
            $allowedColumns = ['nomor_spp', 'tanggal_masuk', 'nilai_rupiah', 'tanggal_spp', 'uraian_spp', 'kategori', 'kebun', 'jenis_dokumen', 'jenis_sub_pekerjaan', 'jenis_pembayaran', 'nama_pengirim', 'dibayar_kepada', 'no_berita_acara', 'tanggal_berita_acara', 'no_spk', 'tanggal_spk', 'tanggal_berakhir_spk', 'status'];
            if (in_array($sortColumn, $allowedColumns)) {
                $query->orderBy($sortColumn, $sortOrder);
            }
            // Always add secondary sort by nomor_agenda DESC
            $query->orderByRaw("CASE 
                WHEN nomor_agenda REGEXP '^[0-9]+(_[0-9]+)?$' THEN 
                    CAST(SUBSTRING_INDEX(nomor_agenda, '_', 1) AS UNSIGNED)
                WHEN nomor_agenda REGEXP '^[0-9]+' THEN 
                    CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC");
        }

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

        // Filter by status using new dokumen_statuses table
        if ($request->has('status_filter') && $request->status_filter) {
            $statusFilter = $request->status_filter;

            switch ($statusFilter) {
                case 'belum_dikirim':
                    // Dokumen yang belum dikirim - no status record for Team Verifikasi exists
                    $query->whereDoesntHave('roleStatuses', function ($q) {
                        $q->where('role_code', 'team_verifikasi');
                    })->where('status', 'draft');
                    break;

                case 'menunggu_approval':
                    // Dokumen yang menunggu approval dari Reviewer (Team Verifikasi)
                    $query->whereHas('roleStatuses', function ($q) {
                        $q->where('role_code', 'team_verifikasi')
                            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING);
                    });
                    break;

                case 'terkirim':
                    // Dokumen yang sudah di-approve oleh Reviewer atau diteruskan ke department lain
                    $query->where(function ($q) {
                        // Approved by Team Verifikasi
                        $q->whereHas('roleStatuses', function ($q2) {
                            $q2->where('role_code', 'team_verifikasi')
                                ->where('status', \App\Models\DokumenStatus::STATUS_APPROVED);
                        })
                            // OR has status record for other departments (sent to them)
                            ->orWhereHas('roleStatuses', function ($q3) {
                                $q3->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran']);
                            });
                    });
                    break;
            }
        }

        $perPage = $request->get('per_page', session('operator_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['operator_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->has('search') && !empty($request->search) && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year);
        }

        // Available columns for customization
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'nilai_rupiah' => 'Nilai Rupiah',
            'status' => 'Status',
            'tanggal_spp' => 'Tanggal SPP',
            'uraian_spp' => 'Uraian SPP',
            'kategori' => 'Kriteria CF',
            'kebun' => 'Kebun',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
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
        ];

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Remove deprecated columns if they exist
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'nomor_mirror' && $col !== 'keterangan';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from session if available
            $selectedColumns = session('dokumens_table_columns', [
                'nomor_agenda',
                'nomor_spp',
                'tanggal_masuk',
                'nilai_rupiah',
                'status'
            ]);

            // Remove deprecated columns if they exist in session
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'nomor_mirror' && $col !== 'keterangan';
            });
            $selectedColumns = array_values($selectedColumns); // Re-index array

            // Update session with cleaned columns
            session(['dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Dokumen",
            "module" => "Operator",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "suggestions" => $suggestions ?? [],
            "availableColumns" => $availableColumns,
            "selectedColumns" => $selectedColumns,
            "sortColumn" => $sortColumn,
            "sortOrder" => $sortOrder,
        );

        return view('operator.dokumens.daftarDokumen', $data);
    }

    public function create()
    {
        // Ambil data dari database cash_bank_new
        // Tambahkan try-catch untuk menangani error koneksi database
        $isDropdownAvailable = false;
        try {
            $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = SubKriteria::all();
            $itemSubKriteria = ItemSubKriteria::all();
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
        $isJenisPembayaranAvailable = false;
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            $isJenisPembayaranAvailable = $jenisPembayaranList->count() > 0;
            \Log::info('Jenis Pembayaran fetched (create): ' . $jenisPembayaranList->count() . ' records');
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran data (create): ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            // Fallback: gunakan collection kosong jika error
            $jenisPembayaranList = collect([]);
            $isJenisPembayaranAvailable = false;
        }

        // Ambil data bagian dari database
        $bagianList = collect([]);
        try {
            $bagianList = Bagian::active()->ordered()->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching bagian data (create): ' . $e->getMessage());
            $bagianList = collect([]);
        }

        $data = array(
            "title" => "Tambah Dokumen",
            "module" => "Operator",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "",
            "menuTambahDokumen" => "active",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "kategoriKriteria" => $kategoriKriteria,
            "subKriteria" => $subKriteria,
            "itemSubKriteria" => $itemSubKriteria,
            "isDropdownAvailable" => $isDropdownAvailable,
            "jenisPembayaranList" => $jenisPembayaranList,
            "isJenisPembayaranAvailable" => $isJenisPembayaranAvailable,
            "bagianList" => $bagianList,
        );
        return view('operator.dokumens.tambahDokumen', $data);
    }

    /**
     * Get document detail for AJAX request for Operator
     */
    public function getDocumentDetailForOperator(Dokumen $dokumen)
    {
        // Allow if:
        // 1. Created by Operator
        // 2. Current handler is Operator
        // 3. Document has a status record for operator role (meaning it passed through operator)
        $createdByLower = strtolower($dokumen->created_by ?? '');
        $currentHandlerLower = strtolower($dokumen->current_handler ?? '');

        $isOperatorDocument = $createdByLower === 'operator'
            || $currentHandlerLower === 'operator'
            || $dokumen->roleStatuses()->where('role_code', 'operator')->exists();

        if (!$isOperatorDocument) {
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return HTML partial for detail view
        $html = view('operator.dokumens.partials.document_detail', compact('dokumen'))->render();

        return response($html);
    }

    /**
     * Get document detail for modal popup (JSON format)
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow if:
        // 1. Created by Operator
        // 2. Current handler is Operator
        // 3. Document has a status record for operator role (meaning it passed through operator)
        $createdByLower = strtolower($dokumen->created_by ?? '');
        $currentHandlerLower = strtolower($dokumen->current_handler ?? '');

        $isOperatorDocument = $createdByLower === 'operator'
            || $currentHandlerLower === 'operator'
            || $dokumen->roleStatuses()->where('role_code', 'operator')->exists();

        if (!$isOperatorDocument) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }


        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return JSON for modal view
        return response()->json([
            'success' => true,
            'dokumen' => [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'tanggal_spp' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : null,
                'bulan' => $dokumen->bulan,
                'tahun' => $dokumen->tahun,
                'tanggal_masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('Y-m-d H:i:s') : null,
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
                'bagian' => $dokumen->bagian,
                'nama_pengirim' => $dokumen->nama_pengirim,
                'no_spk' => $dokumen->no_spk,
                'tanggal_spk' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : null,
                'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('Y-m-d') : null,
                'nomor_miro' => $dokumen->nomor_miro_display,
                'tanggal_miro' => $dokumen->tanggal_miro ? $dokumen->tanggal_miro->format('Y-m-d') : null,
                'no_berita_acara' => $dokumen->no_berita_acara,
                'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : null,
                'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : null,
                'NO_PO' => $dokumen->NO_PO,
                'dokumen_pos' => $dokumen->dokumenPos ? $dokumen->dokumenPos->map(function ($po) {
                    return ['nomor_po' => $po->nomor_po ?? ''];
                })->values() : [],
                'dokumen_prs' => $dokumen->dokumenPrs ? $dokumen->dokumenPrs->map(function ($pr) {
                    return ['nomor_pr' => $pr->nomor_pr ?? ''];
                })->values() : [],
            ]
        ]);
    }

    /**
     * Get document progress for Operator
     */
    public function getDocumentProgressForOperator(Dokumen $dokumen)
    {
        // Allow if:
        // 1. Created by Operator
        // 2. Current handler is Operator
        // 3. Document has a status record for operator role (meaning it passed through operator)
        $createdByLower = strtolower($dokumen->created_by ?? '');
        $currentHandlerLower = strtolower($dokumen->current_handler ?? '');

        $isOperatorDocument = $createdByLower === 'operator'
            || $currentHandlerLower === 'operator'
            || $dokumen->roleStatuses()->where('role_code', 'operator')->exists();

        if (!$isOperatorDocument) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Calculate progress based on document status and timeline
        $progress = $this->calculateProgress($dokumen);

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Calculate document progress percentage and timeline
     */
    private function calculateProgress(Dokumen $dokumen)
    {
        $timeline = [];
        $totalPercentage = 0;

        // Step 1: Document Creation
        $timeline[] = [
            'step' => 'Dokumen DOperatort',
            'status' => 'completed',
            'time' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '',
            'description' => 'Dokumen berhasil dOperatort oleh Ibu Tarapul',
            'percentage' => 20
        ];

        // Step 2: Document Sent to Team Verifikasi
        if ($dokumen->status === 'draft') {
            $timeline[] = [
                'step' => 'Menunggu Pengiriman',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang disiapkan untuk dikirim ke Ibu Yuni',
                'percentage' => 0
            ];
            $totalPercentage = 20;
        } elseif ($dokumen->status === 'sent_to_team_verifikasi') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_team_verifikasi_at ? $dokumen->sent_to_team_verifikasi_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            // Step 3: Processing by Ibu Yuni
            $timeline[] = [
                'step' => 'Sedang Diproses Ibu Yuni',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang ditinjau dan diproses oleh Ibu Yuni',
                'percentage' => 0
            ];
            $totalPercentage = 50;
        } elseif ($dokumen->status === 'returned_to_Operator') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_team_verifikasi_at ? $dokumen->sent_to_team_verifikasi_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            $timeline[] = [
                'step' => 'Dikembalikan ke Ibu Tarapul',
                'status' => 'completed',
                'time' => $dokumen->returned_to_Operator_at ? $dokumen->returned_to_Operator_at->format('d M Y H:i') : '',
                'description' => $dokumen->alasan_pengembalian ? 'Dikembalikan: ' . $dokumen->alasan_pengembalian : 'Dokumen dikembalikan untuk perbaikan',
                'percentage' => 40
            ];

            // Step 4: Need Revision
            $timeline[] = [
                'step' => 'Menunggu Perbaikan',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen perlu diperbaiki sesuai masukan dari Ibu Yuni',
                'percentage' => 0
            ];
            $totalPercentage = 60;
        } elseif ($dokumen->status === 'sedang diproses') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_team_verifikasi_at ? $dokumen->sent_to_team_verifikasi_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            // Step 3: Processing by Ibu Yuni
            $timeline[] = [
                'step' => 'Sedang Diproses Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah selesai diproses oleh Ibu Yuni',
                'percentage' => 40
            ];

            // Step 4: Final Processing
            $timeline[] = [
                'step' => 'Proses Selanjutnya',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang dalam proses selanjutnya (Pembayaran/Akutansi/Perpajakan)',
                'percentage' => 0
            ];
            $totalPercentage = 70;
        } elseif ($dokumen->status === 'selesai') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_team_verifikasi_at ? $dokumen->sent_to_team_verifikasi_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            $timeline[] = [
                'step' => 'Sedang Diproses Team Verifikasi',
                'status' => 'completed',
                'time' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah selesai diproses oleh Team Verifikasi',
                'percentage' => 40
            ];

            $timeline[] = [
                'step' => 'Proses Selanjutnya',
                'status' => 'completed',
                'time' => '',
                'description' => 'Dokumen telah melewati semua tahap proses',
                'percentage' => 30
            ];
            $totalPercentage = 100;
        }

        // Add future steps for visualization
        if ($dokumen->status !== 'selesai') {
            $timeline[] = [
                'step' => 'Proses Selanjutnya',
                'status' => 'pending',
                'time' => '',
                'description' => 'Dokumen akan masuk ke tahap pembayaran/akutansi/perpajakan',
                'percentage' => 0
            ];

            $timeline[] = [
                'step' => 'Selesai',
                'status' => 'pending',
                'time' => '',
                'description' => 'Dokumen telah selesai semua proses',
                'percentage' => 0
            ];
        }

        return [
            'percentage' => $totalPercentage,
            'timeline' => $timeline,
            'current_status' => $dokumen->status,
            'current_handler' => $dokumen->current_handler
        ];
    }

    public function store(StoreDokumenRequest $request)
    {

        try {
            DB::beginTransaction();

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text (nullable)
            $nilaiRupiah = null;
            if ($request->filled('nilai_rupiah')) {
                $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
                if (!empty($nilaiRupiah) && $nilaiRupiah > 0) {
                    $nilaiRupiah = (float) $nilaiRupiah;
                } else {
                    $nilaiRupiah = null;
                }
            }

            // Extract bulan dan tahun from computer timestamp (Carbon::now())
            $now = Carbon::now();
            $bulanIndonesia = [
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
            $bulan = $bulanIndonesia[$now->month];
            $tahun = $now->year;

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;

            try {
                if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                    $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
                }

                if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                    $subKriteria = SubKriteria::find($request->sub_kriteria);
                }

                if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                    $itemSubKriteria = ItemSubKriteria::find($request->item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cash_bank data for store: ' . $e->getMessage());
                // Continue dengan null values, akan menggunakan fallback ke request->kategori/jenis_dokumen/jenis_sub_pekerjaan
            }

            // Create dokumen
            $dokumen = Dokumen::create([
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal_masuk' => $now, // Always use current timestamp
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                // Simpan nama dari ID untuk backward compatibility
                'kategori' => $kategoriKriteria ? $kategoriKriteria->nama_kriteria : ($request->kategori ?? null),
                'jenis_dokumen' => $subKriteria ? $subKriteria->nama_sub_kriteria : ($request->jenis_dokumen ?? null),
                'jenis_sub_pekerjaan' => $itemSubKriteria ? $itemSubKriteria->nama_item_sub_kriteria : ($request->jenis_sub_pekerjaan ?? null),
                'jenis_pembayaran' => $request->jenis_pembayaran,
                'kebun' => $request->kebun,
                'bagian' => $request->bagian,
                'nama_pengirim' => $request->nama_pengirim,
                // Remove old dibayar_kepada field, will handle separately
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'status' => 'draft',
                'keterangan' => null,
                'created_by' => 'operator',
                'current_handler' => 'operator',
            ]);

            // Save PO numbers
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

            // Save PR numbers
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

            // Save dibayar_kepada (multiple recipients)
            if ($request->has('dibayar_kepada')) {
                foreach ($request->dibayar_kepada as $penerima) {
                    if (!empty(trim($penerima))) {
                        DibayarKepada::create([
                            'dokumen_id' => $dokumen->id,
                            'nama_penerima' => trim($penerima),
                        ]);
                    }
                }
            }

            DB::commit();

            // Log activity: dokumen dOperatort
            try {
                ActivityLogHelper::logCreated($dokumen);
            } catch (\Exception $logException) {
                \Log::error('Failed to log document creation: ' . $logException->getMessage());
            }

            $successMessage = 'Dokumen berhasil ditambahkan.';
            if ($dokumen->nomor_agenda) {
                $successMessage .= ' Nomor agenda: ' . $dokumen->nomor_agenda;
            }

            return redirect()->route('documents.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Error creating dokumen: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token', 'password']),
            ]);

            // Provide more detailed error message
            $errorMessage = 'Terjadi kesalahan saat menyimpan dokumen.';
            if (str_contains($e->getMessage(), 'SQLSTATE') || str_contains($e->getMessage(), 'Column')) {
                $errorMessage .= ' Pastikan semua field yang diperlukan sudah diisi dengan benar.';
            } else {
                $errorMessage .= ' Silakan coba lagi atau hubungi administrator.';
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function edit(Dokumen $dokumen)
    {
        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Ambil data dari database cash_bank_new untuk dropdown baru
        $isDropdownAvailable = false;
        try {
            $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = SubKriteria::all();
            $itemSubKriteria = ItemSubKriteria::all();
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
        $isJenisPembayaranAvailable = false;
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            $isJenisPembayaranAvailable = $jenisPembayaranList->count() > 0;
            \Log::info('Jenis Pembayaran fetched (edit): ' . $jenisPembayaranList->count() . ' records');
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran data (edit): ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            // Fallback: gunakan collection kosong jika error
            $jenisPembayaranList = collect([]);
            $isJenisPembayaranAvailable = false;
        }

        // Ambil data bagian dari database
        $bagianList = collect([]);
        try {
            $bagianList = Bagian::active()->ordered()->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching bagian data (edit): ' . $e->getMessage());
            $bagianList = collect([]);
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
            "module" => "Operator",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumen" => $dokumen,
            'kategoriKriteria' => $kategoriKriteria ?? collect([]),
            'subKriteria' => $subKriteria ?? collect([]),
            'itemSubKriteria' => $itemSubKriteria ?? collect([]),
            'selectedKriteriaCfId' => $selectedKriteriaCfId ?? null,
            'selectedSubKriteriaId' => $selectedSubKriteriaId ?? null,
            'selectedItemSubKriteriaId' => $selectedItemSubKriteriaId ?? null,
            'isDropdownAvailable' => $isDropdownAvailable,
            'jenisPembayaranList' => $jenisPembayaranList,
            'isJenisPembayaranAvailable' => $isJenisPembayaranAvailable,
            'bagianList' => $bagianList,
        );

        return view('operator.dokumens.editDokumen', $data);
    }

    public function update(UpdateDokumenRequest $request, Dokumen $dokumen)
    {
        // Validate that user can edit this document
        // Allow editing if:
        // 1. Document is created by Operator and currently with Operator
        // 2. Document is rejected (can be edited to fix issues)
        // 3. Document is in draft or returned status

        $currentHandler = strtolower($dokumen->current_handler ?? '');
        $createdBy = strtolower($dokumen->created_by ?? '');
        $status = strtolower($dokumen->status ?? '');

        // Check if document is created by Operator (case-insensitive, all valid aliases)
        $Operatorliases = ['operator', 'Operator', 'Operator', 'tarapul', 'operator'];
        $createdByOperator = in_array($createdBy, $Operatorliases);

        // Check if document is currently with Operator (case-insensitive)
        $currentHandlerOperator = in_array($currentHandler, $Operatorliases);

        // Check if document is rejected
        $isRejected = false;
        $teamVerifikasiStatus = $dokumen->getStatusForRole('team_verifikasi');
        if ($teamVerifikasiStatus && strtolower($teamVerifikasiStatus->status ?? '') === 'rejected') {
            $isRejected = true;
        } else {
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->whereIn('role_code', ['team_verifikasi', 'team_verifikasi'])
                ->first();
            $isRejected = $rejectedStatus !== null;
        }

        // Check if status allows editing
        // Include sent_to_team_verifikasi for Bagian documents that were resent
        $allowedStatuses = ['draft', 'returned_to_Operator', 'belum_dikirim', 'belum dikirim', 'menunggu_approval_keuangan', 'sent_to_team_verifikasi'];
        $isAllowedStatus = in_array($status, $allowedStatuses);

        // Additional check: document from Bagian with current_handler = operator can be edited
        $isFromBagian = $currentHandlerOperator && !$createdByOperator;
        if ($isFromBagian) {
            $isAllowedStatus = true;
        }

        // Allow editing if:
        // 1. Document is draft/new and current handler is Operator
        // 2. Document is rejected AND current handler is Operator (can always be edited)
        // 3. Document has allowed status AND current handler is Operator
        // Note: Skip createdBy check for draft documents to allow editing new documents
        if (!$currentHandlerOperator) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        if (!$isRejected && !$isAllowedStatus) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Dokumen tidak dapat diedit. Status dokumen harus draft, returned, atau ditolak.');
        }

        try {
            DB::beginTransaction();

            // Store old values for logging
            $oldValues = [
                'nomor_agenda' => $dokumen->nomor_agenda,
                'bulan' => $dokumen->bulan,
                'tahun' => $dokumen->tahun,
                'nomor_spp' => $dokumen->nomor_spp,
                'tanggal_spp' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : null,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'kategori' => $dokumen->kategori,
                'jenis_dokumen' => $dokumen->jenis_dokumen,
                'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan,
                'jenis_pembayaran' => $dokumen->jenis_pembayaran,
                'kebun' => $dokumen->kebun,
                'bagian' => $dokumen->bagian,
                'nama_pengirim' => $dokumen->nama_pengirim,
                'no_berita_acara' => $dokumen->no_berita_acara,
                'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : null,
                'no_spk' => $dokumen->no_spk,
                'tanggal_spk' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : null,
                'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('Y-m-d') : null,
            ];

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text (nullable)
            $nilaiRupiah = null;
            if ($request->filled('nilai_rupiah')) {
                $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
                if (!empty($nilaiRupiah) && $nilaiRupiah > 0) {
                    $nilaiRupiah = (float) $nilaiRupiah;
                } else {
                    $nilaiRupiah = null;
                }
            }

            // Extract bulan dan tahun dari tanggal SPP untuk update (nullable)
            $newBulan = null;
            $newTahun = null;
            if ($request->filled('tanggal_spp')) {
                $tanggalSpp = Carbon::parse($request->tanggal_spp);
                $bulanIndonesia = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'May',
                    6 => 'Juni',
                    7 => 'July',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
                $newBulan = $bulanIndonesia[$tanggalSpp->month];
                $newTahun = $tanggalSpp->year;
            }

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;

            try {
                if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                    $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
                }

                if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                    $subKriteria = SubKriteria::find($request->sub_kriteria);
                }

                if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                    $itemSubKriteria = ItemSubKriteria::find($request->item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cash_bank data for update: ' . $e->getMessage());
                // Continue dengan null values, akan menggunakan fallback ke request->kategori/jenis_dokumen/jenis_sub_pekerjaan
            }

            // Update dokumen
            // IMPORTANT: Status is NOT updated here - it only changes via workflow (send, return, etc)
            // BUT: For rejected documents, we need to ensure status remains 'returned_to_Operator' so they can be resent
            // Only update fields that are filled in the request, otherwise keep existing values
            // Use filled() to check if field is present and not empty (handles empty string vs null)
            $updateData = [
                'nomor_agenda' => $request->filled('nomor_agenda') ? $request->nomor_agenda : $dokumen->nomor_agenda,
                'bulan' => $newBulan ?? $dokumen->bulan,
                'tahun' => $newTahun ?? $dokumen->tahun,
                'tanggal_masuk' => $dokumen->tanggal_masuk, // Keep original creation timestamp
                'nomor_spp' => $request->filled('nomor_spp') ? $request->nomor_spp : $dokumen->nomor_spp,
                'tanggal_spp' => $request->filled('tanggal_spp') ? $request->tanggal_spp : $dokumen->tanggal_spp,
                'uraian_spp' => $request->filled('uraian_spp') ? $request->uraian_spp : $dokumen->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah ?? $dokumen->nilai_rupiah,
                // Simpan nama dari ID untuk backward compatibility
                'kategori' => $kategoriKriteria ? $kategoriKriteria->nama_kriteria : ($request->filled('kategori') ? $request->kategori : $dokumen->kategori),
                'jenis_dokumen' => $subKriteria ? $subKriteria->nama_sub_kriteria : ($request->filled('jenis_dokumen') ? $request->jenis_dokumen : $dokumen->jenis_dokumen),
                'jenis_sub_pekerjaan' => $itemSubKriteria ? $itemSubKriteria->nama_item_sub_kriteria : ($request->filled('jenis_sub_pekerjaan') ? $request->jenis_sub_pekerjaan : $dokumen->jenis_sub_pekerjaan),
                'jenis_pembayaran' => $request->filled('jenis_pembayaran') ? $request->jenis_pembayaran : $dokumen->jenis_pembayaran,
                'kebun' => $request->filled('kebun') ? $request->kebun : $dokumen->kebun,
                'bagian' => $request->filled('bagian') ? $request->bagian : $dokumen->bagian,
                'nama_pengirim' => $request->filled('nama_pengirim') ? $request->nama_pengirim : $dokumen->nama_pengirim,
                // Remove old dibayar_kepada field, will handle separately
                'no_berita_acara' => $request->filled('no_berita_acara') ? $request->no_berita_acara : $dokumen->no_berita_acara,
                'tanggal_berita_acara' => $request->filled('tanggal_berita_acara') ? $request->tanggal_berita_acara : $dokumen->tanggal_berita_acara,
                'no_spk' => $request->filled('no_spk') ? $request->no_spk : $dokumen->no_spk,
                'tanggal_spk' => $request->filled('tanggal_spk') ? $request->tanggal_spk : $dokumen->tanggal_spk,
                'tanggal_berakhir_spk' => $request->filled('tanggal_berakhir_spk') ? $request->tanggal_berakhir_spk : $dokumen->tanggal_berakhir_spk,
                // 'status' => REMOVED - status should only change through workflow, not manual edit
                // 'keterangan' => REMOVED - not used anymore
            ];

            // For rejected documents, ensure status remains 'returned_to_Operator' so they can be resent
            // Don't change status if it's already 'returned_to_Operator' (for rejected documents)
            if ($isRejected && $dokumen->status !== 'returned_to_Operator') {
                // Keep current status, don't change it
                // Status will remain as is, but document can still be edited
            }

            $dokumen->update($updateData);
            $dokumen->refresh();

            // Log changes for all edited fields
            $fieldsToLog = [
                'nomor_agenda' => 'Nomor Agenda',
                'bulan' => 'Bulan',
                'tahun' => 'Tahun',
                'nomor_spp' => 'Nomor SPP',
                'tanggal_spp' => 'Tanggal SPP',
                'uraian_spp' => 'Uraian SPP',
                'nilai_rupiah' => 'Nilai Rupiah',
                'kategori' => 'Kriteria CF',
                'jenis_dokumen' => 'Sub Kriteria',
                'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
                'jenis_pembayaran' => 'Jenis Pembayaran',
                'kebun' => 'Kebun',
                'bagian' => 'Bagian',
                'nama_pengirim' => 'Nama Pengirim',
                'no_berita_acara' => 'Nomor Berita Acara',
                'tanggal_berita_acara' => 'Tanggal Berita Acara',
                'no_spk' => 'Nomor SPK',
                'tanggal_spk' => 'Tanggal SPK',
                'tanggal_berakhir_spk' => 'Tanggal Berakhir SPK',
            ];

            foreach ($fieldsToLog as $field => $fieldName) {
                $oldValueRaw = $oldValues[$field];
                $newValueRaw = $dokumen->$field;
                $oldValue = null;
                $newValue = null;

                if ($field === 'tanggal_spp' || $field === 'tanggal_berita_acara' || $field === 'tanggal_spk' || $field === 'tanggal_berakhir_spk') {
                    $oldValue = $oldValueRaw;
                    $newValue = $newValueRaw ? $dokumen->$field->format('Y-m-d') : null;
                } elseif ($field === 'nilai_rupiah') {
                    // Compare numeric values first to ensure accuracy
                    $oldNumeric = $oldValueRaw ? (float) $oldValueRaw : 0;
                    $newNumeric = $newValueRaw ? (float) $newValueRaw : 0;

                    // Format for display in log
                    $oldValue = $oldValueRaw ? number_format($oldValueRaw, 0, ',', '.') : '0';
                    $newValue = $newValueRaw ? number_format($newValueRaw, 0, ',', '.') : '0';

                    // Use numeric comparison for accuracy
                    if (abs($oldNumeric - $newNumeric) > 0.01) { // Allow for floating point precision
                        try {
                            ActivityLogHelper::logDataEdited(
                                $dokumen,
                                $field,
                                $oldValue,
                                $newValue,
                                'operator'
                            );
                        } catch (\Exception $logException) {
                            \Log::error('Failed to log data edit for ' . $field . ': ' . $logException->getMessage());
                        }
                    }
                    continue; // Skip the general comparison below
                } elseif ($field === 'tahun') {
                    $oldValue = $oldValueRaw ? (string) $oldValueRaw : null;
                    $newValue = $newValueRaw ? (string) $newValueRaw : null;
                } else {
                    $oldValue = $oldValueRaw;
                    $newValue = $newValueRaw;
                }

                // Only log if value actually changed (skip nilai_rupiah as it's handled above)
                if ($field !== 'nilai_rupiah' && $oldValue != $newValue) {
                    try {
                        ActivityLogHelper::logDataEdited(
                            $dokumen,
                            $field,
                            $oldValue,
                            $newValue,
                            'operator'
                        );
                    } catch (\Exception $logException) {
                        \Log::error('Failed to log data edit for ' . $field . ': ' . $logException->getMessage());
                    }
                }
            }

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

            // Update dibayar_kepada (multiple recipients) - delete existing and create new
            $dokumen->dibayarKepadas()->delete();
            if ($request->has('dibayar_kepada')) {
                foreach ($request->dibayar_kepada as $penerima) {
                    if (!empty(trim($penerima))) {
                        DibayarKepada::create([
                            'dokumen_id' => $dokumen->id,
                            'nama_penerima' => trim($penerima),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil diperbarui.');

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Error updating dokumen: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['_token', '_method', 'password']),
            ]);

            // Provide more informative error message
            $errorMessage = 'Terjadi kesalahan saat memperbarui dokumen.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            } else {
                $errorMessage .= ' Silakan coba lagi atau hubungi administrator jika masalah berlanjut.';
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function destroy(Dokumen $dokumen)
    {
        try {
            DB::beginTransaction();

            // Delete related records first
            $dokumen->dokumenPos()->delete();
            $dokumen->dokumenPrs()->delete();

            // Delete dokumen
            $dokumen->delete();

            DB::commit();

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Error deleting dokumen: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus dokumen. Silakan coba lagi.');
        }
    }

    /**
     * Send document to Team Verifikasi (Reviewer)
     * Sets status to WAITING_REVIEWER_APPROVAL - Approval Gate Implementation
     */
    public function sendToTeamVerifikasi(Dokumen $dokumen)
    {
        try {
            // Handle old data that might not have workflow fields
            $currentHandler = $dokumen->current_handler ?? 'operator';
            $createdBy = $dokumen->created_by ?? 'operator';

            // Check if document is created by Operator (case-insensitive)
            $createdByOperator = in_array(strtolower($createdBy), ['operator', 'Operator', 'operator']);

            // Check if document is currently with Operator (case-insensitive)
            $currentHandlerOperator = in_array(strtolower($currentHandler), ['operator', 'Operator', 'operator']);

            // Check if document is rejected (can be sent again)
            $isRejected = false;
            $teamVerifikasiStatus = $dokumen->getStatusForRole('team_verifikasi');
            if ($teamVerifikasiStatus && strtolower($teamVerifikasiStatus->status ?? '') === 'rejected') {
                $isRejected = true;
            } else {
                // Fallback: check from roleStatuses directly
                $rejectedStatus = $dokumen->roleStatuses()
                    ->where('status', 'rejected')
                    ->whereIn('role_code', ['team_verifikasi', 'team_verifikasi'])
                    ->first();
                $isRejected = $rejectedStatus !== null;
            }

            // Check if document status is allowed (case-insensitive)
            $statusLower = strtolower($dokumen->status ?? '');
            $allowedStatuses = ['draft', 'returned_to_Operator', 'sedang diproses', 'menunggu_approval_keuangan', 'sent_to_team_verifikasi'];
            $isAllowedStatus = in_array($statusLower, $allowedStatuses);

            // Check if document is from Bagian (current_handler = operator but created_by != operator)
            $isFromBagian = $currentHandlerOperator && !$createdByOperator;

            // Allow sending if:
            // 1. Document is rejected (can always be resent) AND with Operator
            // 2. OR document has allowed status AND with Operator
            // 3. OR document is from Bagian AND with Operator
            if (!$isRejected && !$isAllowedStatus && !$isFromBagian) {
                return back()->with('error', 'Dokumen tidak dapat dikirim. Status dokumen harus draft, returned, atau sedang diproses.');
            }

            // Only allow if current_handler is Operator (case-insensitive)
            // Documents from Bagian (created_by != 'operator') should also be able to be sent
            if (!$currentHandlerOperator) {
                return back()->with('error', 'Anda tidak memiliki izin untuk mengirim dokumen ini.');
            }

            DB::beginTransaction();

            // Kirim ke inbox Ibu Yuni untuk approval dengan status WAITING_REVIEWER_APPROVAL
            // Method sendToInbox() akan set:
            // - status ke 'waiting_reviewer_approval' untuk Team Verifikasi
            // - current_stage ke 'reviewer'
            // - last_action_status ke 'sent_to_team_verifikasi'
            // - calls sendToRoleInbox() internally which creates status record and activity log
            $dokumen->sendToInbox('team_verifikasi');

            $dokumen->refresh();
            DB::commit();

            // Broadcast event untuk inbox (DocumentSentToInbox sudah di-broadcast di method sendToInbox)
            try {
                \Log::info('Document sent to inbox Team Verifikasi with WAITING_REVIEWER_APPROVAL status', [
                    'document_id' => $dokumen->id,
                    'status' => $dokumen->status,
                    'current_stage' => $dokumen->current_stage,
                    'inbox_approval_status' => $dokumen->getStatusForRole('team_verifikasi')->status ?? 'unknown',
                ]);
            } catch (\Exception $logException) {
                \Log::error('Failed to log document sent to inbox: ' . $logException->getMessage());
            }

            return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dikirim ke inbox Team Verifikasi dan menunggu persetujuan.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending document: ' . $e->getMessage(), [
                'document_id' => $dokumen->id ?? null,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengirim dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Approve document by Reviewer (Ibu Yuni)
     * This method is called when Reviewer clicks "Setujui" button
     */
    public function approveDocument(Dokumen $dokumen)
    {
        try {
            $currentUser = auth()->user();
            $userRole = $this->getUserRole($currentUser);

            // Only Team Verifikasi (Reviewer) can approve documents waiting for reviewer approval
            if ($userRole !== 'team_verifikasi' && $userRole !== 'team_verifikasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menyetujui dokumen ini.'
                ], 403);
            }

            // Check if document is waiting for reviewer approval
            if (
                $dokumen->status !== 'waiting_reviewer_approval' &&
                !($dokumen->isWaitingApprovalFor('team_verifikasi'))
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen belum disetujui oleh Ibu Yuni.'
                ], 403);
            }

            DB::beginTransaction();

            // Approve from inbox
            $dokumen->approveInbox();

            // Update workflow tracking
            $dokumen->current_stage = 'reviewer';
            $dokumen->last_action_status = 'approved_by_reviewer';
            $dokumen->status = 'sedang diproses'; // After approval, document is being processed
            $dokumen->save();

            $dokumen->refresh();
            DB::commit();

            \Log::info('Document approved by Reviewer', [
                'document_id' => $dokumen->id,
                'approved_by' => $userRole,
                'status' => $dokumen->status,
                'current_stage' => $dokumen->current_stage
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disetujui dan telah masuk ke daftar dokumen Reviewer.'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error approving document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyetujui dokumen.'
            ], 500);
        }
    }

    /**
     * Bulk send multiple documents to Team Verifikasi
     * Uses same logic as sendToTeamVerifikasi but for multiple documents
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkSendToTeamVerifikasi(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'exists:dokumens,id'
        ]);

        $successCount = 0;
        $failedCount = 0;
        $failedDocuments = [];
        $successDocuments = [];

        DB::beginTransaction();
        try {
            foreach ($request->document_ids as $docId) {
                $dokumen = Dokumen::find($docId);

                if (!$dokumen) {
                    $failedCount++;
                    $failedDocuments[] = ['id' => $docId, 'reason' => 'Dokumen tidak ditemukan'];
                    continue;
                }

                // Validate document can be sent using helper method
                $canSendResult = $this->canSendToVerifikasi($dokumen);

                if ($canSendResult['canSend']) {
                    // Use the same sendToInbox method as single send to ensure consistency
                    // This ensures documents are NOT deleted, maintain proper status, etc.
                    $dokumen->sendToInbox('team_verifikasi');
                    $dokumen->refresh();

                    $successCount++;
                    $successDocuments[] = $dokumen->nomor_agenda;

                    \Log::info('Bulk send: Document sent to Team Verifikasi inbox', [
                        'document_id' => $dokumen->id,
                        'nomor_agenda' => $dokumen->nomor_agenda,
                        'status' => $dokumen->status,
                        'inbox_status' => $dokumen->getStatusForRole('team_verifikasi')->status ?? 'unknown'
                    ]);
                } else {
                    $failedCount++;
                    $failedDocuments[] = [
                        'nomor_agenda' => $dokumen->nomor_agenda,
                        'reason' => $canSendResult['reason']
                    ];
                }
            }

            DB::commit();

            // Log summary
            \Log::info('Bulk send completed', [
                'total_requested' => count($request->document_ids),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'success_documents' => $successDocuments,
                'failed_documents' => $failedDocuments
            ]);

            // Build response message
            $message = "Berhasil mengirim {$successCount} dokumen ke inbox Team Verifikasi.";
            if ($failedCount > 0) {
                $message .= " ({$failedCount} dokumen gagal dikirim)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'successCount' => $successCount,
                'failedCount' => $failedCount,
                'successDocuments' => $successDocuments,
                'failedDocuments' => $failedDocuments
            ]);

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Bulk send failed: ' . $e->getMessage(), [
                'document_ids' => $request->document_ids,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Check if document can be sent to Team Verifikasi
     * Returns array with canSend boolean and reason string
     * 
     * @param Dokumen $dokumen
     * @return array ['canSend' => bool, 'reason' => string]
     */
    private function canSendToVerifikasi(Dokumen $dokumen): array
    {
        // Get document properties
        $currentHandler = strtolower($dokumen->current_handler ?? 'operator');
        $createdBy = strtolower($dokumen->created_by ?? 'operator');

        // Check if created by Operator (case-insensitive)
        $operatorAliases = ['operator'];
        $createdByOperator = in_array($createdBy, $operatorAliases);
        $currentHandlerOperator = in_array($currentHandler, $operatorAliases);

        // Permission check - only require current handler to be Operator
        // Documents from Bagian (created_by != 'operator') should also be able to be sent
        if (!$currentHandlerOperator) {
            return [
                'canSend' => false,
                'reason' => 'Dokumen tidak sedang di-handle oleh Operator'
            ];
        }

        // Check if document is rejected (can always resend)
        $isRejected = false;
        $teamVerifikasiStatus = $dokumen->getStatusForRole('team_verifikasi');
        if ($teamVerifikasiStatus && strtolower($teamVerifikasiStatus->status ?? '') === 'rejected') {
            $isRejected = true;
        } else {
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->whereIn('role_code', ['team_verifikasi'])
                ->first();
            $isRejected = $rejectedStatus !== null;
        }

        // If rejected, can always resend
        if ($isRejected) {
            return ['canSend' => true, 'reason' => ''];
        }

        // Check status for non-rejected documents
        $statusLower = strtolower($dokumen->status ?? '');
        $allowedStatuses = ['draft', 'returned_to_operator', 'sedang diproses', 'menunggu_approval_keuangan', 'sent_to_team_verifikasi'];

        // Additional check for Bagian documents (current_handler = operator but created_by != operator)
        $isFromBagian = $currentHandlerOperator && !$createdByOperator;
        if ($isFromBagian) {
            // Bagian documents can always be sent if they're with Operator
            return ['canSend' => true, 'reason' => ''];
        }

        if (!in_array($statusLower, $allowedStatuses)) {
            return [
                'canSend' => false,
                'reason' => 'Status dokumen tidak memungkinkan pengiriman (' . $dokumen->status . ')'
            ];
        }

        // Check if already sent and pending approval
        $isPending = $dokumen->roleStatuses()
            ->where('role_code', 'team_verifikasi')
            ->where('status', 'pending')
            ->exists();

        if ($isPending) {
            return [
                'canSend' => false,
                'reason' => 'Dokumen sudah dikirim dan menunggu approval'
            ];
        }

        return ['canSend' => true, 'reason' => ''];
    }

    /**
     * Helper untuk mendapatkan role user
     */
    private function getUserRole($user)
    {
        if (!$user) {
            return null;
        }

        // Coba dengan field role langsung
        if (isset($user->role)) {
            return $user->role;
        }

        // Coba dengan field name
        if (isset($user->name)) {
            $nameToRole = [
                'Operator' => 'operator',
                'team_verifikasi' => 'team_verifikasi',
                'Ibu B' => 'team_verifikasi',
                'Ibu Yuni' => 'team_verifikasi',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
                'Pembayaran' => 'pembayaran'
            ];

            return $nameToRole[$user->name] ?? null;
        }

        return null;
    }

    /**
     * Get search suggestions when no results found
     */
    private function getSearchSuggestions($searchTerm, $year = null): array
    {
        $suggestions = [];

        // Get all unique values from relevant fields
        $baseQuery = Dokumen::where('created_by', 'operator');

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
        $dibayarKepadaValues = DibayarKepada::whereHas('dokumen', function ($q) use ($year) {
            $q->where('created_by', 'operator');
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






