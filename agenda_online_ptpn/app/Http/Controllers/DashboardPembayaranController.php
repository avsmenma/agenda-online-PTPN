<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use Illuminate\Support\Facades\Validator;

class DashboardPembayaranController extends Controller
{
    public function index(){
        // Handler yang dianggap "belum siap dibayar"
        $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
        
        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            if ($doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };

        // Get all documents that pembayaran can see (including belum siap)
        $allDocs = Dokumen::whereNotNull('nomor_agenda')->get();
        
        // Add computed status to each document
        $allDocs->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Calculate statistics
        $totalDokumen = $allDocs->count();

        $totalSelesai = $allDocs
            ->where('computed_status', 'sudah_dibayar')
            ->count();

        $totalProses = $allDocs
            ->where('computed_status', 'siap_dibayar')
            ->count();

        $totalDikembalikan = Dokumen::whereNotNull('returned_from_pembayaran_at')
            ->count();

        // Get latest documents - sorted by sent_to_pembayaran_at (tanggal masuk ke pembayaran) terbaru
        // Jika sent_to_pembayaran_at null, gunakan tanggal_masuk sebagai fallback
        $dokumenTerbaru = Dokumen::where(function($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
            })
            ->with(['dokumenPos', 'dokumenPrs'])
            ->orderByRaw('COALESCE(sent_to_pembayaran_at, tanggal_masuk) DESC')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'totalDokumen' => $totalDokumen,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalDikembalikan' => $totalDikembalikan,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('pembayaran.dashboardPembayaran', $data);
    }

    public function dokumens(){
        // Get filter parameter
        $statusFilter = request('status_filter');

        // Handler yang dianggap "belum siap dibayar"
        $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];

        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            if ($doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };

        // Base query - semua dokumen
        $query = Dokumen::whereNotNull('nomor_agenda');

        // Apply status filter
        if ($statusFilter) {
            if ($statusFilter === 'belum_siap_dibayar') {
                $query->whereIn('current_handler', $belumSiapHandlers);
            } elseif ($statusFilter === 'siap_dibayar') {
                $query->where(function($q) {
                    $q->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
                })->where(function($q) {
                    $q->whereNull('status_pembayaran')
                      ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
            } elseif ($statusFilter === 'sudah_dibayar') {
                $query->where('status_pembayaran', 'sudah_dibayar');
            }
        }

        $dokumens = $query->with(['dokumenPos', 'dokumenPrs'])
            ->orderByRaw("CASE
                WHEN current_handler = 'pembayaran' AND status = 'sent_to_pembayaran' AND deadline_pembayaran_at IS NULL THEN 1
                WHEN current_handler = 'pembayaran' AND deadline_pembayaran_at IS NOT NULL THEN 2
                ELSE 3
            END")
            ->orderByDesc('created_at')
            ->get();

        // Add computed status to each document
        $dokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        $data = array(
            "title" => "Daftar Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'statusFilter' => $statusFilter,
        );
        return view('pembayaran.dokumens.daftarPembayaran', $data);
    }

    /**
     * Get document detail for Pembayaran view
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Handler yang dianggap "belum siap dibayar" - bisa dilihat tapi tidak bisa diedit
        $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
        
        // Allow access if:
        // 1. Document is handled by pembayaran or sent to pembayaran (bisa diedit)
        // 2. Document is still in belum siap handlers (hanya bisa dilihat)
        // 3. Document belum dibayar (status_pembayaran null, belum_dibayar, atau belum_siap_dibayar) - bisa dilihat
        $canView = $dokumen->current_handler === 'pembayaran' 
                || $dokumen->status === 'sent_to_pembayaran'
                || in_array($dokumen->current_handler, $belumSiapHandlers)
                || is_null($dokumen->status_pembayaran)
                || in_array($dokumen->status_pembayaran, ['belum_dibayar', 'siap_dibayar']);
        
        if (!$canView) {
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load required relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        // Return HTML partial for detail view
        $html = $this->generateDocumentDetailHtml($dokumen);

        return response($html);
    }

    /**
     * Generate HTML for document detail with perpajakan data
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

        if ($hasPerpajakanData) {
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

    /**
     * Set deadline for Pembayaran
     */
    public function setDeadline(Request $request, Dokumen $dokumen)
    {
        // Only allow if document is currently with Pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
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
                'deadline_pembayaran_at' => $deadlineAt,
                'deadline_pembayaran_days' => $deadlineDays,
                'deadline_pembayaran_note' => $validated['deadline_note'] ?? null,
                'status_pembayaran' => 'siap_dibayar',
                'processed_pembayaran_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deadline berhasil ditetapkan. Dokumen sekarang siap diproses.',
                'deadline' => $deadlineAt->format('d M Y, H:i'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error setting Pembayaran deadline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline.'
            ], 500);
        }
    }

