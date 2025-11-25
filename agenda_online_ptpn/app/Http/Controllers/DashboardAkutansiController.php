<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use Illuminate\Support\Facades\DB;

class DashboardAkutansiController extends Controller
{
    public function index(){
        // Get all documents that have been assigned to akutansi at any point
        $akutansiDocs = Dokumen::where(function($query) {
            $query->where('current_handler', 'akutansi')
                  ->orWhere('status', 'sent_to_akutansi');
        })->get();

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
            ->where(function($doc) {
                return in_array($doc->status, ['returned_to_ibua', 'returned_to_department', 'dikembalikan']);
            })
            ->count();

        // Total Dikirim: Documents that have been completed and are no longer handled by akutansi
        $totalDikirim = Dokumen::where('status', 'selesai')
            ->where(function($query) {
                $query->where('current_handler', '!=', 'akutansi')
                      ->orWhereNull('current_handler');
            })
            ->where(function($query) {
                $query->where('status', 'sent_to_akutansi')
                      ->orWhere('current_handler', 'akutansi');
            })
            ->count();

        // Get latest documents currently handled by akutansi
        $dokumenTerbaru = Dokumen::where('current_handler', 'akutansi')
            ->with(['dokumenPos', 'dokumenPrs'])
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard Akutansi",
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

            // Cek semua dokumen akutansi yang baru dikirim
            // Filter dokumen yang baru dikirim ke akutansi setelah lastChecked
            // Use updated_at for comparison since it's updated when document is sent
            $newDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'akutansi')
                          ->where('status', 'sent_to_akutansi');
                })
                ->where('updated_at', '>', $lastCheckedDate)
                ->latest('updated_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'akutansi')
                          ->orWhere('status', 'sent_to_akutansi');
                })->count();

            return response()->json([
                'has_updates' => $newDocuments->count() > 0,
                'new_count' => $newDocuments->count(),
                'total_documents' => $totalDocuments,
                'new_documents' => $newDocuments->map(function($doc) {
                    // Use sent_to_akutansi_at if available, otherwise use updated_at
                    $sentAt = $doc->sent_to_akutansi_at 
                        ? $doc->sent_to_akutansi_at->format('d/m/Y H:i')
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
            return response()->json([
                'error' => true,
                'message' => 'Failed to check updates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dokumens(){
        // Akutansi sees:
        // 1. Documents currently handled by Akutansi (active)
        // 2. Documents that have been sent to Akutansi (tracking)
        // 3. Documents that have been sent to Pembayaran (tracking - like perpajakan->akutansi)
        $dokumens = Dokumen::where(function($query) {
                $query->where('current_handler', 'akutansi')
                      ->orWhere('status', 'sent_to_akutansi')
                      ->orWhere('status', 'sent_to_pembayaran');
            })
            ->with(['dokumenPos', 'dokumenPrs'])
            ->orderByRaw("CASE
                WHEN current_handler = 'akutansi' AND status = 'sent_to_akutansi' AND deadline_at IS NULL THEN 1
                WHEN current_handler = 'akutansi' AND deadline_at IS NOT NULL THEN 2
                WHEN status = 'sent_to_pembayaran' THEN 3
                ELSE 4
            END")
            ->orderByDesc('updated_at')
            ->get();

        $data = array(
            "title" => "Daftar Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
        );
        return view('akutansi.dokumens.daftarAkutansi', $data);
    }

    public function createDokumen(){
        $data = array(
            "title" => "Tambah Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        return view('akutansi.dokumens.tambahAkutansi', $data);
    }

    public function storeDokumen(Request $request){
        // Implementation for storing document
        return redirect()->route('dokumensAkutansi.index')->with('success', 'Akutansi berhasil ditambahkan');
    }

    public function editDokumen($id){
        $data = array(
            "title" => "Edit Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
        );
        return view('akutansi.dokumens.editAkutansi', $data);
    }

    public function updateDokumen(Request $request, $id){
        // Implementation for updating document
        return redirect()->route('dokumensAkutansi.index')->with('success', 'Akutansi berhasil diperbarui');
    }

    public function destroyDokumen($id){
        // Implementation for deleting document
        return redirect()->route('dokumensAkutansi.index')->with('success', 'Akutansi berhasil dihapus');
    }

    /**
     * Set deadline for Akutansi to unlock document processing
     */
    public function setDeadline(Request $request, Dokumen $dokumen)
    {
        // Only allow if document is currently with Akutansi and still locked
        if ($dokumen->current_handler !== 'akutansi' || $dokumen->deadline_at) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak valid untuk menetapkan deadline.'
            ], 403);
        }

        $validated = $request->validate([
            'deadline_days' => 'required|integer|min:1|max:14',
            'deadline_note' => 'nullable|string|max:500',
        ], [
            'deadline_days.required' => 'Periode deadline wajib dipilih.',
            'deadline_days.min' => 'Deadline minimal 1 hari.',
            'deadline_days.max' => 'Deadline maksimal 14 hari.',
            'deadline_note.max' => 'Catatan maksimal 500 karakter.',
        ]);

        try {
            $deadlineDays = (int) $validated['deadline_days'];
            $deadlineAt = now()->addDays($deadlineDays);

            $dokumen->update([
                'deadline_at' => $deadlineAt,
                'deadline_days' => $deadlineDays,
                'deadline_note' => $validated['deadline_note'] ?? null,
                'status' => 'sedang diproses',
                'processed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deadline berhasil ditetapkan. Dokumen sekarang terbuka untuk diproses.',
                'deadline' => $deadlineAt->format('d M Y, H:i'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error setting Akutansi deadline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline.'
            ], 500);
        }
    }

    /**
     * Send document to Pembayaran (similar to perpajakan->akutansi flow)
     */
    public function sendToPembayaran(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is akutansi
        if ($dokumen->current_handler !== 'akutansi') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim. Dokumen tidak sedang ditangani oleh Akutansi.'
            ], 403);
        }

        // Check if document has been processed (deadline set and status is processing)
        if (is_null($dokumen->deadline_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen ini tidak dapat dikirim. Silakan tetapkan deadline terlebih dahulu.'
            ], 422);
        }

        try {
            \DB::beginTransaction();

            // Update document to send to pembayaran
            $dokumen->update([
                'status' => 'sent_to_pembayaran',
                'current_handler' => 'pembayaran',
                'sent_to_pembayaran_at' => now(),
                'status_pembayaran' => 'belum_dibayar',
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim ke Pembayaran.'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error sending document to pembayaran: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen ke Pembayaran.'
            ], 500);
        }
    }

    public function pengembalian(){
        $data = array(
            "title" => "Daftar Pengembalian Akutansi",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
        );
        return view('akutansi.dokumens.pengembalianAkutansi', $data);
    }

    public function rekapan()
    {
        // Base query - get documents created by IbuA
        $query = \App\Models\Dokumen::where('created_by', 'ibuA');

        // Apply filters
        $selectedBagian = request('bagian');
        if ($selectedBagian) {
            $query->where('bagian', $selectedBagian);
        }

        $year = request('year');
        if ($year) {
            $query->where('tahun', $year);
        }

        $search = request('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                  ->orWhere('nomor_spp', 'like', "%{$search}%")
                  ->orWhere('uraian_spp', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $dokumens = $query->orderBy('tanggal_masuk', 'desc')->paginate(25);

        // Calculate statistics
        $statistics = [
            'total_documents' => \App\Models\Dokumen::where('created_by', 'ibuA')->count(),
            'by_status' => [
                'draft' => \App\Models\Dokumen::where('created_by', 'ibuA')->where('status', 'draft')->count(),
                'sent_to_ibub' => \App\Models\Dokumen::where('created_by', 'ibuA')->where('status', 'sent_to_ibub')->count(),
                'sedang diproses' => \App\Models\Dokumen::where('created_by', 'ibuA')->where('status', 'sedang diproses')->count(),
                'selesai' => \App\Models\Dokumen::where('created_by', 'ibuA')->where('status', 'selesai')->count(),
            ],
            'by_bagian' => []
        ];

        // Calculate statistics by bagian
        $bagianList = [
            'DPM' => 'Divisi Pengadaan Material',
            'SKH' => 'Sumber Daya Kesehatan Hewan',
            'SDM' => 'Sumber Daya Manusia',
            'TEP' => 'Teknik dan Pemeliharaan',
            'KPL' => 'Keuangan dan Perencanaan',
            'AKN' => 'Akuntansi',
            'TAN' => 'Tanaman',
            'PMO' => 'Project Management Office'
        ];

        foreach ($bagianList as $code => $name) {
            $statistics['by_bagian'][$code] = [
                'name' => $name,
                'total' => \App\Models\Dokumen::where('created_by', 'ibuA')->where('bagian', $code)->count(),
            ];
        }

        // Apply filter for statistics if bagian is selected
        if ($selectedBagian) {
            $baseQuery = \App\Models\Dokumen::where('created_by', 'ibuA')->where('bagian', $selectedBagian);
            $statistics['by_status'] = [
                'draft' => $baseQuery->clone()->where('status', 'draft')->count(),
                'sent_to_ibub' => $baseQuery->clone()->where('status', 'sent_to_ibub')->count(),
                'sedang diproses' => $baseQuery->clone()->where('status', 'sedang diproses')->count(),
                'selesai' => $baseQuery->clone()->where('status', 'selesai')->count(),
            ];
        }

        $data = array(
            "title" => "Rekapan Dokumen",
            "module" => "akutansi",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuRekapan' => 'Active',
            'dokumens' => $dokumens,
            'statistics' => $statistics,
            'bagianList' => $bagianList,
            'selectedBagian' => $selectedBagian,
        );

        return view('akutansi.dokumens.rekapan', $data);
    }

    public function diagram(){
        // Get filter year (default to current year)
        $selectedYear = request('year', date('Y'));
        
        // Get all documents handled by akutansi for the selected year
        $allDokumens = Dokumen::where(function($query) {
                $query->where('current_handler', 'akutansi')
                      ->orWhere('created_by', 'ibuA'); // Documents created by IbuA that may pass through akutansi
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
        
        // Chart 2: Statistik Keterlambatan Dokumen (based on deadline_at)
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
                if ($doc->deadline_at) {
                    $totalWithDeadline++;
                    $now = now();
                    if ($doc->deadline_at->lt($now)) {
                        // Check if processed/completed
                        if ($doc->deadline_completed_at) {
                            if ($doc->deadline_completed_at->gt($doc->deadline_at)) {
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
            
            // Selesai = sudah dikirim ke handler berikutnya (bukan lagi di akutansi)
            $selesai = $monthDocs->filter(function($doc) {
                return $doc->current_handler !== 'akutansi' && $doc->current_handler !== null;
            })->count();
            
            $tidakSelesai = $monthDocs->filter(function($doc) {
                return $doc->current_handler === 'akutansi' || $doc->current_handler === null;
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
            "title" => "Diagram Akutansi",
            "module" => "akutansi",
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
        return view('akutansi.diagramAkutansi', $data);
    }

    /**
     * Get document detail for Akutansi view
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow access if document is handled by akutansi or sent to akutansi
        $allowedHandlers = ['akutansi', 'perpajakan', 'ibuB'];
        $allowedStatuses = ['sent_to_akutansi', 'sedang diproses', 'selesai'];

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
            'Dibayar Kepada' => $dokumen->dibayar_kepada ?? '-',
            'No Berita Acara' => $dokumen->no_berita_acara ?? '-',
            'Tanggal Berita Acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-',
            'No SPK' => $dokumen->no_spk ?? '-',
            'Tanggal SPK' => $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-',
            'Tanggal Akhir SPK' => $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-',
            'No PO' => $dokumen->dokumenPos->count() > 0 ? htmlspecialchars($dokumen->dokumenPos->pluck('nomor_po')->join(', ')) : '-',
            'No PR' => $dokumen->dokumenPrs->count() > 0 ? htmlspecialchars($dokumen->dokumenPrs->pluck('nomor_pr')->join(', ')) : '-',
            'No Mirror' => $dokumen->nomor_mirror ?? '-',
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
            return sprintf('<a href="%s" target="_blank" class="tax-link">%s <i class="fa-solid fa-external-link-alt"></i></a>', 
                htmlspecialchars($link), 
                htmlspecialchars($link)
            );
        }

        return htmlspecialchars($link);
    }
}

