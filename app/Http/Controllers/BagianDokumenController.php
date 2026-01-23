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

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Year filter
        if ($request->has('tahun') && $request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $perPage = $request->get('per_page', 10);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        return view('bagian.dokumens.daftarDokumen', compact(
            'dokumens',
            'bagianCode',
            'bagianName'
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
            'nomor_agenda' => 'required|string|max:255',
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

            // Parse tanggal_spp to get bulan and tahun
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
                'bulan' => $bulanNames[$tanggalSpp->month],
                'tahun' => $tanggalSpp->year,
                'tanggal_masuk' => Carbon::now(),
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
                ->with('success', 'Dokumen berhasil dOperatort.');

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
            'nomor_agenda' => 'required|string|max:255',
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
     * Send document to Ibu Tarapul (Bidang Keuangan dan Akutansi)
     */
    public function sendToOperator(Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }

        if ($dokumen->status !== 'belum dikirim') {
            return redirect()->back()
                ->with('error', 'Dokumen sudah pernah dikirim sebelumnya.');
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            // Update document status - Send to Ibu Tarapul
            $dokumen->update([
                'status' => 'menunggu_approval_keuangan',
                'current_handler' => 'operator',
                'sent_at' => $now,
            ]);

            // Create role data for tracking
            DokumenRoleData::create([
                'dokumen_id' => $dokumen->id,
                'role_code' => 'operator',
                'received_at' => $now,
                'received_from' => 'bagian_' . strtolower($bagianCode),
            ]);

            // Set pending status for Operator inbox
            $dokumen->setStatusForRole('operator', 'pending', Auth::user()->name ?? 'Bagian ' . $bagianCode);

            DB::commit();

            return redirect()->route('bagian.documents.index')
                ->with('success', 'Dokumen berhasil dikirim ke Bidang Keuangan dan Akutansi.');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending document to Ibu Tarapul: ' . $e->getMessage());
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

        // Search
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
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