    public function createDokumen(){
        $data = array(
            "title" => "Tambah Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        return view('pembayaran.dokumens.tambahPembayaran', $data);
    }

    public function storeDokumen(Request $request){
        // Implementation for storing document
        return redirect()->route('dokumensPembayaran.index')->with('success', 'Pembayaran berhasil ditambahkan');
    }

    public function editDokumen(Dokumen $dokumen){
        // Verify access - only allow if document is sent to pembayaran
        if ($dokumen->status !== 'sent_to_pembayaran' && $dokumen->current_handler !== 'pembayaran') {
            return redirect()->route('dokumensPembayaran.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit dokumen ini.');
        }

        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs']);

        $data = array(
            "title" => "Input Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuEditDokumen' => 'Active',
            'dokumen' => $dokumen,
        );
        return view('pembayaran.dokumens.editPembayaran', $data);
    }

    public function updateDokumen(Request $request, Dokumen $dokumen){
        // Verify access
        if ($dokumen->status !== 'sent_to_pembayaran' && $dokumen->current_handler !== 'pembayaran') {
            return redirect()->route('dokumensPembayaran.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate dokumen ini.');
        }

        // Validate request - both fields are optional
        $validated = $request->validate([
            'tanggal_dibayar' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
            'catatan_pembayaran' => 'nullable|string|max:500',
        ], [
            'bukti_pembayaran.mimes' => 'File bukti pembayaran harus berupa PDF, JPG, JPEG, atau PNG.',
            'bukti_pembayaran.max' => 'Ukuran file maksimal 5MB.',
            'catatan_pembayaran.max' => 'Catatan maksimal 500 karakter.',
        ]);

        try {
            $updateData = [];

            // Check if tanggal_dibayar is filled
            if ($request->filled('tanggal_dibayar')) {
                $updateData['tanggal_dibayar'] = $request->tanggal_dibayar;
            }

            // Handle file upload
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $filename = 'bukti_' . $dokumen->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/bukti_pembayaran', $filename);
                $updateData['bukti_pembayaran'] = 'bukti_pembayaran/' . $filename;
            }

            // Add catatan if provided
            if ($request->filled('catatan_pembayaran')) {
                $updateData['catatan_pembayaran'] = $request->catatan_pembayaran;
            }

            // If either tanggal_dibayar or bukti_pembayaran is filled, update status to sudah_dibayar
            if ($request->filled('tanggal_dibayar') || $request->hasFile('bukti_pembayaran')) {
                $updateData['status_pembayaran'] = 'sudah_dibayar';
                $updateData['processed_pembayaran_at'] = now();
            }

            // Only update if there's data to update
            if (!empty($updateData)) {
                $dokumen->update($updateData);
                return redirect()->route('dokumensPembayaran.index')
                    ->with('success', 'Data pembayaran berhasil diperbarui. Status: Sudah Dibayar');
            }

            return redirect()->route('dokumensPembayaran.index')
                ->with('info', 'Tidak ada perubahan data.');

        } catch (\Exception $e) {
            \Log::error('Error updating pembayaran: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data pembayaran.')
                ->withInput();
        }
    }

    public function destroyDokumen($id){
        // Implementation for deleting document
        return redirect()->route('dokumensPembayaran.index')->with('success', 'Pembayaran berhasil dihapus');
    }

    public function pengembalian(){
        $data = array(
            "title" => "Daftar Pengembalian Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumenDikembalikan' => 'Active',
        );
        return view('pembayaran.dokumens.pengembalianPembayaran', $data);
    }

