<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDokumenRequest;
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
    /**
     * Build the base operator document query (dipakai index)
     */
    private function buildOperatorQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'activityLogs', 'roleStatuses', 'roleData'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(created_by) IN (?, ?, ?)', ['operator', 'Operator', 'operator'])
                    ->orWhere('created_by', 'operator')
                    ->orWhere('current_handler', 'operator')
                    ->orWhereHas('roleStatuses', function ($subQ) {
                        $subQ->where('role_code', 'operator');
                    });
            });

        // Sort
        if ($request->has('sort') || $request->has('order')) {
            $sortColumn = $request->get('sort', 'nomor_agenda');
            $sortOrder  = $request->get('order', 'desc');
            $sortOrder  = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
            session(['operator_sort_column' => $sortColumn, 'operator_sort_order' => $sortOrder]);
        } else {
            $sortColumn = session('operator_sort_column', 'nomor_agenda');
            $sortOrder  = session('operator_sort_order', 'desc');
            $sortOrder  = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        }

        if ($sortColumn === 'nomor_agenda') {
            $query->orderByRaw("CASE
                WHEN nomor_agenda REGEXP '^[0-9]+(_[0-9]+)?\$' THEN CAST(SUBSTRING_INDEX(nomor_agenda, '_', 1) AS UNSIGNED)
                WHEN nomor_agenda REGEXP '^[0-9]+' THEN CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END {$sortOrder}")->orderBy('nomor_agenda', $sortOrder);
        } else {
            $allowed = ['nomor_spp','tanggal_masuk','nilai_rupiah','tanggal_spp','uraian_spp','kategori','kebun','jenis_dokumen','jenis_sub_pekerjaan','jenis_pembayaran','nama_pengirim','dibayar_kepada','no_berita_acara','tanggal_berita_acara','no_spk','tanggal_spk','tanggal_berakhir_spk','status'];
            if (in_array($sortColumn, $allowed)) {
                $query->orderBy($sortColumn, $sortOrder);
            }
            $query->orderByRaw("CASE
                WHEN nomor_agenda REGEXP '^[0-9]+(_[0-9]+)?\$' THEN CAST(SUBSTRING_INDEX(nomor_agenda, '_', 1) AS UNSIGNED)
                WHEN nomor_agenda REGEXP '^[0-9]+' THEN CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC");
        }

        // Search
        if ($request->filled('search') && trim((string)$request->search) !== '') {
            $search = trim((string)$request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%")
                    ->orWhere('nama_pengirim', 'like', "%{$search}%")
                    ->orWhere('bagian', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('jenis_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_berita_acara', 'like', "%{$search}%")
                    ->orWhere('no_spk', 'like', "%{$search}%")
                    ->orWhere('nomor_miro', 'like', "%{$search}%")
                    ->orWhere('dibayar_kepada', 'like', "%{$search}%");
                $numeric = preg_replace('/[^0-9]/', '', $search);
                if (is_numeric($numeric) && $numeric > 0) {
                    $q->orWhereRaw('CAST(nilai_rupiah AS CHAR) LIKE ?', ["%{$numeric}%"]);
                }
            })->orWhereHas('dibayarKepadas', fn($q) => $q->where('nama_penerima', 'like', "%{$search}%"));
        }

        // Year filter
        if ($request->filled('year')) {
            $query->where('tahun', $request->year);
        }

        // Status filter
        if ($request->filled('status_filter')) {
            switch ($request->status_filter) {
                case 'belum_dikirim':
                    $query->whereDoesntHave('roleStatuses', fn($q) => $q->where('role_code', 'team_verifikasi'))
                          ->where('status', 'draft');
                    break;
                case 'menunggu_approval':
                    $query->whereHas('roleStatuses', fn($q) => $q->where('role_code', 'team_verifikasi')
                          ->where('status', \App\Models\DokumenStatus::STATUS_PENDING));
                    break;
                case 'terkirim':
                    $query->where(fn($q) => $q
                        ->whereHas('roleStatuses', fn($q2) => $q2->where('role_code', 'team_verifikasi')
                            ->where('status', \App\Models\DokumenStatus::STATUS_APPROVED))
                        ->orWhereHas('roleStatuses', fn($q3) => $q3->whereIn('role_code', ['perpajakan','akutansi','pembayaran'])));
                    break;
            }
        }

        return $query;
    }

    /**
     * Endpoint JSON progressive-load untuk Tabulator (daftar dokumen Operator).
     * Memakai ulang buildOperatorQuery() (scope/search/year/status/sort) dan
     * OperatorDocumentRow untuk derivasi baris — tanpa logika filter baru.
     * Balas {last_page, total, data:[row,...]}.
     */
    public function datatable(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildOperatorQuery($request);

        $size = (int) $request->input('size', 100);
        $size = ($size > 0 && $size <= 200) ? $size : 100;
        $page = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        // Bangun daftar opsi pengurus dokumen SEKALI per-request (hindari N+1);
        // OperatorDocumentRow menanamkan apa adanya ke tiap baris.
        $handlerOptions = $this->buildHandlerOptions();

        $data = collect($paginator->items())
            ->map(fn ($d) => \App\Support\OperatorDocumentRow::fromDokumen($d, $handlerOptions, auth()->user()?->role))
            ->all();

        return response()->json([
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
            'data'      => $data,
        ]);
    }

    /**
     * Susun daftar opsi pengurus dokumen (handler_options) SEKALI per-request.
     * 5 opsi base peran + optgroup 'Bagian' (bila ada Bagian aktif). Sumber
     * tunggal agar datatable() & inlineCreate() memakai bentuk yang identik
     * (OperatorDocumentRow menanamkannya apa adanya ke tiap baris).
     */
    private function buildHandlerOptions(): array
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
                'options'  => $bagian->map(fn ($b) => [
                    'value' => 'bagian_' . strtolower($b->kode),
                    'label' => $b->nama ?: $b->kode,
                ])->all(),
            ];
        }

        return $handlerOptions;
    }

    public function index(Request $request)
    {
        // Tabel Tabulator tak pernah mengirim sort/order. Sesi sort lama (peninggalan
        // jalur ?classic yang kini dimatikan) selalu dibersihkan agar tak mengunci
        // urutan tabel baru selamanya tanpa cara membatalkan. Harus dijalankan SEBELUM
        // buildOperatorQuery() membaca sesi tersebut.
        session()->forget(['operator_sort_column', 'operator_sort_order']);

        $query = $this->buildOperatorQuery($request);
        $sortColumn = session('operator_sort_column', 'nomor_agenda');
        $sortOrder  = session('operator_sort_order', 'desc');

        $perPage = $request->get('per_page', 'all');
        $showAllRows = $perPage === 'all';
        if ($showAllRows) {
            $perPage = 100;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['operator_per_page' => $showAllRows ? 'all' : $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Get suggestions if no results found
        $suggestions = [];
        if ($request->filled('search') && trim((string) $request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string) $request->search);
            $suggestions = $this->getSearchSuggestions($searchTerm, $request->year);
        }

        // Available columns for customization — sumber terpusat (Operator memakai
        // base penuh, termasuk opsi 'status'). Sumber: config/document_columns.php.
        $availableColumns = config('document_columns.base');

        $defaultColumns = $this->defaultOperatorDocumentColumns($availableColumns);

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Remove deprecated columns if they exist
        $selectedColumns = array_filter($selectedColumns, function ($col) {
            return $col !== 'nomor_mirror';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from session if available
            $selectedColumns = session('dokumens_table_columns', $defaultColumns);

            // Remove deprecated columns if they exist in session
            $selectedColumns = array_filter($selectedColumns, function ($col) {
                return $col !== 'nomor_mirror';
            });
            $selectedColumns = array_values($selectedColumns); // Re-index array

            if ($this->isLegacyOperatorDefaultColumns($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session with cleaned columns
            session(['dokumens_table_columns' => $selectedColumns]);
        }

        // Load dropdown options for inline editing
        $ieKategoriList = [];
        $ieSubKriteriaList = [];
        $ieItemSubKriteriaList = [];
        $ieJenisPembayaranList = [];
        try {
            $ieKategoriList = KategoriKriteria::where('tipe', 'Keluar')->get(['id_kategori_kriteria as id', 'nama_kriteria'])->toArray();
            $ieSubKriteriaList = SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])->toArray();
            $ieItemSubKriteriaList = ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])->toArray();
            $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])->toArray();
        } catch (\Exception $e) {
            \Log::error('Error loading inline edit dropdown options: ' . $e->getMessage());
        }

        // Fallback: load distinct values from dokumens table if cash_bank connection unavailable
        if (empty($ieKategoriList)) {
            $ieKategoriList = Dokumen::whereNotNull('kategori')->where('kategori', '!=', '')
                ->distinct()->orderBy('kategori')
                ->pluck('kategori')
                ->map(fn($v) => ['id' => $v, 'nama_kriteria' => $v])
                ->toArray();
        }
        if (empty($ieSubKriteriaList)) {
            $ieSubKriteriaList = Dokumen::whereNotNull('jenis_dokumen')->where('jenis_dokumen', '!=', '')
                ->distinct()->orderBy('jenis_dokumen')
                ->get(['jenis_dokumen', 'kategori'])
                ->map(fn($d) => ['id' => $d->jenis_dokumen, 'nama_sub_kriteria' => $d->jenis_dokumen, 'id_kategori_kriteria' => $d->kategori])
                ->unique('nama_sub_kriteria')->values()
                ->toArray();
        }
        if (empty($ieItemSubKriteriaList)) {
            $ieItemSubKriteriaList = Dokumen::whereNotNull('jenis_sub_pekerjaan')->where('jenis_sub_pekerjaan', '!=', '')
                ->distinct()->orderBy('jenis_sub_pekerjaan')
                ->get(['jenis_sub_pekerjaan', 'jenis_dokumen'])
                ->map(fn($d) => ['id' => $d->jenis_sub_pekerjaan, 'nama_item_sub_kriteria' => $d->jenis_sub_pekerjaan, 'id_sub_kriteria' => $d->jenis_dokumen])
                ->unique('nama_item_sub_kriteria')->values()
                ->toArray();
        }
        if (empty($ieJenisPembayaranList)) {
            $ieJenisPembayaranList = Dokumen::whereNotNull('jenis_pembayaran')->where('jenis_pembayaran', '!=', '')
                ->distinct()->orderBy('jenis_pembayaran')
                ->pluck('jenis_pembayaran')
                ->map(fn($v) => ['id_jenis_pembayaran' => $v, 'nama_jenis_pembayaran' => $v])
                ->toArray();
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
            "ieKategoriList" => $ieKategoriList,
            "ieSubKriteriaList" => $ieSubKriteriaList,
            "ieItemSubKriteriaList" => $ieItemSubKriteriaList,
            "ieJenisPembayaranList" => $ieJenisPembayaranList,
        );

        // Operator selalu dilayani view Tabulator; flag ?classic dimatikan (query param
        // sisa dibiarkan no-op). Penghapusan fisik view classic dilakukan task berikutnya.
        return view('operator.dokumens.daftarDokumenTabulator', $data);
    }


    /**
     * Membuat satu baris dokumen draft langsung dari daftar dokumen (inline add).
     * Hanya butuh nomor_agenda; field lain di-flush via inline-update dari sisi klien.
     */
    public function inlineCreate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'nomor_agenda' => 'required|string|max:255|unique:dokumens,nomor_agenda',
        ], [
            'nomor_agenda.required' => 'Nomor agenda harus diisi.',
            'nomor_agenda.unique'   => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
        ]);

        $now = Carbon::now();
        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dokumen = Dokumen::create([
            'nomor_agenda'    => $validated['nomor_agenda'],
            'bulan'           => $bulanIndonesia[$now->month],
            'tahun'           => $now->year,
            'tanggal_masuk'   => $now,
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);

        try {
            ActivityLogHelper::logCreated($dokumen);
        } catch (\Exception $logException) {
            \Log::warning('Gagal mencatat activity log inline-create: ' . $logException->getMessage());
        }

        // Eager-load relasi yang dipakai partial agar markup baris identik
        $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

        return response()->json([
            'success' => true,
            'id'      => $dokumen->id,
            // Objek baris JSON untuk Tabulator — satu-satunya konsumen. Partial
            // _tableRowsAjax tak lagi dirender di sini (jalur render lama dilepas;
            // view classic-nya dihapus di task berikutnya).
            'row'     => \App\Support\OperatorDocumentRow::fromDokumen($dokumen, $this->buildHandlerOptions(), auth()->user()?->role),
        ]);
    }


    private function operatorDocumentColumns(): array
    {
        return [
            'nomor_agenda' => 'Nomor Agenda',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'kategori' => 'Kriteria CF',
            'jenis_dokumen' => 'Sub Kriteria',
            'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_spp' => 'Tanggal SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'dibayar_kepada' => 'Dibayar Kepada',
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
            'tanggal_dibayar' => 'Tanggal Bayar',
            'bagian' => 'Bagian',
            'link' => 'Link',
            'nama_pengirim' => 'Nama Pengirim',
            'no_spk' => 'No SPK',
            'tanggal_spk' => 'Tanggal SPK',
            'tanggal_berakhir_spk' => 'Tanggal Akhir SPK',
            'no_berita_acara' => 'No Berita Acara (BA)',
            'tanggal_berita_acara' => 'Tanggal Berita Acara (BA)',
            'nomor_po' => 'No PO',
            'nomor_miro' => 'No Miro',
            'no_faktur' => 'No Faktur',
            'tanggal_faktur' => 'Tanggal Faktur',
            'tanggal_selesai_verifikasi_pajak' => 'Tgl Selesai Verifikasi Pajak',
            'jenis_pph' => 'Jenis PPh',
            'dpp_pph' => 'DPP PPh',
            'ppn_terhutang' => 'PPH Terhutang',
            'status' => 'Status',
            'kebun' => 'Kebun',
            'npwp' => 'NPWP',
            'link_dokumen_pajak' => 'Link Dokumen Pajak',
        ];
    }


    private function defaultOperatorDocumentColumns(array $availableColumns): array
    {
        return array_values(array_filter(array_keys($availableColumns), fn($col) => $col !== 'nomor_mirror'));
    }

    private function isLegacyOperatorDefaultColumns($selectedColumns): bool
    {
        $selectedColumns = array_values((array) $selectedColumns);
        $legacyDefaults = [
            ['nomor_agenda', 'nomor_spp', 'tanggal_masuk', 'nilai_rupiah', 'status'],
            ['nomor_agenda', 'tanggal_masuk', 'uraian_spp', 'nilai_rupiah', 'status'],
        ];

        return collect($legacyDefaults)->contains(fn($legacy) => $selectedColumns === $legacy);
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
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
        }

        // Fallback: load distinct values from dokumens table when cash_bank is unavailable
        if ($kategoriKriteria->isEmpty()) {
            $kategoriKriteria = Dokumen::whereNotNull('kategori')->where('kategori', '!=', '')
                ->distinct()->orderBy('kategori')->pluck('kategori')
                ->map(fn($v) => (object)['id_kategori_kriteria' => $v, 'nama_kriteria' => $v, 'tipe' => 'Keluar']);
        }
        if ($subKriteria->isEmpty()) {
            $subKriteria = Dokumen::whereNotNull('jenis_dokumen')->where('jenis_dokumen', '!=', '')
                ->distinct()->orderBy('jenis_dokumen')->get(['jenis_dokumen', 'kategori'])
                ->unique('jenis_dokumen')
                ->map(fn($d) => (object)['id_sub_kriteria' => $d->jenis_dokumen, 'nama_sub_kriteria' => $d->jenis_dokumen, 'id_kategori_kriteria' => $d->kategori]);
        }
        if ($itemSubKriteria->isEmpty()) {
            $itemSubKriteria = Dokumen::whereNotNull('jenis_sub_pekerjaan')->where('jenis_sub_pekerjaan', '!=', '')
                ->distinct()->orderBy('jenis_sub_pekerjaan')->get(['jenis_sub_pekerjaan', 'jenis_dokumen'])
                ->unique('jenis_sub_pekerjaan')
                ->map(fn($d) => (object)['id_item_sub_kriteria' => $d->jenis_sub_pekerjaan, 'nama_item_sub_kriteria' => $d->jenis_sub_pekerjaan, 'id_sub_kriteria' => $d->jenis_dokumen]);
        }
        $isDropdownAvailable = $kategoriKriteria->isNotEmpty();

        // Ambil data jenis pembayaran dari database cash_bank_new
        $jenisPembayaranList = collect([]);
        $isJenisPembayaranAvailable = false;
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            $isJenisPembayaranAvailable = $jenisPembayaranList->count() > 0;
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran data (create): ' . $e->getMessage());
            $jenisPembayaranList = collect([]);
        }
        if ($jenisPembayaranList->isEmpty()) {
            $jenisPembayaranList = Dokumen::whereNotNull('jenis_pembayaran')->where('jenis_pembayaran', '!=', '')
                ->distinct()->orderBy('jenis_pembayaran')->pluck('jenis_pembayaran')
                ->map(fn($v) => (object)['id_jenis_pembayaran' => $v, 'nama_jenis_pembayaran' => $v]);
            $isJenisPembayaranAvailable = $jenisPembayaranList->isNotEmpty();
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
        } elseif ($dokumen->status === 'returned_to_operator') {
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
                'time' => $dokumen->returned_at ? $dokumen->returned_at->format('d M Y H:i') : '',
                'description' => $dokumen->return_reason ? 'Dikembalikan: ' . $dokumen->return_reason : 'Dokumen dikembalikan untuk perbaikan',
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

            // Check if user should return to fullscreen mode
            $returnFullscreen = $request->input('return_to_fullscreen', false);
            $returnUrl = $request->input('return_url', route('documents.index'));

            if ($returnFullscreen) {
                $urlWithFullscreen = $returnUrl .
                    (str_contains($returnUrl, '?') ? '&' : '?') .
                    'fullscreen=1';

                return redirect($urlWithFullscreen)
                    ->with('success', $successMessage);
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



    /**
     * Inline update a single field via AJAX (spreadsheet-style editing)
     */
    public function inlineUpdate(Request $request, Dokumen $dokumen)
    {
        // Validate permission: user's role must match document's current_handler
        $userRole = strtolower(auth()->user()->role ?? '');
        $currentHandler = strtolower($dokumen->current_handler ?? '');
        $editableRoles = ['operator', 'team_verifikasi', 'verifikasi', 'perpajakan', 'akutansi', 'pembayaran', 'bagian'];

        // Normalize role aliases: 'verifikasi' and 'team_verifikasi' are the same role
        $normaliseRole = function (string $r): string {
            return in_array($r, ['verifikasi', 'team_verifikasi']) ? 'team_verifikasi' : $r;
        };
        $userRoleNorm  = $normaliseRole($userRole);
        $handlerNorm   = $normaliseRole($currentHandler);

        // Statuses that indicate the document is currently at team_verifikasi's stage
        $verifikasiAllowedStatuses = [
            'sedang diproses', 'sedang_diproses', 'sent_to_team_verifikasi',
            'menunggu_di_approve', 'returned_to_verifikasi', 'returned_to_department',
        ];
        $docStatus = strtolower($dokumen->status ?? '');

        // For bagian: allow edit when status is 'belum_dikirim' or 'returned_to_bidang'
        $bagianAllowedStatuses = ['belum dikirim', 'belum_dikirim', 'returned_to_bidang'];
        $isBagianUser = $userRole === 'bagian';
        $isBagianAllowed = $isBagianUser && in_array($docStatus, $bagianAllowedStatuses);

        // For pembayaran: dashboard pembayaran is allowed to correct payment document data
        // regardless of the current handler/stage.
        $isPembayaranUser = $userRole === 'pembayaran';
        $isPembayaranAllowed = $isPembayaranUser;

        // For team_verifikasi / verifikasi:
        //   Allow if handler matches OR if document status indicates it's at their stage
        $isVerifikasiUser = in_array($userRole, ['team_verifikasi', 'verifikasi']);
        $isVerifikasiStatus  = in_array($docStatus, $verifikasiAllowedStatuses);

        // Primary gate: role must be in editableRoles AND (handler matches OR special rules pass)
        $handlerMatchesUser = $handlerNorm === $userRoleNorm;
        $passedGate = in_array($userRole, $editableRoles)
            && ($handlerMatchesUser
                || ($isVerifikasiUser && $isVerifikasiStatus)
                || $isBagianAllowed
                || $isPembayaranAllowed);

        if (!$passedGate) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengedit dokumen ini.'], 403);
        }

        // For operator: enforce status whitelist
        if ($userRole === 'operator') {
            $allowedStatuses = ['draft', 'returned_to_operator', 'belum_dikirim', 'belum dikirim', 'menunggu_approval_keuangan'];
            $isRejected = $dokumen->roleStatuses()->where('status', 'rejected')->whereIn('role_code', ['team_verifikasi'])->exists();
            if (!$isRejected && !in_array($docStatus, $allowedStatuses)) {
                return response()->json(['success' => false, 'message' => 'Dokumen tidak dapat diedit pada status ini.'], 403);
            }
        }

        // For team_verifikasi / verifikasi: must have valid status (already checked in gate above via $isVerifikasiStatus)
        // No separate block needed — if they passed the gate with status check, they're allowed.


        $field = $request->input('field');
        $value = $request->input('value');

        // Whitelist of editable fields (all roles)
        $editableFields = [
            'nomor_agenda', 'nomor_spp', 'tanggal_spp', 'uraian_spp', 'nilai_rupiah',
            'kategori', 'jenis_dokumen', 'jenis_sub_pekerjaan', 'jenis_pembayaran',
            'kebun', 'bagian', 'nama_pengirim', 'dibayar_kepada',
            'no_berita_acara', 'tanggal_berita_acara',
            'no_spk', 'tanggal_spk', 'tanggal_berakhir_spk',
            'nomor_miro', 'tanggal_miro', 'no_faktur', 'tanggal_faktur',
            'tanggal_paraf', 'pemaraf', 'bulan', 'tahun',
            'tanggal_dibayar',
            // Perpajakan-specific
            'jenis_pph', 'dpp_pph', 'ppn_terhutang', 'tanggal_selesai_verifikasi_pajak',
            'npwp', 'link_dokumen_pajak',
            'link',
        ];

        if (!in_array($field, $editableFields)) {
            return response()->json(['success' => false, 'message' => 'Field tidak dapat diedit.'], 422);
        }

        try {
            DB::beginTransaction();

            $oldValue = $dokumen->$field;
            $saveValue = $value;

            // Special processing per field
            if ($field === 'nilai_rupiah') {
                $saveValue = $value ? (float) preg_replace('/[^0-9]/', '', $value) : null;
                if ($saveValue <= 0) $saveValue = null;
            } elseif ($field === 'tanggal_spp') {
                // Update bulan & tahun when tanggal_spp changes
                if (!empty($value)) {
                    $tgl = \Carbon\Carbon::parse($value);
                    $bulanMap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    $dokumen->bulan = $bulanMap[$tgl->month];
                    $dokumen->tahun = $tgl->year;
                }
                $saveValue = !empty($value) ? $value : null;
            } elseif (in_array($field, ['dpp_pph', 'ppn_terhutang'])) {
                $saveValue = $value ? (float) preg_replace('/[^0-9]/', '', $value) : null;
                if ($saveValue <= 0) $saveValue = null;
            } elseif (in_array($field, ['tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk', 'tanggal_faktur', 'tanggal_paraf', 'tanggal_miro', 'tanggal_selesai_verifikasi_pajak'])) {
                $saveValue = !empty($value) ? $value : null;
            } elseif ($field === 'tanggal_dibayar') {
                if ($userRole !== 'pembayaran') {
                    return response()->json(['success' => false, 'message' => 'Hanya role pembayaran yang dapat mengubah tanggal bayar.'], 403);
                }

                if (empty($value)) {
                    return response()->json(['success' => false, 'message' => 'Tanggal bayar wajib diisi untuk menandai dokumen sudah dibayar.'], 422);
                }

                $saveValue = \Carbon\Carbon::parse($value)->format('Y-m-d');
                $dokumen->status_pembayaran = 'sudah_dibayar';
                $dokumen->status = 'completed';
            } elseif (in_array($field, ['link', 'link_dokumen_pajak'])) {
                // Sanitasi + validasi skema URL untuk mencegah stored-XSS
                // (mis. javascript:, data:). Skema berbahaya ditolak (422),
                // URL tanpa skema otomatis di-prefix https://.
                $saveValue = \App\Support\SafeUrl::sanitizeForStorage($value);
            } elseif ($field === 'nomor_agenda') {
                // Check uniqueness
                $exists = Dokumen::where('nomor_agenda', $value)->where('id', '!=', $dokumen->id)->exists();
                if ($exists) {
                    return response()->json(['success' => false, 'message' => 'Nomor agenda sudah digunakan.'], 422);
                }
            } elseif ($field === 'dibayar_kepada') {
                // Save to dibayarKepadas relation
                // Support both comma-separated (from text input) and newline-separated (legacy)
                $dokumen->dibayarKepadas()->delete();
                $rawValue = $value ?? '';
                // Normalize: replace newlines with commas, then split by comma
                $normalized = preg_replace('/[\r\n]+/', ',', $rawValue);
                $names = array_filter(array_map('trim', explode(',', $normalized)));
                foreach ($names as $nama) {
                    if (!empty($nama)) {
                        \App\Models\DibayarKepada::create(['dokumen_id' => $dokumen->id, 'nama_penerima' => $nama]);
                    }
                }
                DB::commit();
                // Return formatted display value (comma-separated for text input display)
                $displayValue = $dokumen->dibayarKepadas()->pluck('nama_penerima')->implode(', ');
                return response()->json([
                    'success'       => true,
                    'display_value' => $displayValue ?: '-',
                    'raw_value'     => $displayValue, // raw value = same as display for text input
                ]);
            }

            $dokumen->$field = $saveValue;
            $dokumen->save();

            // Log the change
            try {
                ActivityLogHelper::logDataEdited($dokumen, $field, $oldValue, $saveValue, $userRole);
            } catch (\Exception $e) {
                \Log::error('Inline edit log failed: ' . $e->getMessage());
            }

            // Sync to Cash Bank for relevant fields
            try {
                $cbConnection = config('sync.cashbank_connection', 'cash_bank_new');
                $cbMap = ['nilai_rupiah' => 'nilai_rupiah', 'uraian_spp' => 'uraian', 'nomor_agenda' => 'no_agenda'];
                if (isset($cbMap[$field]) && $dokumen->nomor_agenda) {
                    DB::connection($cbConnection)->table('bank_keluars')
                        ->where(function ($q) use ($dokumen) {
                            $q->where('dokumen_id', $dokumen->id)->orWhere('no_agenda', $dokumen->nomor_agenda);
                        })
                        ->update([$cbMap[$field] => $saveValue, 'updated_at' => now()]);
                }
            } catch (\Throwable $e) {
                \Log::error('[InlineSync] CB sync gagal: ' . $e->getMessage());
            }

            DB::commit();

            // Build display value for frontend
            $displayValue = $saveValue;
            if ($field === 'nilai_rupiah' && $saveValue) {
                $displayValue = 'Rp. ' . number_format($saveValue, 0, ',', '.');
            } elseif ($field === 'tanggal_spp' && $saveValue) {
                $displayValue = \Carbon\Carbon::parse($saveValue)->format('d-m-Y');
            } elseif (in_array($field, ['tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_selesai_verifikasi_pajak','tanggal_dibayar']) && $saveValue) {
                $displayValue = \Carbon\Carbon::parse($saveValue)->format('d-m-Y');
            } elseif (in_array($field, ['dpp_pph', 'ppn_terhutang']) && $saveValue) {
                $displayValue = number_format($saveValue, 0, ',', '.');
            } elseif (in_array($field, ['link', 'link_dokumen_pajak'])) {
                // Bangun anchor aman untuk sink innerHTML di sisi klien.
                // Pertahanan berlapis: SafeUrl::external memastikan href selalu
                // berskema http(s); htmlspecialchars cegah attribute breakout.
                $safeHref = \App\Support\SafeUrl::external($saveValue);
                if ($safeHref) {
                    $label = $field === 'link_dokumen_pajak' ? 'Lihat Dokumen' : 'Lihat';
                    $displayValue = '<a href="' . htmlspecialchars($safeHref, ENT_QUOTES) . '" target="_blank" rel="noopener noreferrer" class="ie-link-anchor" onclick="event.stopPropagation();" style="color: #0d6efd; text-decoration: none;">'
                        . '<i class="fa-solid fa-link me-1"></i>' . $label . '</a>';
                } else {
                    $displayValue = '-';
                }
            }

            return response()->json([
                'success'       => true,
                'display_value' => $displayValue ?: '-',
                'raw_value'     => $saveValue,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?: 'Data tidak valid.',
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Inline update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Dokumen $dokumen)
    {
        try {
            DB::beginTransaction();

        // Delete ALL related records first to avoid FK constraint issues
        // and prevent orphaned urgency/tracking data
        \App\Models\DocumentTracking::where('document_id', $dokumen->id)->delete();
        $dokumen->dokumenPos()->delete();
        $dokumen->dokumenPrs()->delete();
        $dokumen->dibayarKepadas()->delete();
        $dokumen->roleData()->delete();
        $dokumen->roleStatuses()->delete();
        $dokumen->activityLogs()->delete();

        // Delete dokumen (urgency_active column is deleted with the row)
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

    /**
     * Get the next available nomor agenda for auto-generation.
     * Moved from routes/web.php closure for proper Separation of Concerns.
     */
    public function nextNomorAgenda()
    {
        try {
            $currentYear = Carbon::now()->year;

            // Find the highest nomor_agenda number for the current year
            $latestDokumen = Dokumen::where('nomor_agenda', 'like', '%_' . $currentYear)
                ->get()
                ->map(function ($doc) {
                    // Extract the numeric part before the underscore
                    $parts = explode('_', $doc->nomor_agenda);
                    return isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : 0;
                })
                ->max();

            $nextNumber = ($latestDokumen ?? 0) + 1;
            $nextNomorAgenda = $nextNumber . '_' . $currentYear;

            return response()->json([
                'success' => true,
                'next_nomor_agenda' => $nextNomorAgenda,
                'current_highest' => $latestDokumen ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil nomor agenda: ' . $e->getMessage(),
            ], 500);
        }
    }
}
