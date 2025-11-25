<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use Illuminate\Support\Facades\Validator;

class DashboardPerpajakanController extends Controller
{
    public function index(){
        // Get all documents that perpajakan can see (same as dokumens() query)
        $perpajakanDocs = Dokumen::query()
            ->where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                      ->orWhere('status', 'sent_to_akutansi');
            })
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
            ->where(function($doc) {
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
        $dokumenTerbaru = Dokumen::query()
            ->where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                      ->orWhere('status', 'sent_to_akutansi');
            })
            ->with(['dokumenPos', 'dokumenPrs'])
            ->orderByRaw("CASE
                WHEN current_handler = 'perpajakan' AND status != 'sent_to_akutansi' THEN 1
                WHEN status = 'sent_to_akutansi' THEN 2
                ELSE 3
            END")
            ->orderByDesc('sent_to_perpajakan_at')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard Perpajakan",
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

    public function dokumens(){
        // Perpajakan sees:
        // 1. Documents with current_handler = perpajakan (active documents)
        // 2. Documents that were sent to akutansi (for tracking like dokumensB)
        $dokumens = Dokumen::query()
            ->where(function ($query) {
                $query->where('current_handler', 'perpajakan')
                      ->orWhere('status', 'sent_to_akutansi');
            })
            ->with(['dokumenPos', 'dokumenPrs'])
            ->orderByRaw("CASE
                WHEN current_handler = 'perpajakan' AND status != 'sent_to_akutansi' THEN 1
                WHEN status = 'sent_to_akutansi' THEN 2
                ELSE 3
            END")
            ->orderByDesc('sent_to_perpajakan_at')
            ->orderByDesc('updated_at')
            ->get();

        $data = array(
            "title" => "Daftar Dokumen Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
        );
        return view('perpajakan.dokumens.daftarPerpajakan', $data);
    }

    public function editDokumen(Dokumen $dokumen){
        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return redirect()->route('dokumensPerpajakan.index')
                ->with('error', 'Dokumen ini tidak dapat diakses.');
        }

        $data = array(
            "title" => "Edit Dokumen Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumen' => $dokumen,
        );
        return view('perpajakan.dokumens.editPerpajakan', $data);
    }

    public function updateDokumen(Request $request, Dokumen $dokumen){
        $id = $dokumen->id;

        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return redirect()->route('dokumensPerpajakan.index')
                ->with('error', 'Dokumen ini tidak dapat diakses.');
        }

        $validator = Validator::make($request->all(), [
            'nomor_agenda' => 'required|string|unique:dokumens,nomor_agenda,' . $id,
            'bulan' => 'required|string',
            'tahun' => 'required|integer|min:2020|max:2030',
            'tanggal_masuk' => 'required|date',
            'nomor_spp' => 'required|string',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'nullable|string',
            'nilai_rupiah' => 'required',
            'kategori' => 'nullable|in:KONTRAK,LANGGANAN,BIAYA LAINNYA',
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
            // Perpajakan fields
            'npwp' => 'nullable|string',
            'status_perpajakan' => 'nullable|in:sedang_diproses,selesai',
            'no_faktur' => 'nullable|string',
            'tanggal_faktur' => 'nullable|date',
            'tanggal_selesai_verifikasi_pajak' => 'nullable|date',
            'jenis_pph' => 'nullable|string',
            'dpp_pph' => 'nullable|string',
            'ppn_terhutang' => 'nullable|string',
            'link_dokumen_pajak' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada input data. Silakan periksa kembali.');
        }

        try {
            \DB::beginTransaction();

            // Format nilai rupiah
            $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
            if (empty($nilaiRupiah) || $nilaiRupiah <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nilai rupiah harus lebih dari 0.');
            }
            $nilaiRupiah = (float) $nilaiRupiah;

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

            // Update dokumen
            // Note: kategori and jenis_dokumen are required fields, so use existing value if not provided
            $updateData = [
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'tanggal_masuk' => $request->tanggal_masuk,
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp ?? $dokumen->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                'kategori' => !empty($request->kategori) ? $request->kategori : $dokumen->kategori,
                'jenis_dokumen' => !empty($request->jenis_dokumen) ? $request->jenis_dokumen : $dokumen->jenis_dokumen,
                'jenis_sub_pekerjaan' => $request->jenis_sub_pekerjaan,
                'jenis_pembayaran' => $request->jenis_pembayaran,
                'dibayar_kepada' => $request->dibayar_kepada,
                'no_berita_acara' => $request->no_berita_acara,
                'tanggal_berita_acara' => $request->tanggal_berita_acara,
                'no_spk' => $request->no_spk,
                'tanggal_spk' => $request->tanggal_spk,
                'tanggal_berakhir_spk' => $request->tanggal_berakhir_spk,
                'keterangan' => $request->keterangan,
                // Perpajakan fields
                'npwp' => $request->npwp,
                'status_perpajakan' => $request->status_perpajakan,
                'no_faktur' => $request->no_faktur,
                'tanggal_faktur' => $request->tanggal_faktur,
                'tanggal_selesai_verifikasi_pajak' => $request->tanggal_selesai_verifikasi_pajak,
                'jenis_pph' => $request->jenis_pph,
                'dpp_pph' => $dppPph,
                'ppn_terhutang' => $ppnTerhutang,
                'link_dokumen_pajak' => $request->link_dokumen_pajak,
            ];

            $dokumen->update($updateData);

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

            return redirect()->route('dokumensPerpajakan.index')
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

        // Validate
        $validator = Validator::make($request->all(), [
            'deadline_days' => 'required|integer|min:1|max:30',
            'deadline_note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $deadlineDays = (int) $request->deadline_days;
            $deadlineAt = now()->addDays($deadlineDays);

            $dokumen->update([
                'deadline_perpajakan_at' => $deadlineAt,
                'deadline_perpajakan_days' => $deadlineDays,
                'deadline_perpajakan_note' => $request->deadline_note,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deadline berhasil ditetapkan',
                'deadline' => $deadlineAt->format('d M Y, H:i'),
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
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load required relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        // Return HTML partial for detail view
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
            'Kategori' => $dokumen->kategori ?? '-',
            'Jenis Dokumen' => $dokumen->jenis_dokumen ?? '-',
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
            'Dibayar Kepada' => $dokumen->dibayar_kepada ?? '-',
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

        return sprintf('<a href="%s" target="_blank" class="tax-document-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
            htmlspecialchars($link),
            htmlspecialchars($link)
        );
    }

    public function pengembalian(){
        // Get all documents that have been returned by perpajakan
        $dokumens = Dokumen::whereNotNull('returned_from_perpajakan_at')
            ->where('status', 'returned_to_department')
            ->with(['dokumenPos', 'dokumenPrs'])
            ->orderBy('returned_from_perpajakan_at', 'desc')
            ->paginate(10);

        // Calculate statistics
        $totalReturned = Dokumen::whereNotNull('returned_from_perpajakan_at')
            ->where('status', 'returned_to_department')
            ->count();

        $totalPending = Dokumen::where('current_handler', 'ibuB')
            ->where('status', 'returned_to_department')
            ->whereNull('processed_perpajakan_at')
            ->count();

        $totalCompleted = Dokumen::whereNotNull('returned_from_perpajakan_at')
            ->where('status', 'returned_to_department')
            ->whereNotNull('processed_perpajakan_at')
            ->count();

        $data = array(
            "title" => "Daftar Pengembalian Dokumen Perpajakan ke team verifikasi",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
            'dokumens' => $dokumens,
            'totalReturned' => $totalReturned,
            'totalPending' => $totalPending,
            'totalCompleted' => $totalCompleted,
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

            // Update document status to returned to ibuB
            $dokumen->update([
                'status' => 'returned_to_department',
                'current_handler' => 'ibuB',
                'returned_from_perpajakan_at' => now(),
                'alasan_pengembalian' => $request->return_reason,
            ]);

            // Reset tax status since document is being returned
            $dokumen->update([
                'status_perpajakan' => null,
                'tanggal_selesai_verifikasi_pajak' => null,
                'deadline_perpajakan_at' => null,
                'deadline_perpajakan_note' => null,
                // Keep other tax fields for historical reference
            ]);

            // Clear sent timestamps
            $dokumen->update([
                'sent_to_perpajakan_at' => null,
                'sent_to_ibub_at' => now(), // Mark when sent back to ibuB
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikembalikan ke IbuB.'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error returning document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengembalikan dokumen.'
            ], 500);
        }
    }

    /**
     * Send document to akutansi with perpajakan data (like dokumensB flow)
     */
    public function sendToAkutansi(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is perpajakan
        if ($dokumen->current_handler !== 'perpajakan') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim. Dokumen tidak sedang ditangani oleh perpajakan.'
            ], 403);
        }

        // Check if perpajakan status is selesai
        if ($dokumen->status_perpajakan !== 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim. Status perpajakan harus "Selesai" terlebih dahulu.'
            ], 422);
        }

        try {
            \DB::beginTransaction();

            // Update document to keep it in perpajakan view but mark as sent
            $dokumen->update([
                'status' => 'sent_to_akutansi', // Keep document in perpajakan view
                'current_handler' => 'akutansi', // Move to akutansi for processing
                'sent_to_akutansi_at' => now(), // Mark timestamp when sent to akutansi
                'tanggal_selesai_verifikasi_pajak' => now(), // Mark completion timestamp
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim ke Akutansi. Dokumen tetap ditampilkan di halaman Perpajakan dengan status "Sudah terkirim ke Akutansi".'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error sending document to akutansi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen ke Akutansi.'
            ], 500);
        }
    }

    public function diagram(){
        // Get filter year (default to current year)
        $selectedYear = request('year', date('Y'));
        
        // Get all documents handled by perpajakan for the selected year
        $allDokumens = Dokumen::where(function($query) {
                $query->where('current_handler', 'perpajakan')
                      ->orWhereNotNull('sent_to_perpajakan_at');
            })
            ->whereNotNull('nomor_agenda')
            ->whereYear('created_at', $selectedYear)
            ->get();
        
        // Chart 1: Statistik Jumlah Dokumen per Bulan
        $monthlyData = [];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            })->count();
        }
        
        // Chart 2: Statistik Keterlambatan Dokumen (based on deadline_perpajakan_at)
        $keterlambatanData = [];
        $ketepatanData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthDocs = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            });
            
            $terlambat = 0;
            $tepat = 0;
            $totalWithDeadline = 0;
            
            foreach ($monthDocs as $doc) {
                if ($doc->deadline_perpajakan_at) {
                    $totalWithDeadline++;
                    $now = now();
                    if ($doc->deadline_perpajakan_at->lt($now)) {
                        // Check if processed/completed
                        if ($doc->processed_perpajakan_at) {
                            if ($doc->processed_perpajakan_at->gt($doc->deadline_perpajakan_at)) {
                                $terlambat++;
                            } else {
                                $tepat++;
                            }
                        } else {
                            // Not completed yet, check if overdue
                            $terlambat++;
                        }
                    } else {
                        // Not yet overdue
                        $tepat++;
                    }
                }
            }
            
            $keterlambatanData[] = $totalWithDeadline > 0 ? round(($terlambat / $totalWithDeadline) * 100, 1) : 0;
            $ketepatanData[] = $totalWithDeadline > 0 ? round(($tepat / $totalWithDeadline) * 100, 1) : 0;
        }
        
        // Chart 3: Statistik Jumlah Dokumen Selesai per Bulan
        $selesaiData = [];
        $tidakSelesaiData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthDocs = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            });
            
            // Selesai = sudah diproses (processed_perpajakan_at tidak null)
            $selesai = $monthDocs->filter(function($doc) {
                return $doc->processed_perpajakan_at !== null;
            })->count();
            
            $tidakSelesai = $monthDocs->filter(function($doc) {
                return $doc->processed_perpajakan_at === null;
            })->count();
            
            $selesaiData[] = $selesai;
            $tidakSelesaiData[] = $tidakSelesai;
        }
        
        // Get available years for filter
        $availableYears = Dokumen::whereNotNull('nomor_agenda')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        $data = array(
            "title" => "Diagram Perpajakan",
            "module" => "perpajakan",
            "menuDashboard" => "",
            'menuDokumen' => '',
            'menuDiagram' => 'Active',
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'monthlyData' => $monthlyData,
            'keterlambatanData' => $keterlambatanData,
            'ketepatanData' => $ketepatanData,
            'selesaiData' => $selesaiData,
            'tidakSelesaiData' => $tidakSelesaiData,
            'months' => $months,
        );
        return view('perpajakan.diagramPerpajakan', $data);
    }

    /**
     * Check for new documents assigned to perpajakan
     */
    public function checkUpdates(Request $request)
    {
        try {
            $lastChecked = $request->input('last_checked', 0);

            // Cek dokumen baru yang dikirim ke perpajakan
            $newDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'perpajakan')
                          ->orWhere('status', 'sent_to_perpajakan');
                })
                ->where('sent_to_perpajakan_at', '>', date('Y-m-d H:i:s', $lastChecked))
                ->latest('sent_to_perpajakan_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'perpajakan')
                          ->orWhere('status', 'sent_to_perpajakan');
                })->count();

            return response()->json([
                'has_updates' => $newDocuments->count() > 0,
                'new_count' => $newDocuments->count(),
                'total_documents' => $totalDocuments,
                'new_documents' => $newDocuments->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => $doc->uraian_spp,
                        'nilai_rupiah' => $doc->nilai_rupiah,
                        'status' => $doc->status,
                        'status_perpajakan' => $doc->status_perpajakan,
                        'sent_at' => $doc->sent_to_perpajakan_at?->format('d/m/Y H:i'),
                        'deadline_at' => $doc->deadline_at?->format('d/m/Y H:i'),
                    ];
                }),
                'last_checked' => time()
            ]);

        } catch (\Exception $e) {
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
        $query = Dokumen::where('created_by', 'ibuA')
            ->with(['dokumenPos', 'dokumenPrs']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian && in_array($selectedBagian, array_keys(self::BAGIAN_LIST))) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && trim($request->search) !== '') {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                // Always prioritize nomor agenda for exact-style lookup
                $q->where('nomor_agenda', 'like', '%' . $search . '%');

                // Only broaden search to other columns when the keyword contains non-numeric characters
                if (preg_match('/\D/', $search)) {
                    $q->orWhere('nomor_spp', 'like', '%' . $search . '%')
                      ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                      ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                }
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
            "module" => "perpajakan",
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

        return view('perpajakan.rekapan', $data);
    }

    /**
     * Get statistics for rekapan documents (same as IbuB)
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
}