    public function rekapanKeterlambatan(Request $request){
        // Get filter parameters
        $selectedBagian = $request->get('bagian', '');
        $selectedYear = $request->get('year', date('Y'));
        $search = $request->get('search', '');
        
        // Map bagian filter to handler/created_by
        $bagianMap = [
            'IbuA' => ['handler' => null, 'created_by' => 'ibuA', 'deadline_field' => 'deadline_at'],
            'IbuB' => ['handler' => 'ibuB', 'created_by' => null, 'deadline_field' => 'deadline_at'],
            'Perpajakan' => ['handler' => 'perpajakan', 'created_by' => null, 'deadline_field' => 'deadline_perpajakan_at'],
            'Akutansi' => ['handler' => 'akutansi', 'created_by' => null, 'deadline_field' => 'deadline_at'],
        ];
        
        // Base query for documents with deadlines
        $query = Dokumen::whereNotNull('nomor_agenda');
        
        // Apply bagian filter
        if ($selectedBagian && isset($bagianMap[$selectedBagian])) {
            $map = $bagianMap[$selectedBagian];
            if ($map['handler']) {
                $query->where('current_handler', $map['handler']);
            }
            if ($map['created_by']) {
                $query->where('created_by', $map['created_by']);
            }
        }
        
        // Filter by year
        if ($selectedYear) {
            $query->whereYear('created_at', $selectedYear);
        }
        
        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%');
            });
        }
        
        // Get all documents for statistics
        $allDokumens = $query->get();
        
        // Determine deadline field based on selected bagian
        $deadlineField = $selectedBagian && isset($bagianMap[$selectedBagian]) 
            ? $bagianMap[$selectedBagian]['deadline_field'] 
            : 'deadline_pembayaran_at';
        
        // Filter documents with deadlines and calculate overdue
        $terlambatDokumens = [];
        $now = now();
        
        foreach ($allDokumens as $doc) {
            $deadline = null;
            
            // Get deadline based on bagian
            if ($selectedBagian && isset($bagianMap[$selectedBagian])) {
                $field = $bagianMap[$selectedBagian]['deadline_field'];
                $deadline = $doc->$field;
            } else {
                // Default to pembayaran deadline
                $deadline = $doc->deadline_pembayaran_at;
            }
            
            if ($deadline) {
                $isOverdue = false;
                $daysOverdue = 0;
                $completionDate = null;
                
                // Check if overdue based on bagian
                if ($selectedBagian === 'IbuA') {
                    // For IbuA, check if sent to IbuB after deadline
                    if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                        if ($doc->sent_to_ibub_at) {
                            if ($doc->sent_to_ibub_at->gt($doc->deadline_at)) {
                                $isOverdue = true;
                                $daysOverdue = $doc->deadline_at->diffInDays($doc->sent_to_ibub_at);
                                $completionDate = $doc->sent_to_ibub_at;
                            }
                        } else {
                            // Not sent yet, check if deadline passed
                            $isOverdue = true;
                            $daysOverdue = $doc->deadline_at->diffInDays($now);
                        }
                    }
                } elseif ($selectedBagian === 'IbuB') {
                    // For IbuB, check if processed after deadline
                    if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                        if ($doc->processed_at) {
                            if ($doc->processed_at->gt($doc->deadline_at)) {
                                $isOverdue = true;
                                $daysOverdue = $doc->deadline_at->diffInDays($doc->processed_at);
                                $completionDate = $doc->processed_at;
                            }
                        } else {
                            // Not processed yet, check if deadline passed
                            $isOverdue = true;
                            $daysOverdue = $doc->deadline_at->diffInDays($now);
                        }
                    }
                } elseif ($selectedBagian === 'Perpajakan') {
                    // For Perpajakan, check deadline_perpajakan_at
                    if ($doc->deadline_perpajakan_at && $doc->deadline_perpajakan_at->lt($now)) {
                        if ($doc->processed_perpajakan_at) {
                            if ($doc->processed_perpajakan_at->gt($doc->deadline_perpajakan_at)) {
                                $isOverdue = true;
                                $daysOverdue = $doc->deadline_perpajakan_at->diffInDays($doc->processed_perpajakan_at);
                                $completionDate = $doc->processed_perpajakan_at;
                            }
                        } else {
                            $isOverdue = true;
                            $daysOverdue = $doc->deadline_perpajakan_at->diffInDays($now);
                        }
                    }
                } elseif ($selectedBagian === 'Akutansi') {
                    // For Akutansi, check deadline_at
                    if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                        if ($doc->deadline_completed_at) {
                            if ($doc->deadline_completed_at->gt($doc->deadline_at)) {
                                $isOverdue = true;
                                $daysOverdue = $doc->deadline_at->diffInDays($doc->deadline_completed_at);
                                $completionDate = $doc->deadline_completed_at;
                            }
                        } else {
                            $isOverdue = true;
                            $daysOverdue = $doc->deadline_at->diffInDays($now);
                        }
                    }
                } else {
                    // Default: pembayaran deadline
                    if ($doc->deadline_pembayaran_at && $doc->deadline_pembayaran_at->lt($now)) {
                        if ($doc->tanggal_dibayar) {
                            if ($doc->tanggal_dibayar->gt($doc->deadline_pembayaran_at)) {
                                $isOverdue = true;
                                $daysOverdue = $doc->deadline_pembayaran_at->diffInDays($doc->tanggal_dibayar);
                                $completionDate = $doc->tanggal_dibayar;
                            }
                        } else {
                            $isOverdue = true;
                            $daysOverdue = $doc->deadline_pembayaran_at->diffInDays($now);
                        }
                    }
                }
                
                if ($isOverdue) {
                    $terlambatDokumens[] = [
                        'dokumen' => $doc,
                        'deadline' => $deadline,
                        'days_overdue' => $daysOverdue,
                        'completion_date' => $completionDate,
                    ];
                }
            }
        }
        
        // Sort by days overdue (descending)
        usort($terlambatDokumens, function($a, $b) {
            return $b['days_overdue'] <=> $a['days_overdue'];
        });
        
        // Paginate results
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedDokumens = array_slice($terlambatDokumens, $offset, $perPage);
        
        // Create paginator manually with query string preservation
        $queryParams = $request->except('page');
        $dokumens = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedDokumens,
            count($terlambatDokumens),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $queryParams
            ]
        );
        
        // Calculate statistics for chart - use same logic as document filtering
        $monthlyData = [];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthDocs = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            });
            
            $terlambat = 0;
            $now = now();
            
            foreach ($monthDocs as $doc) {
                $deadline = null;
                if ($selectedBagian && isset($bagianMap[$selectedBagian])) {
                    $field = $bagianMap[$selectedBagian]['deadline_field'];
                    $deadline = $doc->$field;
                } else {
                    $deadline = $doc->deadline_pembayaran_at;
                }
                
                if ($deadline) {
                    $isOverdue = false;
                    
                    // Use same logic as document filtering
                    if ($selectedBagian === 'IbuA') {
                        if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                            if ($doc->sent_to_ibub_at) {
                                if ($doc->sent_to_ibub_at->gt($doc->deadline_at)) {
                                    $isOverdue = true;
                                }
                            } else {
                                $isOverdue = true;
                            }
                        }
                    } elseif ($selectedBagian === 'IbuB') {
                        if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                            if ($doc->processed_at) {
                                if ($doc->processed_at->gt($doc->deadline_at)) {
                                    $isOverdue = true;
                                }
                            } else {
                                $isOverdue = true;
                            }
                        }
                    } elseif ($selectedBagian === 'Perpajakan') {
                        if ($doc->deadline_perpajakan_at && $doc->deadline_perpajakan_at->lt($now)) {
                            if ($doc->processed_perpajakan_at) {
                                if ($doc->processed_perpajakan_at->gt($doc->deadline_perpajakan_at)) {
                                    $isOverdue = true;
                                }
                            } else {
                                $isOverdue = true;
                            }
                        }
                    } elseif ($selectedBagian === 'Akutansi') {
                        if ($doc->deadline_at && $doc->deadline_at->lt($now)) {
                            if ($doc->deadline_completed_at) {
                                if ($doc->deadline_completed_at->gt($doc->deadline_at)) {
                                    $isOverdue = true;
                                }
                            } else {
                                $isOverdue = true;
                            }
                        }
                    } else {
                        // Default: pembayaran
                        if ($doc->deadline_pembayaran_at && $doc->deadline_pembayaran_at->lt($now)) {
                            if ($doc->tanggal_dibayar) {
                                if ($doc->tanggal_dibayar->gt($doc->deadline_pembayaran_at)) {
                                    $isOverdue = true;
                                }
                            } else {
                                $isOverdue = true;
                            }
                        }
                    }
                    
                    if ($isOverdue) {
                        $terlambat++;
                    }
                }
            }
            
            $monthlyData[] = $terlambat;
        }
        
        // Get available years
        $availableYears = Dokumen::whereNotNull('nomor_agenda')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        $data = array(
            "title" => "Rekap Keterlambatan",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => '',
            'menuRekapanDokumen' => '',
            'menuRekapKeterlambatan' => 'Active',
            'dokumens' => $dokumens,
            'selectedBagian' => $selectedBagian,
            'selectedYear' => $selectedYear,
            'search' => $search,
            'monthlyData' => $monthlyData,
            'months' => $months,
            'availableYears' => $availableYears,
            'totalTerlambat' => count($terlambatDokumens),
        );
        return view('pembayaran.dokumens.rekapanKeterlambatan', $data);
    }

    /**
     * Rekapan Dokumen Pembayaran
     * Menampilkan semua dokumen dengan filter dan statistik
     */
    public function rekapan()
    {
        // Get filter parameters
        $statusPembayaran = request('status_pembayaran');
        $year = request('year');
        $month = request('month');
        $search = request('search');
        $mode = request('mode', 'normal'); // normal or rekapan_table
        $selectedColumns = request('columns', []); // Array of selected columns in order

        // Handler yang dianggap "belum siap dibayar"
        $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];

        // Base query - semua dokumen yang sudah melewati proses awal
        $query = Dokumen::whereNotNull('nomor_agenda');

        // Apply status filter based on new logic
        if ($statusPembayaran) {
            if ($statusPembayaran === 'belum_siap_dibayar') {
                // Belum siap = masih di akuntansi, perpajakan, ibu_a, ibu_b
                $query->whereIn('current_handler', $belumSiapHandlers);
            } elseif ($statusPembayaran === 'siap_dibayar') {
                // Siap dibayar = sudah di pembayaran tapi belum dibayar
                $query->where(function($q) {
                    $q->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
                })->where(function($q) {
                    $q->whereNull('status_pembayaran')
                      ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                // Sudah dibayar
                $query->where('status_pembayaran', 'sudah_dibayar');
            }
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                  ->orWhere('nomor_spp', 'like', "%{$search}%")
                  ->orWhere('uraian_spp', 'like', "%{$search}%")
                  ->orWhere('dibayar_kepada', 'like', "%{$search}%");
            });
        }

        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            // Jika sudah dibayar
            if ($doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            // Jika masih di akuntansi, perpajakan, ibu_a, ibu_b
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            // Jika sudah di pembayaran tapi belum dibayar
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            // Default
            return 'belum_siap_dibayar';
        };

        // For rekapan table mode - group by vendor
        $rekapanByVendor = null;
        if ($mode === 'rekapan_table' && !empty($selectedColumns)) {
            $allDocsForRekapan = (clone $query)->orderBy('dibayar_kepada')->get();

            // Add computed status to each document
            $allDocsForRekapan->each(function($doc) use ($getComputedStatus) {
                $doc->computed_status = $getComputedStatus($doc);
            });

            // Group by vendor
            $rekapanByVendor = $allDocsForRekapan->groupBy('dibayar_kepada')->map(function($docs, $vendor) {
                return [
                    'vendor' => $vendor ?: 'Tidak Diketahui',
                    'documents' => $docs,
                    'total_nilai' => $docs->sum('nilai_rupiah'),
                    'total_belum_dibayar' => $docs->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah'),
                    'total_siap_dibayar' => $docs->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah'),
                    'total_sudah_dibayar' => $docs->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah'),
                    'count' => $docs->count(),
                ];
            });
        }

        // Get paginated results for normal mode
        $dokumens = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        // Add computed status to paginated results
        $dokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Calculate statistics
        $allDokumensQuery = Dokumen::whereNotNull('nomor_agenda');

        // Apply same filters for statistics
        if ($year) {
            $allDokumensQuery->whereYear('created_at', $year);
        }
        if ($month) {
            $allDokumensQuery->whereMonth('created_at', $month);
        }

        $allDokumensData = $allDokumensQuery->get();

        // Add computed status to all documents for statistics
        $allDokumensData->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        $statistics = [
            'total_documents' => $allDokumensData->count(),
            'total_nilai' => $allDokumensData->sum('nilai_rupiah'),
            'by_status' => [
                'belum_dibayar' => $allDokumensData->where('computed_status', 'belum_siap_dibayar')->count(),
                'siap_dibayar' => $allDokumensData->where('computed_status', 'siap_dibayar')->count(),
                'sudah_dibayar' => $allDokumensData->where('computed_status', 'sudah_dibayar')->count(),
            ],
            'total_nilai_by_status' => [
                'belum_dibayar' => $allDokumensData->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah'),
                'siap_dibayar' => $allDokumensData->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah'),
                'sudah_dibayar' => $allDokumensData->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah'),
            ],
        ];

        // Get available years for filter
        $availableYears = Dokumen::whereNotNull('nomor_agenda')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Available columns for rekapan table
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'dibayar_kepada' => 'Nama Vendor/Dibayar Kepada',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'jenis_sub_pekerjaan' => 'Jenis Subbagian',
            'nomor_mirror' => 'Nomor Mirror',
            'nomor_spp' => 'No SPP',
            'tanggal_spp' => 'Tanggal SPP',
            'tanggal_berita_acara' => 'Tanggal BA',
            'no_berita_acara' => 'Nomor BA',
            'tanggal_berakhir_ba' => 'Tanggal Akhir BA',
            'no_spk' => 'Nomor SPK',
            'tanggal_spk' => 'Tanggal SPK',
            'tanggal_berakhir_spk' => 'Tanggal Berakhir SPK',
            'umur_dokumen_tanggal_masuk' => 'Umur Dokumen (Berdasarkan Tanggal Masuk)',
            'umur_dokumen_tanggal_spp' => 'Umur Dokumen (Berdasarkan Tanggal SPP)',
            'umur_dokumen_tanggal_ba' => 'Umur Dokumen (Berdasarkan Tanggal BA)',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nilai_belum_siap_bayar' => 'Nilai Rupiah Belum Siap Bayar',
            'nilai_siap_bayar' => 'Nilai Rupiah Sudah Siap Bayar',
            'nilai_sudah_dibayar' => 'Nilai Rupiah Sudah Dibayar',
        ];

        $data = [
            'title' => 'Rekapan Dokumen Pembayaran',
            'module' => 'pembayaran',
            'menuDashboard' => '',
            'menuDokumen' => 'Active',
            'menuRekapanDokumen' => 'Active',
            'dokumens' => $dokumens,
            'statistics' => $statistics,
            'selectedStatus' => $statusPembayaran,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'search' => $search,
            'availableYears' => $availableYears,
            'mode' => $mode,
            'selectedColumns' => $selectedColumns,
            'availableColumns' => $availableColumns,
            'rekapanByVendor' => $rekapanByVendor,
        ];

        return view('pembayaran.dokumens.rekapanDokumen', $data);
    }

    public function diagram(){
        // Get filter year (default to current year)
        $selectedYear = request('year', date('Y'));
        
        // Handler yang dianggap "belum siap dibayar"
        $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
        
        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            if ($doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            if (in_array($doc->current_handler, $belumSiapHandlers)) {
                return 'belum_siap_dibayar';
            }
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            return 'belum_siap_dibayar';
        };
        
        // Get all documents for the selected year
        $allDokumens = Dokumen::whereNotNull('nomor_agenda')
            ->whereYear('created_at', $selectedYear)
            ->get();
        
        // Add computed status to each document
        $allDokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });
        
        // Chart 1: Statistik Jumlah Dokumen per Bulan
        $monthlyData = [];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = $allDokumens->filter(function($doc) use ($month, $selectedYear) {
                return $doc->created_at->year == $selectedYear && $doc->created_at->month == $month;
            })->count();
        }
        
        // Chart 2: Statistik Keterlambatan Dokumen
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
                if ($doc->deadline_pembayaran_at) {
                    $totalWithDeadline++;
                    if ($doc->tanggal_dibayar) {
                        // Compare tanggal dibayar dengan deadline
                        if ($doc->tanggal_dibayar->gt($doc->deadline_pembayaran_at)) {
                            $terlambat++;
                        } else {
                            $tepat++;
                        }
                    } else {
                        // Belum dibayar, cek apakah sudah melewati deadline
                        if (now()->gt($doc->deadline_pembayaran_at)) {
                            $terlambat++;
                        } else {
                            // Belum melewati deadline, dihitung sebagai tepat (masih dalam waktu)
                            $tepat++;
                        }
                    }
                }
            }
            
            // Calculate percentage based on documents with deadline
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
            
            $selesai = $monthDocs->where('computed_status', 'sudah_dibayar')->count();
            $tidakSelesai = $monthDocs->where('computed_status', '!=', 'sudah_dibayar')->count();
            
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
            "title" => "Diagram Pembayaran",
            "module" => "pembayaran",
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
        return view('pembayaran.diagramPembayaran', $data);
    }
}

