<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SetDeadlineRequest;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\Bidang;
use App\Models\DibayarKepada;
use App\Events\DocumentReturned;
use App\Helpers\SearchHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class DashboardBController extends Controller
{
    public function index(){
        // Get statistics for IbuB (only documents with current_handler = ibuB)

        // 1. Total dokumen - semua dokumen yang terlihat oleh ibuB (same as dokumens() query)
        $totalDokumen = Dokumen::where(function($q) {
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
        $dokumenTerbaru = Dokumen::where(function($q) {
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
            "title" => "Dashboard B",
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

    public function dokumens(Request $request){
        // IbuB sees:
        // 1. Documents with current_handler = ibuB (active documents) - including approved via universal approval
        // 2. Documents with status sedang_diproses and current_handler = ibuB (from universal approval)
        // 3. Documents that were sent to perpajakan/akutansi (for tracking)
        // Exclude documents that are returned to bidang (they should appear in pengembalian ke bidang page)
        // Exclude pending approval documents (they should use daftar masuk dokumen)
        // Optimized query - only load essential columns for list view
        $query = Dokumen::where(function($q) {
                $q->where('current_handler', 'ibuB')
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'sedang_diproses')
                            ->where('current_handler', 'ibuB');
                  })
                  ->orWhereIn('status', ['sent_to_perpajakan', 'sent_to_akutansi']);
            })
            ->where('status', '!=', 'returned_to_bidang')
            ->orderByRaw("
                CASE
                    WHEN status = 'sedang_diproses' AND current_handler = 'ibuB' THEN 1
                    WHEN current_handler = 'ibuB' THEN 2
                    ELSE 3
                END ASC,
                COALESCE(sent_to_ibub_at, updated_at, created_at) DESC
            ")
            ->select([
                'id', 'nomor_agenda', 'nomor_spp', 'uraian_spp', 'nilai_rupiah',
                'status', 'created_at', 'sent_to_ibub_at', 'tanggal_masuk', 'tanggal_spp',
                'keterangan', 'alasan_pengembalian', 'deadline_at', 'deadline_days', 'deadline_note',
                'current_handler', 'bulan', 'tahun', 'kategori', 'kebun', 'jenis_dokumen',
                'updated_at', 'tanggal_spk', 'tanggal_berakhir_spk', 'no_spk', 'nomor_mirror',
                'nama_pengirim', 'jenis_pembayaran', 'dibayar_kepada', 'no_berita_acara',
                'tanggal_berita_acara'
            ]);

        // Enhanced search functionality - search across all relevant fields
        if ($request->has('search') && !empty($request->search) && trim((string)$request->search) !== '') {
            $search = trim((string)$request->search);
            $query->where(function($q) use ($search) {
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
            ->orWhereHas('dibayarKepadas', function($q) use ($search) {
                $q->where('nama_penerima', 'like', '%' . $search . '%');
            });
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Use eager loading for relations to prevent N+1 queries
        $dokumens = $query->with(['dibayarKepadas'])
            ->withCount([
                'dokumenPos',
                'dokumenPrs'
            ])->paginate(10);

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
        if ($request->has('search') && !empty($request->search) && trim((string)$request->search) !== '' && $dokumens->total() == 0) {
            $searchTerm = trim((string)$request->search);
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
        $selectedColumns = array_filter($selectedColumns, function($col) {
            return $col !== 'status' && $col !== 'keterangan';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array
        
        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['ibub_dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from session if available, and filter out 'status'
            $selectedColumns = session('ibub_dokumens_table_columns', [
                'nomor_agenda',
                'nomor_spp',
                'tanggal_masuk',
                'nilai_rupiah',
                'nomor_mirror'
            ]);
            // Filter out 'status' and 'keterangan' if they exist in session
            $selectedColumns = array_filter($selectedColumns, function($col) {
                return $col !== 'status' && $col !== 'keterangan';
            });
            $selectedColumns = array_values($selectedColumns);
            // Update session to remove 'status' if it was present
            session(['ibub_dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Dokumen B",
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

    public function createDokumen(){
        $data = array(
            "title" => "Tambah Dokumen B",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        return view('ibuB.dokumens.tambahDokumenB', $data);
    }

    public function storeDokumen(Request $request){
        // Implementation for storing document
        return redirect()->route('dokumensB.index')->with('success', 'Dokumen berhasil ditambahkan');
    }

    public function editDokumen(Dokumen $dokumen){
        // Only allow editing if current_handler is ibuB
        if ($dokumen->current_handler !== 'ibuB') {
            return redirect()->route('dokumensB.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit dokumen ini.');
        }

        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        $data = array(
            "title" => "Edit Dokumen",
            "module" => "ibuB",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumen' => $dokumen,
        );
        return view('ibuB.dokumens.editDokumenB', $data);
    }

    public function updateDokumen(Request $request, Dokumen $dokumen){
        // Only allow updating if current_handler is ibuB
        if ($dokumen->current_handler !== 'ibuB') {
            return redirect()->route('dokumensB.index')
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
            'kategori' => 'required|string|in:Investasi on farm,Investasi off farm,Exploitasi',
            'jenis_dokumen' => 'required|string',
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
            'kategori.in' => 'Kategori tidak valid. Pilih salah satu dari opsi yang tersedia.',
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

            // Update dokumen - IMPORTANT: Status is NOT updated here
            $dokumen->update([
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'tanggal_masuk' => $request->tanggal_masuk,
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                'kategori' => $request->kategori,
                'jenis_dokumen' => $request->jenis_dokumen,
                'jenis_sub_pekerjaan' => $request->jenis_sub_pekerjaan,
                'jenis_pembayaran' => $request->jenis_pembayaran,
                'kebun' => $request->kebun,
                'dibayar_kepada' => $request->dibayar_kepada,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                // Status is NOT updated - only changes through workflow
                'keterangan' => $request->keterangan,
            ]);

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
                                 $dokumen->returned_from_perpajakan_at ||
                                 $dokumen->department_returned_at);

            // Also check referer to be more accurate
            $referer = request()->header('referer');
            $fromPengembalian = $referer && str_contains($referer, 'pengembalian-dokumensB');

            if ($isReturnedDocument || $fromPengembalian) {
                session()->flash('success', 'Dokumen berhasil diperbarui.');
                return redirect()->route('pengembalianB.index');
            } else {
                session()->flash('success', 'Dokumen berhasil diperbarui.');
                return redirect()->route('dokumensB.index');
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
    public function getDocumentDetail(Dokumen $dokumen): \Illuminate\Http\Response
    {
        try {
            Log::info('Accessing document detail', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'status' => $dokumen->status,
                'user_agent' => request()->userAgent(),
            ]);

            // Allow access if document was handled by ibuB or sent from ibuB
            $allowedHandlers = ['ibuB', 'perpajakan', 'akutansi'];
            $allowedStatuses = ['sent_to_ibub', 'sent_to_perpajakan', 'sent_to_akutansi', 'approved_ibub', 'returned_to_department', 'returned_to_bidang'];

            if (!in_array($dokumen->current_handler, $allowedHandlers) && !in_array($dokumen->status, $allowedStatuses)) {
                Log::warning('Access denied for document detail', [
                    'document_id' => $dokumen->id,
                    'current_handler' => $dokumen->current_handler,
                    'status' => $dokumen->status,
                ]);

                return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
            }

            // Load required relationships with error handling
            try {
                $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);
            } catch (\Exception $e) {
                Log::error('Failed to load document relationships', [
                    'document_id' => $dokumen->id,
                    'error' => $e->getMessage(),
                ]);

                return response('<div class="text-center p-4 text-danger">Error loading document data</div>', 500);
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
            ]);

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
            'Kategori' => $dokumen->kategori,
            'Jenis Dokumen' => $dokumen->jenis_dokumen,
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
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

        // Dates
        $dates = [
            'Tanggal Dikirim ke Ibu Yuni' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d-m-Y H:i') : null,
            'Tanggal Diproses' => $dokumen->processed_at ? $dokumen->processed_at->format('d-m-Y H:i') : null,
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

        public function destroyDokumen($id){
        // Implementation for deleting document
        return redirect()->route('dokumensB.index')->with('success', 'Dokumen berhasil dihapus');
    }

    public function pengembalian(Request $request){
        // IbuB sees documents that were returned to department (unified return page)
        $query = \App\Models\Dokumen::with(['dokumenPos', 'dokumenPrs'])
            ->where('current_handler', 'ibuB')
            ->where(function($q) {
                $q->where('status', 'returned_to_department')
                  ->orWhere(function($subQ) {
                      $subQ->whereNotNull('returned_from_perpajakan_at')
                            ->where('pengembalian_awaiting_fix', true); // Hanya yang masih menunggu perbaikan
                  });
            })
            ->orderByRaw('COALESCE(returned_from_perpajakan_at, department_returned_at) DESC');

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('target_department', $request->department);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%');
            });
        }

        $dokumens = $query->paginate(10);

        // Get statistics
        $totalReturnedToDept = \App\Models\Dokumen::where('current_handler', 'ibuB')
            ->where(function($q) {
                $q->where('status', 'returned_to_department')
                  ->orWhere(function($subQ) {
                      $subQ->whereNotNull('returned_from_perpajakan_at')
                            ->where('pengembalian_awaiting_fix', true); // Hanya yang masih menunggu perbaikan
                  });
            })
            ->count();

        $totalByDept = [
            'perpajakan' => \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where(function($q) {
                    $q->where('status', 'returned_to_department')
                      ->where('target_department', 'perpajakan')
                      ->orWhere(function($subQ) {
                          $subQ->whereNotNull('returned_from_perpajakan_at')
                              ->where('pengembalian_awaiting_fix', true);
                      });
                })
                ->count(),
            'akutansi' => \App\Models\Dokumen::where('current_handler', 'ibuB')
                ->where('status', 'returned_to_department')
                ->where('target_department', 'akutansi')
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

    public function diagram(){
        $data = array(
            "title" => "Diagram B",
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
            if (!$dokumen->returned_from_perpajakan_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen ini bukan dokumen yang dikembalikan dari perpajakan.'
                ], 403);
            }

            \DB::beginTransaction();

            // Update document data with current values from ibuB
            $dokumen->update([
                'current_handler' => 'perpajakan',
                'status' => 'sent_to_perpajakan', // Langsung kirim ke perpajakan
                'pengembalian_awaiting_fix' => false, // Tidak lagi menunggu perbaikan
                'returned_from_perpajakan_fixed_at' => now(), // Tandai sebagai sudah diperbaiki
                'sent_to_perpajakan_at' => now(), // Tandai waktu pengiriman ke perpajakan
                'processed_at' => now(),
                // Reset perpajakan deadline to null so document will be locked until perpajakan sets deadline
                'deadline_perpajakan_at' => null,
                'deadline_perpajakan_days' => null,
                'deadline_perpajakan_note' => null,
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
     * Send document to next handler (Perpajakan or Akutansi)
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
                'next_handler' => 'required|in:perpajakan,akutansi'
            ]);

            \DB::beginTransaction();

            $updateData = [
                'current_handler' => $request->next_handler,
                'processed_at' => now(),
                'status' => 'sent_to_' . $request->next_handler,
            ];

            // Set specific timestamp based on destination
            // Note: Deadline will be set by the destination department (perpajakan/akutansi) themselves
            if ($request->next_handler === 'perpajakan') {
                $updateData['sent_to_perpajakan_at'] = now();
                // Reset deadline_at to null so document will be locked until perpajakan sets deadline
                $updateData['deadline_at'] = null;
                $updateData['deadline_days'] = null;
                $updateData['deadline_note'] = null;
                // Also reset perpajakan-specific deadline fields
                $updateData['deadline_perpajakan_at'] = null;
                $updateData['deadline_perpajakan_days'] = null;
                $updateData['deadline_perpajakan_note'] = null;
            } elseif ($request->next_handler === 'akutansi') {
                $updateData['sent_to_akutansi_at'] = now();
                // Reset deadline_at to null so document will be locked until akutansi sets deadline
                $updateData['deadline_at'] = null;
                $updateData['deadline_days'] = null;
                $updateData['deadline_note'] = null;
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
            Log::info('=== SET DEADLINE REQUEST START ===', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'deadline_exists' => $dokumen->deadline_at ? true : false,
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

            if ($dokumen->deadline_at) {
                Log::warning('Deadline set failed - Deadline already exists', [
                    'document_id' => $dokumen->id,
                    'existing_deadline' => $dokumen->deadline_at,
                    'user_role' => Auth::user()?->role
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen sudah memiliki deadline. Deadline tidak dapat diubah.',
                    'debug_info' => [
                        'existing_deadline' => $dokumen->deadline_at
                    ]
                ], 403);
            }

            $validStatuses = ['sent_to_ibub', 'approved_data_sudah_terkirim', 'menunggu_approved_pengiriman'];
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

            $updateData = [
                'deadline_at' => now()->addDays($deadlineDays),
                'deadline_days' => $deadlineDays,
                'deadline_note' => $deadlineNote,
                'status' => 'sedang diproses',
                'processed_at' => now(),
            ];

            // Update using transaction
            DB::transaction(function () use ($dokumen, $updateData) {
                $dokumen->update($updateData);
            });

            // Log activity: deadline diatur oleh Ibu Yuni
            try {
                \App\Helpers\ActivityLogHelper::logDeadlineSet(
                    $dokumen->fresh(),
                    'ibuB',
                    [
                        'deadline_days' => $deadlineDays,
                        'deadline_at' => $dokumen->fresh()->deadline_at?->format('Y-m-d H:i:s'),
                        'deadline_note' => $deadlineNote,
                    ]
                );
            } catch (\Exception $logException) {
                \Log::error('Failed to log deadline set: ' . $logException->getMessage());
            }

            Log::info('Deadline successfully set', [
                'document_id' => $dokumen->id,
                'deadline_days' => $deadlineDays,
                'deadline_at' => $dokumen->fresh()->deadline_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Deadline berhasil ditetapkan ({$deadlineDays} hari). Dokumen sekarang terbuka untuk diproses.",
                'deadline' => $dokumen->fresh()->deadline_at?->format('d-m-Y H:i'),
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
                'processed_at' => now(),
            ];

            // Add deadline if provided
            if ($request->deadline_days) {
                $updateData['deadline_at'] = now()->addDays((int)$request->deadline_days);
                $updateData['deadline_days'] = (int)$request->deadline_days;
                $updateData['deadline_note'] = $request->deadline_note;
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
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%');
            });
        }

        // Get paginated results
        $dokumens = $query->select([
            'id', 'nomor_agenda', 'nomor_spp', 'uraian_spp', 'nilai_rupiah',
            'target_bidang', 'bidang_returned_at', 'bidang_return_reason',
            'created_at', 'updated_at', 'bulan', 'tahun'
        ])->paginate(10);

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
            // Only allow if current_handler is ibuB
            if ($dokumen->current_handler !== 'ibuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah status dokumen ini.'
                ], 403);
            }

            // Validate status
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ], [
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid. Pilih approved atau rejected.'
            ]);

            $newStatus = $request->status === 'approved' ? 'approved_ibub' : 'rejected_ibub';

            \DB::beginTransaction();

            // Update document status
            $dokumen->update([
                'status' => $newStatus,
                'processed_at' => now(),
            ]);

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
                broadcast(new \App\Events\DocumentAccepted($dokumen, 'ibuB'));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast acceptance: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diterima dan masuk ke sistem Ibu Yuni.'
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
                broadcast(new \App\Events\DocumentRejected($dokumen, 'ibuB', $request->rejection_reason));
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
        $query = Dokumen::where('created_by', 'ibuA')
            ->with(['dokumenPos', 'dokumenPrs']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian && in_array($selectedBagian, array_keys(self::BAGIAN_LIST))) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                  ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
            });
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        $dokumens = $query->latest('tanggal_masuk')->paginate(10);

        // Get statistics
        $statistics = $this->getRekapanStatistics($selectedBagian);

        $data = array(
            "title" => "Rekapan Dokumen",
            "module" => "ibuB",
            "menuDokumen" => "active",
            "menuRekapan" => "active",
            "menuDaftarDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "statistics" => $statistics,
            "bagianList" => self::BAGIAN_LIST,
            "selectedBagian" => $selectedBagian,
        );

        return view('ibuB.dokumens.rekapan', $data);
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
     * Helper method to safely escape HTML content with type casting
     */
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
            return number_format($value, 2, '.', ',');
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
        $baseQuery = Dokumen::where(function($q) use ($handler) {
            $q->where('current_handler', $handler)
              ->orWhere(function($subQ) {
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
        $fields = ['nomor_agenda', 'nomor_spp', 'uraian_spp', 'nama_pengirim', 'bagian', 
                   'kategori', 'jenis_dokumen', 'no_berita_acara', 'no_spk', 
                   'nomor_mirror', 'nomor_miro', 'keterangan', 'dibayar_kepada'];
        
        foreach ($fields as $field) {
            $values = $baseQuery->whereNotNull($field)
                ->distinct()
                ->pluck($field)
                ->filter()
                ->toArray();
            $allValues = $allValues->merge($values);
        }
        
        // Get from dibayarKepadas relation
        $dibayarKepadaQuery = DibayarKepada::whereHas('dokumen', function($q) use ($handler, $year) {
            $q->where(function($subQ) use ($handler) {
                $subQ->where('current_handler', $handler)
                     ->orWhere(function($subSubQ) {
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
}

