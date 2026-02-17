<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\DokumenRoleData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class BagianDokumenController extends Controller
{
    /**
     * Get the bagian code for the current user
     */
    private function getBagianCode()
    {
        $user = Auth::user();
        return $user->bagian_code ?? null;
    }

    /**
     * Get the bagian name for display
     */
    private function getBagianName()
    {
        $bagianCode = $this->getBagianCode();
        $bagianNames = [
            'AKN' => 'Akuntansi',
            'DPM' => 'DPM',
            'KPL' => 'Kepatuhan',
            'PMO' => 'PMO',
            'SDM' => 'SDM',
            'SKH' => 'Sekretariat',
            'TAN' => 'Tanaman',
            'TEP' => 'Teknik & Pengolahan',
        ];
        return $bagianNames[$bagianCode] ?? $bagianCode;
    }

    /**
     * Dashboard for Bagian
     */
    public function dashboard()
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // Count documents for this bagian - filter by created_by to only show docs created by this bagian
        $createdByValue = 'bagian_' . strtolower($bagianCode);
        $totalDokumen = Dokumen::where('created_by', $createdByValue)->count();
        $dokumenBelumDikirim = Dokumen::where('created_by', $createdByValue)
            ->where('status', 'belum dikirim')
            ->count();
        $dokumenTerkirim = Dokumen::where('created_by', $createdByValue)
            ->whereNotIn('status', ['belum dikirim'])
            ->count();
        $dokumenSelesai = Dokumen::where('created_by', $createdByValue)
            ->where('status', 'sudah dibayar')
            ->count();

        // Recent documents - only show docs created by this bagian
        $recentDokumens = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
            ->where('created_by', $createdByValue)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('bagian.dashboard', compact(
            'bagianCode',
            'bagianName',
            'totalDokumen',
            'dokumenBelumDikirim',
            'dokumenTerkirim',
            'dokumenSelesai',
            'recentDokumens'
        ));
    }

    /**
     * List documents for current bagian
     */
    public function index(Request $request)
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // Filter by created_by to only show documents created by this bagian
        $createdByValue = 'bagian_' . strtolower($bagianCode);
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
            ->where('created_by', $createdByValue)
            ->orderByRaw('CASE 
                WHEN nomor_agenda REGEXP "^[0-9]+$" THEN CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%");
            });
        }

        // Status filter with expanded options
        if ($request->has('status') && $request->status) {
            $statusFilter = $request->status;

            if ($statusFilter === 'belum_dikirim') {
                // Documents not yet sent
                $query->where('status', 'belum dikirim');
            } elseif ($statusFilter === 'menunggu_approve') {
                // Documents waiting for approval (sent to verifikasi but not yet approved)
                $query->where('status', 'menunggu_approval_keuangan');
            } elseif ($statusFilter === 'terkirim') {
                // Documents that have been sent (not 'belum dikirim' and not 'menunggu_approval_keuangan')
                $query->whereNotIn('status', ['belum dikirim', 'menunggu_approval_keuangan']);
            } elseif ($statusFilter === 'belum_dibayar') {
                // Documents not yet at payment stage and not paid
                $query->where(function ($q) {
                    $q->where('current_handler', '!=', 'pembayaran')
                        ->whereNull('tanggal_dibayar');
                });
            } elseif ($statusFilter === 'siap_dibayar') {
                // Documents at payment stage but not yet paid
                $query->where('current_handler', 'pembayaran')
                    ->whereNull('tanggal_dibayar');
            } elseif ($statusFilter === 'sudah_dibayar') {
                // Documents that have been paid
                $query->whereNotNull('tanggal_dibayar');
            } elseif ($statusFilter === 'dikembalikan') {
                // Documents returned from Team Verifikasi
                $query->where('status', 'returned_to_bidang')
                    ->where('return_source', $bagianCode);
            } else {
                // Fallback: try exact match
                $query->where('status', $statusFilter);
            }
        }

        // Year filter
        if ($request->has('tahun') && $request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $perPage = $request->get('per_page', session('bagian_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['bagian_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Available columns for customization
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_spp' => 'Nomor SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'nilai_rupiah' => 'Nilai Rupiah',
            'status' => 'Status',
            'umur_dokumen' => 'Umur Dokumen',
            'status_pembayaran' => 'Status Pembayaran',
            'tanggal_spp' => 'Tanggal SPP',
            'uraian_spp' => 'Uraian SPP',
            'kebun' => 'Kebun',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'nama_pengirim' => 'Nama Pengirim',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'dibayar_kepada' => 'Dibayar Kepada',
            'no_berita_acara' => 'No Berita Acara',
            'tanggal_berita_acara' => 'Tanggal Berita Acara',
        ];

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Default columns
        $defaultColumns = [
            'nomor_agenda',
            'nomor_spp',
            'tanggal_masuk',
            'nilai_rupiah',
            'status',
            'umur_dokumen',
            'status_pembayaran',
        ];

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['bagian_dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from session or use default
            $selectedColumns = session('bagian_dokumens_table_columns', $defaultColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['bagian_dokumens_table_columns' => $selectedColumns]);
        }

        return view('bagian.dokumens.daftarDokumen', compact(
            'dokumens',
            'bagianCode',
            'bagianName',
            'availableColumns',
            'selectedColumns'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // Get dropdown data from cash_bank database
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

        // Get jenis pembayaran
        $jenisPembayaranList = collect([]);
        $isJenisPembayaranAvailable = false;
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            $isJenisPembayaranAvailable = $jenisPembayaranList->count() > 0;
        } catch (\Exception $e) {
            \Log::error('Error fetching jenis pembayaran: ' . $e->getMessage());
        }

        return view('bagian.dokumens.tambahDokumen', compact(
            'bagianCode',
            'bagianName',
            'kategoriKriteria',
            'subKriteria',
            'itemSubKriteria',
            'isDropdownAvailable',
            'jenisPembayaranList',
            'isJenisPembayaranAvailable'
        ));
    }

    /**
     * Store a new document
     */
    public function store(Request $request)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        $validated = $request->validate([
            'nomor_agenda' => 'nullable|string|max:255',
            'nomor_spp' => 'required|string|max:255',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'required|string',
            'nilai_rupiah' => 'required|numeric|min:0',
            'nama_pengirim' => 'nullable|string|max:255',
            'dibayar_kepada' => 'nullable|array',
            'nomor_po' => 'nullable|array',
            'nomor_pr' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Parse tanggal_spp
            $tanggalSpp = Carbon::parse($request->tanggal_spp);

            // Get bulan and tahun from computer timestamp (Carbon::now())
            $now = Carbon::now();
            $bulanNames = [
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

            // Get nama from ID untuk field kriteria_cf, sub_kriteria, item_sub_kriteria
            $kategoriNama = $request->kategori;
            $jenisDokumenNama = $request->jenis_dokumen;
            $jenisSubPekerjaanNama = $request->jenis_sub_pekerjaan;

            try {
                if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                    $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
                    if ($kategoriKriteria) {
                        $kategoriNama = $kategoriKriteria->nama_kriteria;
                    }
                }

                if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                    $subKriteriaObj = SubKriteria::find($request->sub_kriteria);
                    if ($subKriteriaObj) {
                        $jenisDokumenNama = $subKriteriaObj->nama_sub_kriteria;
                    }
                }

                if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                    $itemSubKriteriaObj = ItemSubKriteria::find($request->item_sub_kriteria);
                    if ($itemSubKriteriaObj) {
                        $jenisSubPekerjaanNama = $itemSubKriteriaObj->nama_item_sub_kriteria;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cash_bank data for bagian store: ' . $e->getMessage());
            }

            // Create document with bagian auto-filled
            $dokumen = Dokumen::create([
                'nomor_agenda' => $request->nomor_agenda,
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $tanggalSpp,
                'bulan' => $bulanNames[$now->month],
                'tahun' => $now->year,
                'tanggal_masuk' => $now,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => str_replace(['.', ','], ['', '.'], $request->nilai_rupiah),
                'bagian' => $bagianCode, // Auto-filled from user's bagian
                'nama_pengirim' => $request->nama_pengirim ?? Auth::user()->name,
                'kebun' => $request->kebun,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'jenis_pembayaran' => $request->jenis_pembayaran,
                // Store nama (not ID) for backward compatibility
                'kategori' => $kategoriNama,
                'jenis_dokumen' => $jenisDokumenNama,
                'jenis_sub_pekerjaan' => $jenisSubPekerjaanNama,
                'status' => 'belum dikirim',
                'current_handler' => 'bagian_' . strtolower($bagianCode),
                'created_by' => 'bagian_' . strtolower($bagianCode),
            ]);

            // Create DibayarKepada records
            if ($request->has('dibayar_kepada') && is_array($request->dibayar_kepada)) {
                foreach ($request->dibayar_kepada as $nama) {
                    if (!empty($nama)) {
                        DibayarKepada::create([
                            'dokumen_id' => $dokumen->id,
                            'nama_penerima' => $nama,
                        ]);
                    }
                }
            }

            // Create PO records
            if ($request->has('nomor_po') && is_array($request->nomor_po)) {
                foreach ($request->nomor_po as $po) {
                    if (!empty($po)) {
                        DokumenPO::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_po' => $po,
                        ]);
                    }
                }
            }

            // Create PR records
            if ($request->has('nomor_pr') && is_array($request->nomor_pr)) {
                foreach ($request->nomor_pr as $pr) {
                    if (!empty($pr)) {
                        DokumenPR::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_pr' => $pr,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen berhasil dibuat.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error creating bagian document: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }

        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Get dropdown data
        $isDropdownAvailable = false;
        $selectedKriteriaCfId = null;
        $selectedSubKriteriaId = null;
        $selectedItemSubKriteriaId = null;

        try {
            $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = SubKriteria::all();
            $itemSubKriteria = ItemSubKriteria::all();
            $isDropdownAvailable = $kategoriKriteria->count() > 0;

            // Look up IDs from stored names
            if ($dokumen->kategori) {
                $found = $kategoriKriteria->firstWhere('nama_kriteria', $dokumen->kategori);
                if ($found) {
                    $selectedKriteriaCfId = $found->id_kategori_kriteria;
                }
            }

            if ($dokumen->jenis_dokumen && $selectedKriteriaCfId) {
                $found = $subKriteria->where('id_kategori_kriteria', $selectedKriteriaCfId)
                    ->firstWhere('nama_sub_kriteria', $dokumen->jenis_dokumen);
                if ($found) {
                    $selectedSubKriteriaId = $found->id_sub_kriteria;
                }
            }

            if ($dokumen->jenis_sub_pekerjaan && $selectedSubKriteriaId) {
                $found = $itemSubKriteria->where('id_sub_kriteria', $selectedSubKriteriaId)
                    ->firstWhere('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan);
                if ($found) {
                    $selectedItemSubKriteriaId = $found->id_item_sub_kriteria;
                }
            }
        } catch (\Exception $e) {
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
            \Log::error('Error fetching cash_bank data for bagian edit: ' . $e->getMessage());
        }

        $jenisPembayaranList = collect([]);
        $isJenisPembayaranAvailable = false;
        try {
            $jenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get();
            $isJenisPembayaranAvailable = $jenisPembayaranList->count() > 0;
        } catch (\Exception $e) {
        }

        return view('bagian.dokumens.editDokumen', compact(
            'dokumen',
            'bagianCode',
            'bagianName',
            'kategoriKriteria',
            'subKriteria',
            'itemSubKriteria',
            'isDropdownAvailable',
            'jenisPembayaranList',
            'isJenisPembayaranAvailable',
            'selectedKriteriaCfId',
            'selectedSubKriteriaId',
            'selectedItemSubKriteriaId'
        ));
    }

    /**
     * Update document
     */
    public function update(Request $request, Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }

        $validated = $request->validate([
            'nomor_agenda' => 'nullable|string|max:255',
            'nomor_spp' => 'required|string|max:255',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'required|string',
            'nilai_rupiah' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $tanggalSpp = Carbon::parse($request->tanggal_spp);
            $bulanNames = [
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

            // Get nama from ID untuk field kriteria_cf, sub_kriteria, item_sub_kriteria
            $kategoriNama = $request->kategori ?? $dokumen->kategori;
            $jenisDokumenNama = $request->jenis_dokumen ?? $dokumen->jenis_dokumen;
            $jenisSubPekerjaanNama = $request->jenis_sub_pekerjaan ?? $dokumen->jenis_sub_pekerjaan;

            try {
                if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                    $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
                    if ($kategoriKriteria) {
                        $kategoriNama = $kategoriKriteria->nama_kriteria;
                    }
                }

                if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                    $subKriteriaObj = SubKriteria::find($request->sub_kriteria);
                    if ($subKriteriaObj) {
                        $jenisDokumenNama = $subKriteriaObj->nama_sub_kriteria;
                    }
                }

                if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                    $itemSubKriteriaObj = ItemSubKriteria::find($request->item_sub_kriteria);
                    if ($itemSubKriteriaObj) {
                        $jenisSubPekerjaanNama = $itemSubKriteriaObj->nama_item_sub_kriteria;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cash_bank data for bagian update: ' . $e->getMessage());
            }

            $dokumen->update([
                'nomor_agenda' => $request->nomor_agenda,
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $tanggalSpp,
                'bulan' => $bulanNames[$tanggalSpp->month],
                'tahun' => $tanggalSpp->year,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => str_replace(['.', ','], ['', '.'], $request->nilai_rupiah),
                'nama_pengirim' => $request->nama_pengirim,
                'kebun' => $request->kebun,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'jenis_pembayaran' => $request->jenis_pembayaran,
                // Store nama (not ID) for backward compatibility
                'kategori' => $kategoriNama,
                'jenis_dokumen' => $jenisDokumenNama,
                'jenis_sub_pekerjaan' => $jenisSubPekerjaanNama,
            ]);

            // Update related records
            $dokumen->dokumenPos()->delete();
            $dokumen->dokumenPrs()->delete();
            $dokumen->dibayarKepadas()->delete();

            if ($request->has('dibayar_kepada') && is_array($request->dibayar_kepada)) {
                foreach ($request->dibayar_kepada as $nama) {
                    if (!empty($nama)) {
                        DibayarKepada::create([
                            'dokumen_id' => $dokumen->id,
                            'nama_penerima' => $nama,
                        ]);
                    }
                }
            }

            if ($request->has('nomor_po') && is_array($request->nomor_po)) {
                foreach ($request->nomor_po as $po) {
                    if (!empty($po)) {
                        DokumenPO::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_po' => $po,
                        ]);
                    }
                }
            }

            if ($request->has('nomor_pr') && is_array($request->nomor_pr)) {
                foreach ($request->nomor_pr as $pr) {
                    if (!empty($pr)) {
                        DokumenPR::create([
                            'dokumen_id' => $dokumen->id,
                            'nomor_pr' => $pr,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen berhasil diperbarui.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error updating bagian document: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete document
     */
    public function destroy(Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }

        try {
            DB::beginTransaction();

            $dokumen->dokumenPos()->delete();
            $dokumen->dokumenPrs()->delete();
            $dokumen->dibayarKepadas()->delete();
            $dokumen->delete();

            DB::commit();

            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Send document - Conditional routing:
     * - New documents → Operator
     * - Returned documents (from Team Verifikasi) → directly to Team Verifikasi
     */
    public function sendToOperator(Request $request, Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();
        $isAjax = $request->ajax();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke dokumen ini'], 403);
            }
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }

        // Allow sending if document is 'belum dikirim' or 'returned_to_bidang' (dikembalikan)
        if (!in_array($dokumen->status, ['belum dikirim', 'returned_to_bidang'])) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Dokumen sudah pernah dikirim sebelumnya.'], 400);
            }
            return redirect()->back()
                ->with('error', 'Dokumen sudah pernah dikirim sebelumnya.');
        }

        // Conditional routing: if returned by Team Verifikasi, send directly back to them
        if ($dokumen->return_source === 'team_verifikasi') {
            return $this->sendBackToVerifikasi($request, $dokumen, $bagianCode);
        }

        // Normal flow: send to Operator
        try {
            DB::beginTransaction();

            $now = Carbon::now();

            // Update document status - Send to Ibu Tarapul
            $dokumen->update([
                'status' => 'menunggu_approval_keuangan',
                'current_handler' => 'operator',
                'sent_at' => $now,
            ]);

            // Create or update role data for tracking
            DokumenRoleData::updateOrCreate(
                [
                    'dokumen_id' => $dokumen->id,
                    'role_code' => 'operator',
                ],
                [
                    'received_at' => $now,
                    'received_from' => 'bagian_' . strtolower($bagianCode),
                    'processed_at' => null, // Reset for re-processing
                    'display_status' => null, // Reset so Operator sees it as new/pending
                ]
            );

            // Set pending status for Operator inbox
            $dokumen->setStatusForRole('operator', 'pending', Auth::user()->name ?? 'Bagian ' . $bagianCode);

            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil dikirim ke Bidang Keuangan dan Akutansi.',
                    'destination' => 'Bidang Keuangan',
                ]);
            }
            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen berhasil dikirim ke Bidang Keuangan dan Akutansi.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending document to Ibu Tarapul: ' . $e->getMessage());
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Gagal mengirim dokumen: ' . $e->getMessage()], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal mengirim dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Send corrected document directly back to Team Verifikasi (skip Operator)
     * This is called when a document was previously returned by Team Verifikasi
     */
    private function sendBackToVerifikasi(Request $request, Dokumen $dokumen, string $bagianCode)
    {
        $isAjax = $request->ajax();

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            // Update document status - Send directly to Team Verifikasi
            $dokumen->update([
                'status' => 'sent_to_team_verifikasi',
                'current_handler' => 'team_verifikasi',
                // return_source is cleared via unified fields below
                'resent_to_verifikasi_at' => $now,
                // Clear unified return fields (Phase 1 Opsi B)
                'return_source' => null,
                'return_reason' => null,
                'returned_at' => null,
            ]);

            // Update existing Team Verifikasi role data (reset for re-processing)
            $existingRoleData = DokumenRoleData::where('dokumen_id', $dokumen->id)
                ->where('role_code', 'team_verifikasi')
                ->first();

            if ($existingRoleData) {
                $existingRoleData->update([
                    'received_at' => $now,
                    'processed_at' => null, // Reset so they can process again
                    'received_from' => 'bagian_resend_' . strtolower($bagianCode),
                ]);
            } else {
                DokumenRoleData::create([
                    'dokumen_id' => $dokumen->id,
                    'role_code' => 'team_verifikasi',
                    'received_at' => $now,
                    'received_from' => 'bagian_resend_' . strtolower($bagianCode),
                ]);
            }

            // Set pending status for Team Verifikasi inbox
            $dokumen->setStatusForRole('team_verifikasi', 'pending', Auth::user()->name ?? 'Bagian ' . $bagianCode);

            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen yang telah diperbaiki berhasil dikirim langsung ke Team Verifikasi.',
                    'destination' => 'Team Verifikasi',
                ]);
            }
            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen yang telah diperbaiki berhasil dikirim langsung ke Team Verifikasi.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending corrected document to Team Verifikasi: ' . $e->getMessage());
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Gagal mengirim dokumen: ' . $e->getMessage()], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal mengirim dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Document tracking for bagian - only shows their documents
     */
    public function tracking(Request $request)
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // Filter by created_by to only show documents created by this bagian
        $createdByValue = 'bagian_' . strtolower($bagianCode);
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
            ->where('created_by', $createdByValue)
            ->orderBy('updated_at', 'desc');

        // General search - searches across multiple fields
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%")
                    ->orWhere('kebun', 'like', "%{$search}%")
                    ->orWhere('bagian', 'like', "%{$search}%")
                    ->orWhere('nama_pengirim', 'like', "%{$search}%")
                    ->orWhereHas('dibayarKepadas', function ($subQ) use ($search) {
                        $subQ->where('nama_penerima', 'like', "%{$search}%");
                    });
            });
        }

        // Specific filter: Nomor SPP
        if ($request->has('nomor_spp') && $request->nomor_spp) {
            $query->where('nomor_spp', 'like', "%{$request->nomor_spp}%");
        }

        // Specific filter: Nilai (with range support)
        if ($request->has('nilai_min') && $request->nilai_min) {
            $nilaiMin = floatval(str_replace(['.', ','], ['', '.'], $request->nilai_min));
            $query->where('nilai_rupiah', '>=', $nilaiMin);
        }
        if ($request->has('nilai_max') && $request->nilai_max) {
            $nilaiMax = floatval(str_replace(['.', ','], ['', '.'], $request->nilai_max));
            $query->where('nilai_rupiah', '<=', $nilaiMax);
        }

        // Specific filter: Kebun
        if ($request->has('kebun') && $request->kebun) {
            $query->where('kebun', 'like', "%{$request->kebun}%");
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $statusFilter = $request->status;
            if ($statusFilter === 'belum_dikirim') {
                $query->where('status', 'belum dikirim');
            } elseif ($statusFilter === 'terkirim') {
                $query->where('status', '!=', 'belum dikirim')
                    ->whereNull('tanggal_dibayar');
            } elseif ($statusFilter === 'sudah_dibayar') {
                $query->whereNotNull('tanggal_dibayar');
            }
        }

        $perPage = $request->get('per_page', session('bagian_tracking_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['bagian_tracking_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        return view('bagian.tracking', compact(
            'dokumens',
            'bagianCode',
            'bagianName'
        ));
    }

    /**
     * Get document detail for modal
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        return response()->json([
            'success' => true,
            'dokumen' => [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'tanggal_spp' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('Y-m-d') : null,
                'bulan' => $dokumen->bulan,
                'tahun' => $dokumen->tahun,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'status' => $dokumen->status,
                'bagian' => $dokumen->bagian,
                'nama_pengirim' => $dokumen->nama_pengirim,
                'kebun' => $dokumen->kebun,
                'no_spk' => $dokumen->no_spk,
                'tanggal_spk' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('Y-m-d') : null,
                'dokumen_pos' => $dokumen->dokumenPos ? $dokumen->dokumenPos->map(fn($po) => ['nomor_po' => $po->nomor_po])->values() : [],
                'dokumen_prs' => $dokumen->dokumenPrs ? $dokumen->dokumenPrs->map(fn($pr) => ['nomor_pr' => $pr->nomor_pr])->values() : [],
            ]
        ]);
    }
}






