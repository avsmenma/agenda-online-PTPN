<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\DocumentTracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OwnerDashboardController extends Controller
{
    /**
     * Display the owner dashboard with document list and tracking
     */
    public function index(Request $request)
    {
        // Get paginated documents with latest status and apply search filter
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;

        $documents = $this->getDocumentsWithTracking($request, $perPage);

        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        // Calculate dashboard statistics
        $totalDokumen = Dokumen::count();

        // Dokumen Selesai: status completed atau status_pembayaran = sudah_dibayar
        $dokumenSelesai = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar');
        })->count();

        // Dokumen Proses: dokumen yang belum selesai (tidak termasuk status selesai)
        $dokumenProses = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        })->count();

        // Total Nilai (Rp)
        $totalNilai = Dokumen::sum('nilai_rupiah') ?? 0;

        // Calculate trend indicators (compare with last week)
        $oneWeekAgo = Carbon::now()->subWeek();

        $totalDokumenLastWeek = Dokumen::where('created_at', '<=', $oneWeekAgo)->count();
        $totalDokumenTrend = $totalDokumenLastWeek > 0
            ? round((($totalDokumen - $totalDokumenLastWeek) / $totalDokumenLastWeek) * 100, 1)
            : 0;

        $dokumenSelesaiLastWeek = Dokumen::where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar');
        })->where('updated_at', '<=', $oneWeekAgo)->count();
        $dokumenSelesaiTrend = $dokumenSelesaiLastWeek > 0
            ? round((($dokumenSelesai - $dokumenSelesaiLastWeek) / $dokumenSelesaiLastWeek) * 100, 1)
            : ($dokumenSelesai > 0 ? 100 : 0);

        $dokumenProsesLastWeek = Dokumen::where(function ($q) {
            $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        })->where('created_at', '<=', $oneWeekAgo)->count();
        $dokumenProsesTrend = $dokumenProsesLastWeek > 0
            ? round((($dokumenProses - $dokumenProsesLastWeek) / $dokumenProsesLastWeek) * 100, 1)
            : ($dokumenProses > 0 ? 100 : 0);

        $totalNilaiLastWeek = Dokumen::where('created_at', '<=', $oneWeekAgo)->sum('nilai_rupiah') ?? 0;
        $totalNilaiTrend = $totalNilaiLastWeek > 0
            ? round((($totalNilai - $totalNilaiLastWeek) / $totalNilaiLastWeek) * 100, 1)
            : ($totalNilai > 0 ? 100 : 0);

        return view('owner.dashboard', compact(
            'documents',
            'totalDokumen',
            'dokumenProses',
            'dokumenSelesai',
            'totalNilai',
            'totalDokumenTrend',
            'dokumenSelesaiTrend',
            'dokumenProsesTrend',
            'totalNilaiTrend',
            'filterData'
        ))
            ->with('title', 'Dashboard Owner - Pusat Komando')
            ->with('module', 'owner')
            ->with('menuDashboard', 'active')
            ->with('menuRekapan', '')
            ->with('menuRekapanKeterlambatan', '')
            ->with('menuDokumen', '')
            ->with('menuDaftarDokumen', '')
            ->with('menuEditDokumen', '')
            ->with('menuRekapKeterlambatan', '')
            ->with('menuDaftarDokumenDikembalikan', '')
            ->with('menuPengembalianKeBidang', '')
            ->with('menuTambahDokumen', '')
            ->with('dashboardUrl', '/owner/dashboard')
            ->with('dokumenUrl', '#')
            ->with('pengembalianUrl', '#')
            ->with('tambahDokumenUrl', '#')
            ->with('search', $request->get('search', ''));
    }

    /**
     * Get API endpoint for document timeline
     */
    public function getDocumentTimeline($id): JsonResponse
    {
        $dokumen = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
            ->findOrFail($id);

        $timeline = $this->generateDocumentTimeline($dokumen);

        return response()->json([
            'success' => true,
            'dokumen' => [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'status' => $dokumen->status,
                'current_handler' => $dokumen->current_handler,
                'created_at' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '-',
                'progress_percentage' => $this->calculateProgress($dokumen),
            ],
            'timeline' => $timeline
        ]);
    }

    /**
     * Get documents with their latest tracking status
     */
    private function getDocumentsWithTracking(Request $request = null, $perPage = 10)
    {
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData', 'roleStatuses']);

        // Apply status filter if provided
        if ($request && $request->has('status') && !empty($request->status)) {
            $status = $request->status;
            if ($status === 'proses') {
                $query->where(function ($q) {
                    $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->where(function ($subQ) {
                            $subQ->whereNull('status_pembayaran')
                                ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                        });
                });
            } elseif ($status === 'selesai') {
                $query->where(function ($q) {
                    $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            }
        }

        // Apply advanced filters
        // Filter by bagian
        if ($request && $request->has('filter_bagian') && !empty($request->filter_bagian)) {
            $query->where('bagian', $request->filter_bagian);
        }

        // Filter by vendor/dibayar_kepada
        if ($request && $request->has('filter_vendor') && !empty($request->filter_vendor)) {
            $query->where(function ($q) use ($request) {
                $q->where('dibayar_kepada', $request->filter_vendor)
                    ->orWhereHas('dibayarKepadas', function ($subQ) use ($request) {
                        $subQ->where('nama_penerima', $request->filter_vendor);
                    });
            });
        }

        // Filter by kriteria CF (by ID or name)
        if ($request && $request->has('filter_kriteria_cf') && !empty($request->filter_kriteria_cf)) {
            try {
                $kriteriaCf = \App\Models\KategoriKriteria::on('cash_bank')
                    ->where('id_kategori_kriteria', $request->filter_kriteria_cf)
                    ->orWhere('nama_kriteria', $request->filter_kriteria_cf)
                    ->first();
                if ($kriteriaCf) {
                    $query->where('kategori', $kriteriaCf->nama_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by kriteria CF: ' . $e->getMessage());
            }
        }

        // Filter by sub kriteria (by ID or name)
        if ($request && $request->has('filter_sub_kriteria') && !empty($request->filter_sub_kriteria)) {
            try {
                $subKriteria = \App\Models\SubKriteria::on('cash_bank')
                    ->where('id_sub_kriteria', $request->filter_sub_kriteria)
                    ->orWhere('nama_sub_kriteria', $request->filter_sub_kriteria)
                    ->first();
                if ($subKriteria) {
                    $query->where('jenis_dokumen', $subKriteria->nama_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by sub kriteria: ' . $e->getMessage());
            }
        }

        // Filter by item sub kriteria (by ID or name)
        if ($request && $request->has('filter_item_sub_kriteria') && !empty($request->filter_item_sub_kriteria)) {
            try {
                $itemSubKriteria = \App\Models\ItemSubKriteria::on('cash_bank')
                    ->where('id_item_sub_kriteria', $request->filter_item_sub_kriteria)
                    ->orWhere('nama_item_sub_kriteria', $request->filter_item_sub_kriteria)
                    ->first();
                if ($itemSubKriteria) {
                    $query->where('jenis_sub_pekerjaan', $itemSubKriteria->nama_item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::error('Error filtering by item sub kriteria: ' . $e->getMessage());
            }
        }

        // Filter by kebun
        if ($request && $request->has('filter_kebun') && !empty($request->filter_kebun)) {
            $query->where('kebun', $request->filter_kebun);
        }

        // Filter by status pembayaran
        if ($request && $request->has('filter_status_pembayaran') && !empty($request->filter_status_pembayaran)) {
            $statusPembayaran = $request->filter_status_pembayaran;
            if ($statusPembayaran === 'belum_dibayar') {
                $query->where(function ($q) {
                    $q->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar')
                        ->orWhere('status_pembayaran', 'pending');
                })
                    ->whereNull('tanggal_dibayar')
                    ->whereNull('link_bukti_pembayaran');
            } elseif ($statusPembayaran === 'siap_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'siap_dibayar')
                        ->orWhere('status_pembayaran', 'siap_bayar')
                        ->orWhere(function ($subQ) {
                            $subQ->where('current_handler', 'pembayaran')
                                ->where('status', 'sent_to_pembayaran')
                                ->whereNull('tanggal_dibayar')
                                ->whereNull('link_bukti_pembayaran');
                        });
                });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                $query->where(function ($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                        ->orWhereNotNull('tanggal_dibayar')
                        ->orWhereNotNull('link_bukti_pembayaran');
                });
            }
        }

        // Apply search filter if provided
        if ($request && $request->has('search') && !empty($request->search) && trim((string) $request->search) !== '') {
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
                    ->orWhere('dibayar_kepada', 'like', '%' . $search . '%')
                    ->orWhere('npwp', 'like', '%' . $search . '%')
                    ->orWhere('no_faktur', 'like', '%' . $search . '%')
                    ->orWhere('jenis_pph', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('current_handler', 'like', '%' . $search . '%');

                // Search in nilai_rupiah - handle various formats
                $numericSearch = preg_replace('/[^0-9]/', '', $search);
                if (is_numeric($numericSearch) && $numericSearch > 0) {
                    $q->orWhereRaw('CAST(nilai_rupiah AS CHAR) LIKE ?', ['%' . $numericSearch . '%']);
                }
            })
                ->orWhereHas('dibayarKepadas', function ($q) use ($search) {
                    $q->where('nama_penerima', 'like', '%' . $search . '%');
                })
                ->orWhereHas('dokumenPos', function ($q) use ($search) {
                    $q->where('nomor_po', 'like', '%' . $search . '%');
                })
                ->orWhereHas('dokumenPrs', function ($q) use ($search) {
                    $q->where('nomor_pr', 'like', '%' . $search . '%');
                });
        }

        // Paginate the query
        $paginatedDocuments = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString(); // Preserve query parameters (search, status, etc.)

        // Map the paginated collection
        $paginatedDocuments->getCollection()->transform(function ($dokumen) {
            return [
                'id' => $dokumen->id,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'nomor_spp' => $dokumen->nomor_spp,
                'uraian_spp' => $dokumen->uraian_spp,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'status' => $dokumen->status,
                'status_display' => $this->getStatusDisplayName($dokumen->status),
                'current_handler' => $dokumen->current_handler,
                'current_handler_display' => $this->getRoleDisplayName($dokumen->current_handler),
                'created_at' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '-',
                'tanggal_masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d M Y') : ($dokumen->created_at ? $dokumen->created_at->format('d M Y') : '-'),
                'progress_percentage' => $this->calculateProgress($dokumen),
                'status_badge_color' => $this->getStatusBadgeColor($dokumen->status),
                'progress_color' => $this->getProgressColor($dokumen->status),
                'is_overdue' => $this->isDocumentOverdue($dokumen),
                'deadline_info' => $this->getDeadlineInfo($dokumen),
                'workflow_timeline' => $this->getWorkflowTimeline($dokumen),
            ];
        });

        return $paginatedDocuments;
    }

    /**
     * Generate timeline events for a document
     */
    private function generateDocumentTimeline($dokumen)
    {
        $events = [];
        $previousTime = null;

        // Event 1: Dokumen Dibuat
        if ($dokumen->created_at) {
            $events[] = [
                'type' => 'created',
                'icon' => '✅',
                'title' => 'Dokumen Dibuat',
                'timestamp' => $dokumen->created_at->format('d M Y H:i'),
                'info' => [
                    'Dibuat oleh' => $this->getRoleDisplayName($dokumen->created_by),
                    'Nomor Agenda' => $dokumen->nomor_agenda,
                    'Nomor SPP' => $dokumen->nomor_spp,
                    'Nilai' => 'Rp. ' . number_format($dokumen->nilai_rupiah, 0, ',', '.'),
                    'Uraian' => $dokumen->uraian_spp,
                ]
            ];
            $previousTime = $dokumen->created_at;
        }

        // Event 2: Dikirim ke Ibu Yuni
        $ibubData = $dokumen->getDataForRole('ibub');
        if ($ibubData && $ibubData->received_at) {
            $receivedAt = $ibubData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_ibub',
                'icon' => '🚀',
                'title' => 'Dikirim ke Ibu Yuni',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => 'Ibu Tarapul',
                    'Penerima' => 'Ibu Yuni',
                    'Durasi dari dibuat' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 3: Deadline Ditetapkan
        if ($dokumen->deadline_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->deadline_at) : null;
            $events[] = [
                'type' => 'deadline_set',
                'icon' => '⏰',
                'title' => 'Deadline Ditetapkan',
                'timestamp' => $dokumen->deadline_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Durasi deadline' => $dokumen->deadline_days . ' hari',
                    'Catatan' => $dokumen->deadline_note,
                ]
            ];
        }

        // Event 4: Diproses Ibu Yuni
        if ($dokumen->processed_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->processed_at) : null;
            $events[] = [
                'type' => 'processed_ibub',
                'icon' => '⚡',
                'title' => 'Diproses Ibu Yuni',
                'timestamp' => $dokumen->processed_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Handler' => 'Ibu Yuni',
                    'Durasi proses' => $duration,
                ]
            ];
            $previousTime = $dokumen->processed_at;
        }

        // Event 5: Dikirim ke Perpajakan
        $perpajakanData = $dokumen->getDataForRole('perpajakan');
        if ($perpajakanData && $perpajakanData->received_at) {
            $receivedAt = $perpajakanData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_perpajakan',
                'icon' => '🚀',
                'title' => 'Dikirim ke Team Perpajakan',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => $this->getRoleDisplayName($dokumen->current_handler),
                    'Penerima' => 'Team Perpajakan',
                    'Durasi dari step sebelumnya' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 6: Diproses Perpajakan
        if ($dokumen->processed_perpajakan_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->processed_perpajakan_at) : null;
            $events[] = [
                'type' => 'processed_perpajakan',
                'icon' => '⚡',
                'title' => 'Diproses Team Perpajakan',
                'timestamp' => $dokumen->processed_perpajakan_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Handler' => 'Team Perpajakan',
                    'Status Team Perpajakan' => $dokumen->status_perpajakan,
                    'Durasi proses' => $duration,
                ]
            ];
            $previousTime = $dokumen->processed_perpajakan_at;
        }

        // Event 7: Dikirim ke Akutansi
        $akutansiData = $dokumen->getDataForRole('akutansi');
        if ($akutansiData && $akutansiData->received_at) {
            $receivedAt = $akutansiData->received_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $receivedAt) : null;
            $events[] = [
                'type' => 'sent_to_akutansi',
                'icon' => '🚀',
                'title' => 'Dikirim ke Team Akutansi',
                'timestamp' => $receivedAt->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Pengirim' => 'Team Perpajakan',
                    'Penerima' => 'Team Akutansi',
                    'Durasi dari step sebelumnya' => $duration,
                ]
            ];
            $previousTime = $receivedAt;
        }

        // Event 8: Dikembalikan
        if ($dokumen->returned_to_ibua_at || $dokumen->department_returned_at || $dokumen->bidang_returned_at) {
            $returnTime = $dokumen->returned_to_ibua_at ?? $dokumen->department_returned_at ?? $dokumen->bidang_returned_at;
            $duration = $previousTime ? $this->calculateDuration($previousTime, $returnTime) : null;

            $events[] = [
                'type' => 'returned',
                'icon' => '↩️',
                'title' => 'Dikembalikan',
                'timestamp' => $returnTime->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Dikembalikan oleh' => $this->getRoleDisplayName($dokumen->current_handler),
                    'Dikembalikan ke' => $this->getReturnDestination($dokumen),
                    'Alasan' => $dokumen->alasan_pengembalian ?? $dokumen->department_return_reason ?? $dokumen->bidang_return_reason,
                ]
            ];
            $previousTime = $returnTime;
        }

        // Event 9: Deadline Selesai (jika ada)
        if ($dokumen->deadline_completed_at) {
            $duration = $previousTime ? $this->calculateDuration($previousTime, $dokumen->deadline_completed_at) : null;
            $events[] = [
                'type' => 'deadline_completed',
                'icon' => '✅',
                'title' => 'Deadline Selesai',
                'timestamp' => $dokumen->deadline_completed_at->format('d M Y H:i'),
                'duration' => $duration,
                'info' => [
                    'Status' => 'Deadline terpenuhi',
                    'Durasi' => $duration,
                ]
            ];
        }

        // Event 10: Dokumen Selesai
        if (in_array($dokumen->status, ['approved_data_sudah_terkirim', 'selesai'])) {
            $completedTime = $dokumen->updated_at;
            $totalDuration = $dokumen->created_at ? $this->calculateDuration($dokumen->created_at, $completedTime) : null;

            $events[] = [
                'type' => 'completed',
                'icon' => '🎉',
                'title' => 'Dokumen Selesai',
                'timestamp' => $completedTime->format('d M Y H:i'),
                'total_duration' => $totalDuration,
                'info' => [
                    'Status Akhir' => $dokumen->status === 'approved_data_sudah_terkirim' ? 'Approved Data Sudah Terkirim' : 'Selesai',
                    'Total waktu proses' => $totalDuration,
                    'Diselesaikan oleh' => $this->getRoleDisplayName($dokumen->current_handler),
                ]
            ];
        }

        return $events;
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $now = Carbon::now();

        return [
            'total_documents' => Dokumen::count(),
            'active_processing' => Dokumen::whereNotIn('status', ['approved_data_sudah_terkirim', 'rejected_data_tidak_lengkap'])->count(),
            'completed_today' => Dokumen::where('status', 'approved_data_sudah_terkirim')
                ->whereDate('updated_at', $now->toDateString())
                ->count(),
            'overdue_documents' => Dokumen::whereNotNull('deadline_at')
                ->where('deadline_at', '<', $now)
                ->whereNotIn('status', [
                    'approved_data_sudah_terkirim',
                    'rejected_data_tidak_lengkap',
                    'selesai',
                    'completed',
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                ])
                ->count(),
            'avg_processing_time' => $this->calculateAverageProcessingTime(),
            'fastest_department' => $this->getFastestDepartment(),
            'slowest_department' => $this->getSlowestDepartment(),
        ];
    }

    /**
     * Calculate progress percentage based on status
     */
    private function calculateProgress($dokumen)
    {
        // Calculate progress based on current_handler and status
        $handler = $dokumen->current_handler ?? 'ibuA';
        $status = $dokumen->status ?? 'draft';

        // Get status_pembayaran from model or database
        $statusPembayaran = null;
        if (isset($dokumen->status_pembayaran)) {
            $statusPembayaran = $dokumen->status_pembayaran;
        } elseif (isset($dokumen->id)) {
            // Try to get from database directly only if id is available
            $statusPembayaran = \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
        }

        // If document is completed or payment is completed
        if (in_array($status, ['selesai', 'approved_data_sudah_terkirim', 'completed']) || $statusPembayaran === 'sudah_dibayar') {
            return 100;
        }

        // Normalize handler name to canonical form
        $handler = $this->normalizeHandlerName($handler);

        // Calculate progress based on handler position in workflow
        $handlerProgress = [
            'ibuA' => 20,          // Ibu Tarapul (step 1)
            'ibuB' => 40,          // Team Verifikasi (step 2)
            'perpajakan' => 60,    // Team Perpajakan (step 3)
            'akutansi' => 80,      // Team Akutansi (step 4)
            'pembayaran' => 100,   // Pembayaran (step 5)
        ];

        $baseProgress = $handlerProgress[$handler] ?? 20;

        // Adjust based on status within handler
        if ($status == 'draft' && $handler == 'ibuA') {
            return 0;
        }

        if ($status == 'sedang diproses') {
            // Add 10% if being processed
            return min($baseProgress + 10, 100);
        }

        if (strpos($status, 'sent_to_') === 0) {
            // Document sent to next handler - use base progress
            return $baseProgress;
        }

        if (strpos($status, 'pending_approval') === 0) {
            // Pending approval - slightly less than base
            return max($baseProgress - 5, 0);
        }

        if (strpos($status, 'returned') === 0) {
            // Returned - go back to previous handler progress
            return max($baseProgress - 20, 0);
        }

        // Default: use base progress
        return $baseProgress;
    }

    /**
     * Normalize handler name to canonical form
     * Handles various handler name variants stored in database
     */
    private function normalizeHandlerName($handler)
    {
        // Convert to lowercase for consistent comparison
        $handlerLower = strtolower($handler ?? '');

        // Map all possible handler name variants to canonical names
        $handlerAliases = [
            // ibuA variants
            'ibua' => 'ibuA',
            'ibu_a' => 'ibuA',
            'ibutarapul' => 'ibuA',
            'ibu_tarapul' => 'ibuA',
            'ibu tarapul' => 'ibuA',
            'tarapul' => 'ibuA',

            // ibuB variants (Team Verifikasi)
            'ibub' => 'ibuB',
            'ibu_b' => 'ibuB',
            'ibuyuni' => 'ibuB',
            'ibu_yuni' => 'ibuB',
            'ibu yuni' => 'ibuB',
            'verifikasi' => 'ibuB',
            'team_verifikasi' => 'ibuB',
            'team verifikasi' => 'ibuB',
            'teamverifikasi' => 'ibuB',

            // Perpajakan variants
            'perpajakan' => 'perpajakan',
            'team_perpajakan' => 'perpajakan',
            'team perpajakan' => 'perpajakan',
            'teamperpajakan' => 'perpajakan',
            'pajak' => 'perpajakan',

            // Akutansi variants
            'akutansi' => 'akutansi',
            'team_akutansi' => 'akutansi',
            'team akutansi' => 'akutansi',
            'teamakutansi' => 'akutansi',
            'akuntansi' => 'akutansi',

            // Pembayaran variants
            'pembayaran' => 'pembayaran',
            'team_pembayaran' => 'pembayaran',
            'team pembayaran' => 'pembayaran',
            'teampembayaran' => 'pembayaran',
            'payment' => 'pembayaran',
        ];

        return $handlerAliases[$handlerLower] ?? 'ibuA';
    }

    /**
     * Get status badge color
     */
    private function getStatusBadgeColor($status)
    {
        $colorMap = [
            'draft' => '#6c757d', // Gray
            'sedang diproses' => '#083E40', // Green (theme)
            'menunggu_verifikasi' => '#083E40', // Green (theme)
            'pending_approval_ibub' => '#ffc107', // Yellow
            'sent_to_ibub' => '#0a4f52', // Dark green
            'proses_ibub' => '#ffc107', // Yellow
            'sent_to_perpajakan' => '#0a4f52', // Dark green
            'proses_perpajakan' => '#0a4f52', // Dark green
            'sent_to_akutansi' => '#6f42c1', // Purple
            'proses_akutansi' => '#6f42c1', // Purple
            'menunggu_approved_pengiriman' => '#fd7e14', // Orange
            'proses_pembayaran' => '#6f42c1', // Purple
            'approved_data_sudah_terkirim' => '#889717', // Green (theme)
            'rejected_data_tidak_lengkap' => '#dc3545', // Red
            'selesai' => '#889717', // Green (theme)
        ];

        return $colorMap[$status] ?? '#6c757d';
    }

    /**
     * Get progress bar color
     */
    private function getProgressColor($status)
    {
        $progress = $this->calculateProgress((object) ['status' => $status]);

        if ($progress <= 30)
            return '#dc3545'; // Red
        if ($progress <= 60)
            return '#ffc107'; // Yellow
        if ($progress <= 90)
            return '#083E40'; // Green (theme)
        return '#889717'; // Green (theme)
    }

    /**
     * Check if document is overdue
     */
    private function isDocumentOverdue($dokumen)
    {
        if (!$dokumen->deadline_at)
            return false;

        // Dokumen yang sudah terkirim ke perpajakan/akutansi/pembayaran tidak dianggap terlambat
        // karena deadline Ibu Yuni sudah tidak berlaku lagi setelah dokumen terkirim
        $excludedStatuses = [
            'approved_data_sudah_terkirim',
            'rejected_data_tidak_lengkap',
            'selesai',
            'completed',
            'sent_to_perpajakan',
            'sent_to_akutansi',
            'sent_to_pembayaran',
            'pending_approval_perpajakan',
            'pending_approval_akutansi',
            'pending_approval_pembayaran',
        ];

        return Carbon::now()->greaterThan($dokumen->deadline_at) &&
            !in_array($dokumen->status, $excludedStatuses);
    }

    /**
     * Get deadline information based on elapsed time since document approval
     * Color coding:
     * - Green (aman): < 1 day elapsed
     * - Yellow (warning): 1-2 days elapsed  
     * - Red (danger): >= 2 days elapsed
     */
    private function getDeadlineInfo($dokumen)
    {
        // Get the approval timestamp - when the document started being processed
        // This is when the "timer" begins
        $startTime = null;

        // Priority: processed_at (when IbuB approved) -> received_at from roleData -> deadline_at
        if ($dokumen->processed_at) {
            $startTime = Carbon::parse($dokumen->processed_at);
        } else {
            // Try to get from roleData - when received by IbuB (approval from inbox)
            $ibubData = null;
            if (method_exists($dokumen, 'getDataForRole')) {
                $ibubData = $dokumen->getDataForRole('ibub');
            } elseif (isset($dokumen->roleData)) {
                $ibubData = $dokumen->roleData->where('role_code', 'ibub')->first();
            }

            if ($ibubData && isset($ibubData->processed_at) && $ibubData->processed_at) {
                $startTime = Carbon::parse($ibubData->processed_at);
            } elseif ($ibubData && isset($ibubData->received_at) && $ibubData->received_at) {
                $startTime = Carbon::parse($ibubData->received_at);
            }
        }

        // If no approval time found, check deadline_at as fallback for display
        if (!$startTime) {
            if (!$dokumen->deadline_at) {
                return null;
            }
            // Show deadline date if no start time
            return [
                'date' => Carbon::parse($dokumen->deadline_at)->format('d M Y, H:i'),
                'text' => 'Belum diproses',
                'elapsed' => '',
                'class' => 'safe',
                'color' => 'success'
            ];
        }

        $now = Carbon::now();

        // Calculate elapsed time since approval
        $diff = $startTime->diff($now);
        $totalHours = ($diff->days * 24) + $diff->h;
        $totalMinutes = $totalHours * 60 + $diff->i;

        // Format elapsed time as "X hari Y jam Z menit"
        $elapsedParts = [];
        if ($diff->days > 0) {
            $elapsedParts[] = $diff->days . ' hari';
        }
        if ($diff->h > 0) {
            $elapsedParts[] = $diff->h . ' jam';
        }
        if ($diff->i > 0 || empty($elapsedParts)) {
            $elapsedParts[] = $diff->i . ' menit';
        }
        $elapsedText = implode(' ', $elapsedParts);

        // Determine color based on elapsed time (in hours)
        // < 24 hours = green (aman)
        // 24-72 hours = yellow (warning)
        // >= 72 hours = red (danger)
        $class = 'safe';
        $color = 'success';
        $statusText = 'AMAN';

        if ($totalHours >= 72) {
            $class = 'danger';
            $color = 'danger';
            $statusText = 'TERLAMBAT';
        } elseif ($totalHours >= 24) {
            $class = 'warning';
            $color = 'warning';
            $statusText = 'PERINGATAN';
        }

        return [
            'date' => $startTime->format('d M Y, H:i'),
            'text' => $statusText,
            'elapsed' => $elapsedText,
            'class' => $class,
            'color' => $color
        ];
    }

    /**
     * Calculate duration between two dates
     */
    private function calculateDuration($from, $to)
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        $diff = $from->diff($to);

        $parts = [];
        if ($diff->y > 0)
            $parts[] = $diff->y . ' tahun';
        if ($diff->m > 0)
            $parts[] = $diff->m . ' bulan';
        if ($diff->d > 0)
            $parts[] = $diff->d . ' hari';
        if ($diff->h > 0)
            $parts[] = $diff->h . ' jam';
        if ($diff->i > 0)
            $parts[] = $diff->i . ' menit';

        return empty($parts) ? 'kurang dari 1 menit' : implode(' ', $parts);
    }

    /**
     * Get display name for role
     */
    private function getRoleDisplayName($role)
    {
        // Normalize handler name first to handle all variants
        $normalizedRole = $this->normalizeHandlerName($role);

        $roleMap = [
            'ibuA' => 'Ibu Tarapul',
            'ibuB' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Pembayaran',
        ];

        return $roleMap[$normalizedRole] ?? $role;
    }

    /**
     * Get workflow timeline with inbox step for transparency
     * Returns timeline data showing when document was sent, received in inbox, and processed by each role
     */
    private function getWorkflowTimeline($dokumen): array
    {
        $roleOrder = [
            'ibuA' => ['label' => 'Bagian', 'icon' => '📋'],
            'ibub' => ['label' => 'Verif', 'icon' => '✓'],
            'perpajakan' => ['label' => 'Perpajakan', 'icon' => '💰'],
            'akutansi' => ['label' => 'Akutansi', 'icon' => '📊'],
            'pembayaran' => ['label' => 'Pembayaran', 'icon' => '💳'],
        ];

        $currentHandler = $dokumen->current_handler ?? 'ibuA';

        // Normalize handler to match roleOrder keys
        $normalizedHandler = strtolower($currentHandler);
        if ($normalizedHandler === 'verifikasi' || $normalizedHandler === 'ibub') {
            $normalizedHandler = 'ibub';
        }

        $status = $dokumen->status ?? 'draft';
        $roleKeys = array_keys($roleOrder);
        $currentIndex = array_search($normalizedHandler, $roleKeys);

        // Determine if document is in inbox (sent but not yet approved/processed by current role)
        $isInInbox = false;
        if ($currentHandler !== 'ibuA') {
            // Check roleStatuses to see if current role has approved the document
            $hasApproved = false;
            if (method_exists($dokumen, 'roleStatuses') && $dokumen->roleStatuses) {
                $roleStatus = $dokumen->roleStatuses->where('role_code', $currentHandler)->first();
                if (!$roleStatus) {
                    // Try with lowercase ibub for verifikasi
                    $roleStatus = $dokumen->roleStatuses->where('role_code', 'ibub')->first();
                }
                if ($roleStatus && $roleStatus->status === \App\Models\DokumenStatus::STATUS_APPROVED) {
                    $hasApproved = true;
                }
            }

            // Document is in inbox if current role hasn't approved it yet
            // But we also need to check if document is actually in inbox (sent_to status)
            $isInboxStatus = in_array($status, [
                'sent_to_ibub',
                'menunggu_verifikasi',
                'pending_approval_ibub',
                'sent_to_perpajakan',
                'pending_approval_perpajakan',
                'sent_to_akutansi',
                'pending_approval_akutansi',
                'sent_to_pembayaran',
                'pending_approval_pembayaran',
            ]);

            // If role has already approved, document is NOT in inbox (it's in daftar dokumen)
            $isInInbox = !$hasApproved && $isInboxStatus;
        }

        $timeline = [];
        foreach ($roleKeys as $index => $role) {
            $roleInfo = $roleOrder[$role];
            $roleData = null;

            if (method_exists($dokumen, 'getDataForRole')) {
                $roleData = $dokumen->getDataForRole($role);
            } elseif (isset($dokumen->roleData)) {
                $roleData = $dokumen->roleData->where('role_code', $role)->first();
            }

            // Determine step status
            $stepStatus = 'pending';
            $inboxStatus = 'pending';

            if ($index < $currentIndex) {
                $stepStatus = 'completed';
                $inboxStatus = 'completed';
            } elseif ($index === $currentIndex) {
                if ($isInInbox) {
                    $stepStatus = 'waiting'; // In inbox, waiting to be processed
                    $inboxStatus = 'active';
                } else {
                    $stepStatus = 'active'; // Being processed
                    $inboxStatus = 'completed';
                }
            }

            // Get timestamps
            $sentAt = null;
            $receivedAt = null;
            $processedAt = null;
            $elapsedInRole = null;

            if ($roleData) {
                $receivedAt = $roleData->received_at;
                $processedAt = $roleData->processed_at;

                if ($receivedAt) {
                    $now = Carbon::now();
                    $elapsedInRole = Carbon::parse($receivedAt)->diffForHumans($now, true);
                }
            }

            // Get sent_at from previous role or dokumen timestamps
            if ($role === 'ibub' && $dokumen->sent_to_ibub_at) {
                $sentAt = $dokumen->sent_to_ibub_at;
            } elseif ($index > 0) {
                $prevRole = $roleKeys[$index - 1];
                $prevRoleData = null;
                if (method_exists($dokumen, 'getDataForRole')) {
                    $prevRoleData = $dokumen->getDataForRole($prevRole);
                } elseif (isset($dokumen->roleData)) {
                    $prevRoleData = $dokumen->roleData->where('role_code', $prevRole)->first();
                }
                if ($prevRoleData && $prevRoleData->processed_at) {
                    $sentAt = $prevRoleData->processed_at;
                }
            }

            $timeline[] = [
                'role' => $role,
                'label' => $roleInfo['label'],
                'icon' => $roleInfo['icon'],
                'status' => $stepStatus,
                'inbox_status' => $inboxStatus,
                'is_current' => $index === $currentIndex,
                'in_inbox' => $index === $currentIndex && $isInInbox,
                'sent_at' => $sentAt ? Carbon::parse($sentAt)->format('d M Y, H:i') : null,
                'received_at' => $receivedAt ? Carbon::parse($receivedAt)->format('d M Y, H:i') : null,
                'processed_at' => $processedAt ? Carbon::parse($processedAt)->format('d M Y, H:i') : null,
                'elapsed' => $elapsedInRole,
            ];
        }

        return [
            'steps' => $timeline,
            'current_handler' => $currentHandler,
            'current_handler_display' => $this->getRoleDisplayName($currentHandler),
            'is_in_inbox' => $isInInbox,
            'total_steps' => count($roleKeys),
            'current_step' => $currentIndex + 1,
        ];
    }

    /**
     * Get return destination
     */
    private function getReturnDestination($dokumen)
    {
        if ($dokumen->returned_to_ibua_at)
            return 'Ibu Tarapul';
        if ($dokumen->department_returned_at)
            return 'Ibu Tarapul (Department)';
        if ($dokumen->bidang_returned_at)
            return 'Team Verifikasi';
        return 'Tidak Diketahui';
    }

    /**
     * Get status display name in Indonesian
     */
    private function getStatusDisplayName($status)
    {
        $statusMap = [
            'draft' => 'Draft',
            'sedang diproses' => 'Sedang Diproses',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pending_approval_ibub' => 'Menunggu Persetujuan Team Verifikasi',
            'sent_to_ibub' => 'Terkirim ke Team Verifikasi',
            'proses_ibub' => 'Diproses Team Verifikasi',
            'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
            'proses_perpajakan' => 'Diproses Team Perpajakan',
            'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
            'proses_akutansi' => 'Diproses Team Akutansi',
            'menunggu_approved_pengiriman' => 'Menunggu Persetujuan Pengiriman',
            'proses_pembayaran' => 'Diproses Team Pembayaran',
            'sent_to_pembayaran' => 'Terkirim ke Team Pembayaran',
            'approved_data_sudah_terkirim' => 'Data Sudah Terkirim',
            'rejected_data_tidak_lengkap' => 'Ditolak - Data Tidak Lengkap',
            'selesai' => 'Selesai',
            'returned_to_ibua' => 'Dikembalikan ke Ibu Tarapul',
            'returned_to_department' => 'Dikembalikan ke Department',
            'returned_to_bidang' => 'Dikembalikan ke Bidang',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime()
    {
        $completedDocs = Dokumen::where('status', 'approved_data_sudah_terkirim')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($completedDocs->isEmpty()) {
            return '0 hari';
        }

        $totalHours = $completedDocs->sum(function ($doc) {
            return $doc->created_at->diffInHours($doc->updated_at);
        });

        $avgHours = $totalHours / $completedDocs->count();

        if ($avgHours < 24) {
            return round($avgHours, 1) . ' jam';
        } else {
            return round($avgHours / 24, 1) . ' hari';
        }
    }

    /**
     * Get fastest processing department
     */
    private function getFastestDepartment()
    {
        $departments = ['ibuB', 'perpajakan', 'akutansi', 'pembayaran'];
        $avgTimes = [];

        foreach ($departments as $dept) {
            $avgTimes[$dept] = $this->getDepartmentAvgProcessingTime($dept);
        }

        return empty($avgTimes) ? '-' : array_keys($avgTimes, min($avgTimes))[0];
    }

    /**
     * Get slowest processing department
     */
    private function getSlowestDepartment()
    {
        $departments = ['ibuB', 'perpajakan', 'akutansi', 'pembayaran'];
        $avgTimes = [];

        foreach ($departments as $dept) {
            $avgTimes[$dept] = $this->getDepartmentAvgProcessingTime($dept);
        }

        return empty($avgTimes) ? '-' : array_keys($avgTimes, max($avgTimes))[0];
    }

    /**
     * Get department average processing time
     */
    private function getDepartmentAvgProcessingTime($dept)
    {
        $completedDocs = Dokumen::where('status', 'approved_data_sudah_terkirim')
            ->where('created_by', $dept)
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($completedDocs->isEmpty()) {
            return 999; // High number for departments with no completed docs
        }

        $totalHours = $completedDocs->sum(function ($doc) {
            return $doc->created_at->diffInHours($doc->updated_at);
        });

        return $totalHours / $completedDocs->count();
    }

    /**
     * Display tracking dokumen page for all roles (without header and statistics)
     */
    public function trackingDokumen(Request $request)
    {
        // Get paginated documents with latest status and apply search filter
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;

        $documents = $this->getDocumentsWithTracking($request, $perPage);

        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        // Determine module based on user role
        $user = auth()->user();
        $module = 'ibua'; // default
        if ($user) {
            $role = strtolower($user->role ?? '');
            if (in_array($role, ['ibub', 'pembayaran', 'akutansi', 'perpajakan'])) {
                $module = $role;
            }
        }

        return view('tracking.dokumen', [
            'documents' => $documents,
            'filterData' => $filterData,
            'search' => $request->get('search', ''),
            'module' => $module,
            'title' => 'Tracking Dokumen',
            'menuDashboard' => '',
            'menuDokumen' => '',
            'menuDaftarDokumen' => '',
            'menuRekapanDokumen' => '',
        ]);
    }

    /**
     * Show workflow tracking page for a document
     */
    public function showWorkflow($id)
    {
        $dokumen = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
            ->findOrFail($id);

        $workflowStages = $this->generateWorkflowStages($dokumen);

        // Load activity logs for each stage
        try {
            $dokumen->load('activityLogs');
            $activityLogsByStage = $dokumen->activityLogs->groupBy('stage');
        } catch (\Exception $e) {
            // If table doesn't exist yet, use empty collection
            $activityLogsByStage = collect();
        }

        // Detect user role to set appropriate module and dashboard URL
        $user = auth()->user();
        $userRole = strtolower($user->role ?? $user->name ?? 'owner');
        $module = 'owner';
        $dashboardUrl = '/tracking-dokumen'; // Default to tracking dokumen for all roles

        // Set module and dashboard URL based on user role
        if (in_array($userRole, ['ibua', 'ibutara', 'ibu a'])) {
            $module = 'ibua';
            $dashboardUrl = '/dashboard';
        } elseif (in_array($userRole, ['ibub', 'ibuyuni', 'ibu b', 'team verifikasi'])) {
            $module = 'ibub';
            $dashboardUrl = '/dashboardB';
        } elseif (in_array($userRole, ['pembayaran', 'team pembayaran'])) {
            $module = 'pembayaran';
            $dashboardUrl = '/dashboardPembayaran';
        } elseif (in_array($userRole, ['akutansi', 'team akutansi'])) {
            $module = 'akutansi';
            $dashboardUrl = '/dashboardAkutansi';
        } elseif (in_array($userRole, ['perpajakan', 'team perpajakan'])) {
            $module = 'perpajakan';
            $dashboardUrl = '/dashboardPerpajakan';
        } elseif (in_array($userRole, ['admin', 'owner'])) {
            $module = 'owner';
            $dashboardUrl = '/owner/dashboard';
        }

        return view('owner.workflow', compact('dokumen', 'workflowStages', 'activityLogsByStage'))
            ->with('title', 'Workflow Tracking - ' . $dokumen->nomor_agenda)
            ->with('module', $module)
            ->with('menuDashboard', '')
            ->with('menuDokumen', '') // Default value - prevents undefined variable error
            ->with('menuDaftarDokumen', '') // Default value
            ->with('menuRekapanDokumen', '') // Default value
            ->with('menuRekapKeterlambatan', '') // Default value
            ->with('dashboardUrl', $dashboardUrl);
    }

    /**
     * Generate workflow stages for visualization
     */
    private function generateWorkflowStages($dokumen)
    {
        $stages = [];
        $currentTime = now();
        $returnEvents = $this->getReturnEvents($dokumen);

        // Stage 1: ibutara (Ibu Tarapul) - Always completed
        $stages[] = [
            'id' => 'sender',
            'name' => 'Ibu Tarapul',
            'label' => 'ibutara',
            'status' => 'completed',
            'timestamp' => $dokumen->created_at,
            'icon' => 'fa-user',
            'color' => '#10b981',
            'description' => 'Dokumen Dibuat',
            'details' => [
                'Dibuat oleh' => 'Ibu Tarapul',
                'Nomor Agenda' => $dokumen->nomor_agenda,
                'Nomor SPP' => $dokumen->nomor_spp,
                'Nilai' => 'Rp. ' . number_format($dokumen->nilai_rupiah, 0, ',', '.'),
            ],
            'hasReturn' => false,
            'returnInfo' => null
        ];

        // Stage 2: REVIEWER (Ibu Yuni)
        $reviewerStatus = 'pending';
        $reviewerTimestamp = null;
        $reviewerDescription = 'Menunggu';
        $reviewerReturnInfo = $this->getReturnInfoForStage($dokumen, 'reviewer', $returnEvents);
        $reviewerCycleInfo = $this->getCycleInfo($dokumen, 'reviewer');

        if ($dokumen->getDataForRole('ibub')?->received_at) {
            $reviewerStatus = 'completed';
            $reviewerTimestamp = $dokumen->getDataForRole('ibub')->received_at;
            $reviewerDescription = 'Dikirim ke Ibu Yuni';

            // Check if this is a re-send after return
            if ($reviewerCycleInfo && $reviewerCycleInfo['isResend']) {
                $reviewerDescription = 'Dikirim kembali ke Ibu Yuni (Attempt ' . $reviewerCycleInfo['attemptCount'] . ')';
            }
        }

        if ($dokumen->processed_at) {
            $reviewerStatus = 'completed';
            $reviewerTimestamp = $dokumen->processed_at;
            $reviewerDescription = 'Diproses Ibu Yuni';

            // Check if processed after return
            if ($reviewerCycleInfo && $reviewerCycleInfo['isResend']) {
                $reviewerDescription = 'Diproses Ibu Yuni (Attempt ' . $reviewerCycleInfo['attemptCount'] . ')';
            }
        }

        // Check if returned from this stage
        if ($reviewerReturnInfo && !$reviewerCycleInfo['isResend']) {
            $reviewerStatus = 'returned';
            if (!$reviewerTimestamp) {
                $reviewerTimestamp = $dokumen->getDataForRole('ibub')?->received_at ?? $dokumen->created_at;
            }
        }

        // Check if reviewer stage is overdue
        $reviewerRoleData = $dokumen->getDataForRole('ibub');
        $reviewerIsOverdue = false;
        $reviewerDeadlineInfo = null;
        if ($reviewerRoleData && $reviewerRoleData->deadline_at && !$reviewerRoleData->processed_at) {
            if (now()->greaterThan($reviewerRoleData->deadline_at)) {
                $reviewerIsOverdue = true;
                $daysOverdue = now()->diffInDays($reviewerRoleData->deadline_at);
                $reviewerDeadlineInfo = [
                    'deadline_at' => $reviewerRoleData->deadline_at,
                    'days_overdue' => $daysOverdue,
                    'deadline_note' => $reviewerRoleData->deadline_note,
                ];
            }
        }

        $stages[] = [
            'id' => 'reviewer',
            'name' => 'Team Verifikasi',
            'label' => 'teamverifikasi',
            'status' => $reviewerStatus,
            'timestamp' => $reviewerTimestamp,
            'icon' => 'fa-user-check',
            'color' => $reviewerStatus === 'completed' ? '#10b981' : ($reviewerStatus === 'processing' ? '#3b82f6' : ($reviewerStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $reviewerDescription,
            'details' => $reviewerTimestamp ? [
                'Dikirim pada' => $dokumen->getDataForRole('ibub')?->received_at ? $dokumen->getDataForRole('ibub')->received_at->format('d M Y H:i') : '-',
                'Diproses pada' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '-',
            ] : [],
            'hasReturn' => $reviewerReturnInfo !== null,
            'returnInfo' => $reviewerReturnInfo,
            'hasCycle' => $reviewerCycleInfo['hasCycle'],
            'cycleInfo' => $reviewerCycleInfo,
            'isOverdue' => $reviewerIsOverdue,
            'deadlineInfo' => $reviewerDeadlineInfo
        ];

        // Stage 3: TAX (Team Perpajakan)
        $taxStatus = 'pending';
        $taxTimestamp = null;
        $taxDescription = 'Menunggu';
        $taxReturnInfo = $this->getReturnInfoForStage($dokumen, 'tax', $returnEvents);
        $taxCycleInfo = $this->getCycleInfo($dokumen, 'tax');

        if ($dokumen->getDataForRole('perpajakan')?->received_at) {
            $taxStatus = 'processing';
            $taxTimestamp = $dokumen->getDataForRole('perpajakan')->received_at;
            $taxDescription = 'Dikirim ke Team Perpajakan';

            // Check if this is a re-send after return
            if ($taxCycleInfo && $taxCycleInfo['isResend']) {
                $taxDescription = 'Dikirim kembali ke Team Perpajakan (Attempt ' . $taxCycleInfo['attemptCount'] . ')';
            }
        }

        if ($dokumen->processed_perpajakan_at) {
            $taxStatus = 'completed';
            $taxTimestamp = $dokumen->processed_perpajakan_at;
            $taxDescription = 'Diproses Team Perpajakan';

            // Check if completed after return
            if ($taxCycleInfo && $taxCycleInfo['isResend']) {
                $taxDescription = 'Diproses Team Perpajakan (Attempt ' . $taxCycleInfo['attemptCount'] . ')';
            }
        }

        // Check if returned from this stage (only if not re-sent)
        if ($taxReturnInfo && !$taxCycleInfo['isResend']) {
            // Only show as returned if not yet re-sent
            $perpajakanReceivedAt = $dokumen->getDataForRole('perpajakan')?->received_at;
            if (
                !$perpajakanReceivedAt ||
                ($dokumen->returned_from_perpajakan_at &&
                    $perpajakanReceivedAt->lte($dokumen->returned_from_perpajakan_at))
            ) {
                $taxStatus = 'returned';
                if (!$taxTimestamp) {
                    $taxTimestamp = $perpajakanReceivedAt ?? $dokumen->processed_at;
                }
            }
        }

        // Check if tax stage is overdue
        $taxRoleData = $dokumen->getDataForRole('perpajakan');
        $taxIsOverdue = false;
        $taxDeadlineInfo = null;
        if ($taxRoleData && $taxRoleData->deadline_at && !$taxRoleData->processed_at) {
            if (now()->greaterThan($taxRoleData->deadline_at)) {
                $taxIsOverdue = true;
                $daysOverdue = now()->diffInDays($taxRoleData->deadline_at);
                $taxDeadlineInfo = [
                    'deadline_at' => $taxRoleData->deadline_at,
                    'days_overdue' => $daysOverdue,
                    'deadline_note' => $taxRoleData->deadline_note,
                ];
            }
        }

        $stages[] = [
            'id' => 'tax',
            'name' => 'Team Perpajakan',
            'label' => 'team perpajakan',
            'status' => $taxStatus,
            'timestamp' => $taxTimestamp,
            'icon' => 'fa-file-invoice',
            'color' => $taxStatus === 'completed' ? '#10b981' : ($taxStatus === 'processing' ? '#3b82f6' : ($taxStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $taxDescription,
            'details' => $taxTimestamp ? [
                'Dikirim pada' => $dokumen->getDataForRole('perpajakan')?->received_at ? $dokumen->getDataForRole('perpajakan')->received_at->format('d M Y H:i') : '-',
                'Diproses pada' => $dokumen->processed_perpajakan_at ? $dokumen->processed_perpajakan_at->format('d M Y H:i') : '-',
                'Status' => $dokumen->status_perpajakan ?? '-',
            ] : [],
            'hasReturn' => $taxReturnInfo !== null,
            'returnInfo' => $taxReturnInfo,
            'hasCycle' => $taxCycleInfo['hasCycle'],
            'cycleInfo' => $taxCycleInfo,
            'isOverdue' => $taxIsOverdue,
            'deadlineInfo' => $taxDeadlineInfo
        ];

        // Stage 4: ACCOUNTING (Team Akutansi)
        $accountingStatus = 'pending';
        $accountingTimestamp = null;
        $accountingDescription = 'Menunggu';
        $accountingReturnInfo = $this->getReturnInfoForStage($dokumen, 'accounting', $returnEvents);

        // Check if sent to akutansi (using status or sent_to_akutansi_at if exists)
        if ($dokumen->status === 'sent_to_akutansi' || $dokumen->status === 'processed_by_akutansi') {
            $accountingStatus = 'processing';
            $accountingTimestamp = $dokumen->updated_at;
            $accountingDescription = 'Dikirim ke Team Akutansi';
        }

        // Try to get sent_to_akutansi_at from database (might not exist in model fillable)
        $sentToAkutansiAt = null;
        try {
            $sentToAkutansiAt = $dokumen->getAttribute('sent_to_akutansi_at') ??
                (\DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_akutansi_at') ?
                    \Carbon\Carbon::parse(\DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_akutansi_at')) : null);
            if ($sentToAkutansiAt) {
                $accountingStatus = 'processing';
                $accountingTimestamp = $sentToAkutansiAt;
                $accountingDescription = 'Dikirim ke Team Akutansi';
            }
        } catch (\Exception $e) {
            // Field might not exist, use status
        }

        if ($dokumen->status === 'processed_by_akutansi' || $dokumen->nomor_miro) {
            $accountingStatus = 'completed';
            $accountingTimestamp = $dokumen->updated_at;
            $accountingDescription = 'Diproses Team Akutansi';
        }

        // Check if returned from this stage
        if ($accountingReturnInfo) {
            $accountingStatus = 'returned';
            if (!$accountingTimestamp) {
                $accountingTimestamp = $sentToAkutansiAt ?? $dokumen->processed_perpajakan_at;
            }
        }

        // Check if accounting stage is overdue
        $accountingRoleData = $dokumen->getDataForRole('akutansi');
        $accountingIsOverdue = false;
        $accountingDeadlineInfo = null;
        if ($accountingRoleData && $accountingRoleData->deadline_at && !$accountingRoleData->processed_at) {
            if (now()->greaterThan($accountingRoleData->deadline_at)) {
                $accountingIsOverdue = true;
                $daysOverdue = now()->diffInDays($accountingRoleData->deadline_at);
                $accountingDeadlineInfo = [
                    'deadline_at' => $accountingRoleData->deadline_at,
                    'days_overdue' => $daysOverdue,
                    'deadline_note' => $accountingRoleData->deadline_note,
                ];
            }
        }

        $stages[] = [
            'id' => 'accounting',
            'name' => 'Team Akutansi',
            'label' => 'team akutansi',
            'status' => $accountingStatus,
            'timestamp' => $accountingTimestamp,
            'icon' => 'fa-calculator',
            'color' => $accountingStatus === 'completed' ? '#10b981' : ($accountingStatus === 'processing' ? '#3b82f6' : ($accountingStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $accountingDescription,
            'details' => $accountingTimestamp ? [
                'Nomor MIRO' => $dokumen->nomor_miro ?? '-',
                'Status' => $dokumen->status,
            ] : [],
            'hasReturn' => $accountingReturnInfo !== null,
            'returnInfo' => $accountingReturnInfo,
            'isOverdue' => $accountingIsOverdue,
            'deadlineInfo' => $accountingDeadlineInfo
        ];

        // Stage 5: PAYMENT (Pembayaran)
        $paymentStatus = 'pending';
        $paymentTimestamp = null;
        $paymentDescription = 'Menunggu';

        // Get sent_to_pembayaran_at from model or database
        $sentToPembayaranAt = null;
        if (isset($dokumen->sent_to_pembayaran_at)) {
            $sentToPembayaranAt = $dokumen->sent_to_pembayaran_at;
        } else {
            try {
                $sentToPembayaranAt = \DB::table('dokumens')->where('id', $dokumen->id)->value('sent_to_pembayaran_at');
                if ($sentToPembayaranAt) {
                    $sentToPembayaranAt = \Carbon\Carbon::parse($sentToPembayaranAt);
                }
            } catch (\Exception $e) {
                // Field might not exist
            }
        }

        // Check if sent to pembayaran
        if ($dokumen->status === 'sent_to_pembayaran' || $dokumen->current_handler === 'pembayaran' || $sentToPembayaranAt) {
            $paymentStatus = 'processing';
            $paymentTimestamp = $sentToPembayaranAt ?? $dokumen->updated_at;
            $paymentDescription = 'Dikirim ke Pembayaran';
        }

        // Get status_pembayaran from model or database
        $statusPembayaran = null;
        if (isset($dokumen->status_pembayaran)) {
            $statusPembayaran = $dokumen->status_pembayaran;
        } else {
            try {
                $statusPembayaran = \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
            } catch (\Exception $e) {
                // Field might not exist
            }
        }

        // Check if payment is completed
        if ($statusPembayaran === 'sudah_dibayar' || $dokumen->status === 'selesai' || $dokumen->status === 'approved_data_sudah_terkirim' || $dokumen->status === 'completed') {
            $paymentStatus = 'completed';
            if (!$paymentTimestamp) {
                $paymentTimestamp = $sentToPembayaranAt ?? $dokumen->updated_at;
            }
            $paymentDescription = 'Selesai Dibayar';
        }

        $stages[] = [
            'id' => 'payment',
            'name' => 'Pembayaran',
            'label' => 'pembayaran',
            'status' => $paymentStatus,
            'timestamp' => $paymentTimestamp,
            'icon' => 'fa-money-bill-wave',
            'color' => $paymentStatus === 'completed' ? '#10b981' : ($paymentStatus === 'processing' ? '#3b82f6' : ($paymentStatus === 'returned' ? '#ef4444' : '#9ca3af')),
            'description' => $paymentDescription,
            'details' => $paymentTimestamp ? [
                'Status Pembayaran' => $statusPembayaran ?? '-',
                'Status' => $dokumen->status,
            ] : [],
            'hasReturn' => false,
            'returnInfo' => null
        ];

        // Calculate durations between stages
        for ($i = 0; $i < count($stages); $i++) {
            if ($i > 0 && $stages[$i]['timestamp'] && $stages[$i - 1]['timestamp']) {
                $stages[$i]['duration'] = $this->calculateDuration($stages[$i - 1]['timestamp'], $stages[$i]['timestamp']);
            }
        }

        return $stages;
    }

    /**
     * Get all return events for a document
     */
    private function getReturnEvents($dokumen)
    {
        $returns = [];

        // Return from Perpajakan to Ibu Yuni
        if ($dokumen->returned_from_perpajakan_at) {
            $returns[] = [
                'from' => 'tax',
                'to' => 'reviewer',
                'timestamp' => $dokumen->returned_from_perpajakan_at,
                'reason' => $dokumen->alasan_pengembalian ?? 'Tidak ada alasan',
                'returned_by' => 'Team Perpajakan',
                'returned_to' => 'Ibu Yuni'
            ];
        }

        // Return from Ibu Yuni to Bidang
        if ($dokumen->bidang_returned_at) {
            $returns[] = [
                'from' => 'reviewer',
                'to' => 'bidang',
                'timestamp' => $dokumen->bidang_returned_at,
                'reason' => $dokumen->bidang_return_reason ?? 'Tidak ada alasan',
                'returned_by' => 'Ibu Yuni',
                'returned_to' => 'Bidang: ' . ($dokumen->target_bidang ?? 'Tidak diketahui')
            ];
        }

        // Return to Department
        if ($dokumen->department_returned_at) {
            $returns[] = [
                'from' => $dokumen->current_handler === 'perpajakan' ? 'tax' : ($dokumen->current_handler === 'akutansi' ? 'accounting' : 'reviewer'),
                'to' => 'department',
                'timestamp' => $dokumen->department_returned_at,
                'reason' => $dokumen->department_return_reason ?? 'Tidak ada alasan',
                'returned_by' => $this->getRoleDisplayName($dokumen->current_handler),
                'returned_to' => 'Department'
            ];
        }

        // Return to Ibu A
        if ($dokumen->returned_to_ibua_at) {
            $returns[] = [
                'from' => 'reviewer',
                'to' => 'sender',
                'timestamp' => $dokumen->returned_to_ibua_at,
                'reason' => $dokumen->alasan_pengembalian ?? 'Tidak ada alasan',
                'returned_by' => 'Ibu Yuni',
                'returned_to' => 'Ibu Tarapul'
            ];
        }

        return $returns;
    }

    /**
     * Get return info for a specific stage
     */
    private function getReturnInfoForStage($dokumen, $stageId, $returnEvents)
    {
        foreach ($returnEvents as $return) {
            if ($return['from'] === $stageId) {
                return $return;
            }
        }
        return null;
    }

    /**
     * Get cycle/loop information for a stage (return and re-send)
     */
    private function getCycleInfo($dokumen, $stageId)
    {
        $hasCycle = false;
        $isResend = false;
        $attemptCount = 1;
        $returnTimestamp = null;
        $resendTimestamp = null;

        if ($stageId === 'tax') {
            // Check if returned from perpajakan
            if ($dokumen->returned_from_perpajakan_at) {
                $hasCycle = true;
                $returnTimestamp = $dokumen->returned_from_perpajakan_at;
                $attemptCount = 1;

                // Check if sent back after return
                $perpajakanReceivedAt = $dokumen->getDataForRole('perpajakan')?->received_at;
                if (
                    $perpajakanReceivedAt &&
                    $perpajakanReceivedAt->gt($dokumen->returned_from_perpajakan_at)
                ) {
                    $isResend = true;
                    $resendTimestamp = $perpajakanReceivedAt;
                    $attemptCount = 2;

                    // Check if there's a fixed_at timestamp (indicates re-send after fix)
                    if ($dokumen->getAttribute('returned_from_perpajakan_fixed_at')) {
                        $resendTimestamp = $dokumen->getAttribute('returned_from_perpajakan_fixed_at');
                    }
                }
            }
        } elseif ($stageId === 'reviewer') {
            // Check if returned to bidang and sent back
            if ($dokumen->bidang_returned_at) {
                $hasCycle = true;
                $returnTimestamp = $dokumen->bidang_returned_at;
                $attemptCount = 1;

                // Check if processed again after return from bidang
                if (
                    $dokumen->processed_at &&
                    $dokumen->processed_at->gt($dokumen->bidang_returned_at)
                ) {
                    $isResend = true;
                    $resendTimestamp = $dokumen->processed_at;
                    $attemptCount = 2;
                }
            }

            // Check if returned to Ibu A and sent back
            if ($dokumen->returned_to_ibua_at) {
                $hasCycle = true;
                if (!$returnTimestamp || $dokumen->returned_to_ibua_at->gt($returnTimestamp)) {
                    $returnTimestamp = $dokumen->returned_to_ibua_at;
                }

                // Check if sent to Ibu B again after return
                $ibubReceivedAt = $dokumen->getDataForRole('ibub')?->received_at;
                if (
                    $ibubReceivedAt &&
                    $ibubReceivedAt->gt($dokumen->returned_to_ibua_at)
                ) {
                    $isResend = true;
                    if (!$resendTimestamp || $ibubReceivedAt->gt($resendTimestamp)) {
                        $resendTimestamp = $ibubReceivedAt;
                    }
                    $attemptCount = max($attemptCount, 2);
                }
            }
        }

        return [
            'hasCycle' => $hasCycle,
            'isResend' => $isResend,
            'attemptCount' => $attemptCount,
            'returnTimestamp' => $returnTimestamp,
            'resendTimestamp' => $resendTimestamp
        ];
    }

    /**
     * Display rekapan dokumen for owner (all documents from all roles)
     */
    public function rekapan(Request $request)
    {
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            // Dokumen selesai: status completed atau status_pembayaran = sudah_dibayar
            $query->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            // Dokumen belum selesai: tidak termasuk status selesai
            $query->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        $dokumens = $query->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get statistics
        $statistics = $this->getRekapanStatistics($selectedBagian);

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list with document counts
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        // Get document counts per bagian
        $bagianCounts = [];
        foreach ($bagianList as $code => $name) {
            $countQuery = Dokumen::where('bagian', $code);

            // Apply same filters as main query (year filter)
            if ($request->has('year') && $request->year) {
                $countQuery->where('tahun', $request->year);
            }

            // Apply search filter if exists
            if ($request->has('search') && $request->search) {
                $search = trim((string) $request->search);
                if (!empty($search)) {
                    $countQuery->where(function ($q) use ($search) {
                        $q->where('nomor_agenda', 'like', '%' . $search . '%')
                            ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                            ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                            ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                    });
                }
            }

            // Apply completion status filter if exists
            if ($completionFilter === 'selesai') {
                $countQuery->where(function ($q) {
                    $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            } elseif ($completionFilter === 'belum_selesai') {
                $countQuery->where(function ($q) {
                    $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->where(function ($subQ) {
                            $subQ->whereNull('status_pembayaran')
                                ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                        });
                });
            }

            $bagianCounts[$code] = $countQuery->count();
        }

        $completionFilter = $request->get('completion_status', '');

        return view('owner.rekapan', compact('dokumens', 'statistics', 'availableYears', 'bagianList', 'bagianCounts', 'selectedBagian', 'completionFilter'))
            ->with('title', 'Rekapan Dokumen - Owner')
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display rekapan dokumen by handler (Ibu Tarapul, Ibu Yuni, Team Perpajakan, Team Akutansi)
     */
    public function rekapanByHandler(Request $request, $handler)
    {
        // Validate handler
        $validHandlers = ['ibuA', 'ibuB', 'perpajakan', 'akutansi'];
        if (!in_array($handler, $validHandlers)) {
            abort(404, 'Handler tidak valid');
        }

        $handlerNames = [
            'ibuA' => 'Ibu Tarapul',
            'ibuB' => 'Ibu Yuni',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi'
        ];

        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Filter by handler
        if ($handler === 'ibuA') {
            // Ibu Tarapul: dokumen dengan current_handler = 'ibuA' atau status draft
            $query->where(function ($q) {
                $q->where('current_handler', 'ibuA')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'draft')
                            ->where(function ($subSubQ) {
                                $subSubQ->whereNull('current_handler')
                                    ->orWhere('current_handler', 'ibuA');
                            });
                    });
            });
        } elseif ($handler === 'ibuB') {
            // Ibu Yuni: dokumen dengan current_handler = 'ibuB' atau status sent_to_ibub
            $query->where(function ($q) {
                $q->where('current_handler', 'ibuB')
                    ->orWhere('status', 'sent_to_ibub')
                    ->orWhere('status', 'pending_approval_ibub')
                    ->orWhere('status', 'proses_ibub');
            });
        } elseif ($handler === 'perpajakan') {
            // Team Perpajakan: dokumen dengan current_handler = 'perpajakan' atau status sent_to_perpajakan
            $query->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_perpajakan');
            });
        } elseif ($handler === 'akutansi') {
            // Team Akutansi: dokumen dengan current_handler = 'akutansi' atau status sent_to_akutansi
            $query->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi');
            });
        } elseif ($handler === 'pembayaran') {
            // Team Pembayaran: dokumen dengan current_handler = 'pembayaran' atau status sent_to_pembayaran atau CSV imported
            $query->where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere(function ($csvQ) {
                        // Include CSV imported documents (exclusive to Pembayaran)
                        $csvQ->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                            $query->where('imported_from_csv', true);
                        });
                    });
            });
        }

        // Exclude CSV imported documents from all handlers except Pembayaran
        if ($handler !== 'pembayaran') {
            $query->when(\Schema::hasColumn('dokumens', 'imported_from_csv'), function ($query) {
                $query->where(function ($q) {
                    $q->where('imported_from_csv', false)
                        ->orWhereNull('imported_from_csv');
                });
            });
        }

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $query->where('bagian', $selectedBagian);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            $query->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            $query->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        $dokumens = $query->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get statistics (with handler filter)
        $statistics = $this->getRekapanStatistics($selectedBagian);

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list with document counts
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        // Get document counts per bagian (with handler filter)
        $bagianCounts = [];
        foreach ($bagianList as $code => $name) {
            $countQuery = Dokumen::where('bagian', $code);

            // Apply handler filter
            if ($handler === 'ibuA') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'ibuA')
                        ->orWhere(function ($subQ) {
                            $subQ->where('status', 'draft')
                                ->where(function ($subSubQ) {
                                    $subSubQ->whereNull('current_handler')
                                        ->orWhere('current_handler', 'ibuA');
                                });
                        });
                });
            } elseif ($handler === 'ibuB') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'ibuB')
                        ->orWhere('status', 'sent_to_ibub')
                        ->orWhere('status', 'pending_approval_ibub')
                        ->orWhere('status', 'proses_ibub');
                });
            } elseif ($handler === 'perpajakan') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'perpajakan')
                        ->orWhere('status', 'sent_to_perpajakan');
                });
            } elseif ($handler === 'akutansi') {
                $countQuery->where(function ($q) {
                    $q->where('current_handler', 'akutansi')
                        ->orWhere('status', 'sent_to_akutansi');
                });
            }

            // Apply same filters as main query
            if ($request->has('year') && $request->year) {
                $countQuery->where('tahun', $request->year);
            }

            if ($request->has('search') && $request->search) {
                $search = trim((string) $request->search);
                if (!empty($search)) {
                    $countQuery->where(function ($q) use ($search) {
                        $q->where('nomor_agenda', 'like', '%' . $search . '%')
                            ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                            ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                            ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                    });
                }
            }

            if ($completionFilter === 'selesai') {
                $countQuery->where(function ($q) {
                    $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            } elseif ($completionFilter === 'belum_selesai') {
                $countQuery->where(function ($q) {
                    $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                        ->where(function ($subQ) {
                            $subQ->whereNull('status_pembayaran')
                                ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                        });
                });
            }

            $bagianCounts[$code] = $countQuery->count();
        }

        return view('owner.rekapan', compact('dokumens', 'statistics', 'availableYears', 'bagianList', 'bagianCounts', 'selectedBagian', 'completionFilter', 'handler', 'handlerNames'))
            ->with('title', 'Rekapan Dokumen - ' . $handlerNames[$handler])
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display detail rekapan dengan 4 statistik (total, selesai, proses, terlambat)
     */
    public function rekapanDetail(Request $request, $type)
    {
        // Validate type
        $validTypes = ['total', 'selesai', 'ibuA', 'ibuB', 'perpajakan', 'akutansi'];
        if (!in_array($type, $validTypes)) {
            abort(404, 'Type tidak valid');
        }

        $typeNames = [
            'total' => 'Total Dokumen',
            'selesai' => 'Dokumen Selesai',
            'ibuA' => 'Dokumen Ibu Tarapul',
            'ibuB' => 'Dokumen Ibu Yuni',
            'perpajakan' => 'Dokumen Team Perpajakan',
            'akutansi' => 'Dokumen Team Akutansi'
        ];

        $now = Carbon::now();
        $baseQuery = Dokumen::query();

        // Apply filter based on type
        if ($type === 'total') {
            // All documents
        } elseif ($type === 'selesai') {
            // Completed documents
            $baseQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($type === 'ibuA') {
            // Ibu Tarapul documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'ibuA')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'draft')
                            ->where(function ($subSubQ) {
                                $subSubQ->whereNull('current_handler')
                                    ->orWhere('current_handler', 'ibuA');
                            });
                    });
            });
        } elseif ($type === 'ibuB') {
            // Ibu Yuni documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'ibuB')
                    ->orWhere('status', 'sent_to_ibub')
                    ->orWhere('status', 'pending_approval_ibub')
                    ->orWhere('status', 'proses_ibub');
            });
        } elseif ($type === 'perpajakan') {
            // Team Perpajakan documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'perpajakan')
                    ->orWhere('status', 'sent_to_perpajakan');
            });
        } elseif ($type === 'akutansi') {
            // Team Akutansi documents
            $baseQuery->where(function ($q) {
                $q->where('current_handler', 'akutansi')
                    ->orWhere('status', 'sent_to_akutansi');
            });
        }

        // Calculate 4 statistics
        // 1. Total Dokumen
        $totalDokumen = (clone $baseQuery)->count();

        // 2. Total Dokumen Selesai
        $totalSelesai = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->orWhere('status_pembayaran', 'sudah_dibayar');
        })->count();

        // 3. Total Dokumen Proses (sedang diproses)
        $totalProses = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'sedang diproses')
                ->orWhere('status', 'sent_to_ibub')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'sent_to_pembayaran')
                ->orWhere('status', 'proses_ibub')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'pending_approval_ibub');
        })->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
            ->where(function ($subQ) {
                $subQ->whereNull('status_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
            })->count();

        // 4. Total Dokumen Terlambat (memiliki deadline dan sudah lewat deadline, belum selesai)
        $totalTerlambat = (clone $baseQuery)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
            ->where(function ($subQ) {
                $subQ->whereNull('status_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
            })->count();

        // Get documents list with pagination
        $documentsQuery = (clone $baseQuery)->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $documentsQuery->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('uraian_spp', 'like', '%' . $search . '%')
                        ->orWhere('nama_pengirim', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $documentsQuery->where('tahun', $request->year);
        }

        // Filter by bagian
        $selectedBagian = $request->get('bagian', '');
        if ($selectedBagian) {
            $documentsQuery->where('bagian', $selectedBagian);
        }

        // Filter by completion status
        $completionFilter = $request->get('completion_status', '');
        if ($completionFilter === 'selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($completionFilter === 'belum_selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->where(function ($subQ) {
                        $subQ->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            });
        }

        // Filter by statistic card (total, selesai, proses, terlambat)
        $statFilter = $request->get('stat_filter', '');
        if ($statFilter === 'selesai') {
            $documentsQuery->where(function ($q) {
                $q->whereIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            });
        } elseif ($statFilter === 'proses') {
            $documentsQuery->where(function ($q) {
                $q->where('status', 'sedang diproses')
                    ->orWhere('status', 'sent_to_ibub')
                    ->orWhere('status', 'sent_to_perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere('status', 'proses_ibub')
                    ->orWhere('status', 'sent_to_perpajakan')
                    ->orWhere('status', 'sent_to_akutansi')
                    ->orWhere('status', 'pending_approval_ibub');
            })->whereNotIn('status', ['selesai', 'approved_data_sudah_terkirim', 'completed'])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        } elseif ($statFilter === 'terlambat') {
            // Dokumen terlambat: memiliki deadline yang sudah lewat, belum selesai, dan belum terkirim ke perpajakan/akutansi
            $documentsQuery->whereNotNull('deadline_at')
                ->where('deadline_at', '<', $now)
                ->whereNotIn('status', [
                    'selesai',
                    'approved_data_sudah_terkirim',
                    'completed',
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                ])
                ->where(function ($subQ) {
                    $subQ->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                });
        }
        // If stat_filter is 'total' or empty, show all documents (no additional filter)

        $dokumens = $documentsQuery->latest('tanggal_masuk')->paginate(20)->appends($request->query());

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Bagian list
        $bagianList = [
            'DPM' => 'DPM',
            'SKH' => 'SKH',
            'SDM' => 'SDM',
            'TEP' => 'TEP',
            'KPL' => 'KPL',
            'AKN' => 'AKN',
            'TAN' => 'TAN',
            'PMO' => 'PMO'
        ];

        $statFilter = $request->get('stat_filter', '');

        return view('owner.rekapanDetail', compact('type', 'typeNames', 'totalDokumen', 'totalSelesai', 'totalProses', 'totalTerlambat', 'dokumens', 'availableYears', 'bagianList', 'selectedBagian', 'completionFilter', 'statFilter'))
            ->with('title', 'Detail ' . $typeNames[$type])
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', 'active')
            ->with('menuRekapanKeterlambatan', '')
            ->with('dashboardUrl', '/owner/dashboard');
    }

    /**
     * Display rekapan keterlambatan for owner
     * Updated to use dokumen_role_data table for per-role deadline tracking
     */
    public function rekapanKeterlambatan(Request $request)
    {
        // Redirect langsung ke submenu Ibu Tara dengan mempertahankan query parameters
        $queryParams = $request->query();
        return redirect()->route('owner.rekapan-keterlambatan.role', [
            'roleCode' => 'ibuA'
        ] + $queryParams);
    }

    /**
     * Get statistics for rekapan
     */
    private function getRekapanStatistics($filterBagian = '')
    {
        $query = Dokumen::query();

        if ($filterBagian) {
            $query->where('bagian', $filterBagian);
        }

        $total = $query->count();

        // Count completed documents (status = 'selesai' or 'approved_data_sudah_terkirim' or current_handler = 'pembayaran')
        $completedQuery = Dokumen::query();
        if ($filterBagian) {
            $completedQuery->where('bagian', $filterBagian);
        }
        $completedCount = $completedQuery->where(function ($q) {
            $q->where('status', 'selesai')
                ->orWhere('status', 'approved_data_sudah_terkirim')
                ->orWhere('current_handler', 'pembayaran');
        })->count();

        // Count documents by handler
        $ibuTarapulQuery = Dokumen::query();
        $ibuYuniQuery = Dokumen::query();
        $perpajakanQuery = Dokumen::query();
        $akutansiQuery = Dokumen::query();

        if ($filterBagian) {
            $ibuTarapulQuery->where('bagian', $filterBagian);
            $ibuYuniQuery->where('bagian', $filterBagian);
            $perpajakanQuery->where('bagian', $filterBagian);
            $akutansiQuery->where('bagian', $filterBagian);
        }

        // Ibu Tarapul: dokumen dengan current_handler = 'ibuA' atau status draft
        $ibuTarapulCount = $ibuTarapulQuery->where(function ($q) {
            $q->where('current_handler', 'ibuA')
                ->orWhere(function ($subQ) {
                    $subQ->where('status', 'draft')
                        ->where(function ($subSubQ) {
                            $subSubQ->whereNull('current_handler')
                                ->orWhere('current_handler', 'ibuA');
                        });
                });
        })->count();

        // Ibu Yuni: dokumen dengan current_handler = 'ibuB' atau status sent_to_ibub
        $ibuYuniCount = $ibuYuniQuery->where(function ($q) {
            $q->where('current_handler', 'ibuB')
                ->orWhere('status', 'sent_to_ibub')
                ->orWhere('status', 'pending_approval_ibub')
                ->orWhere('status', 'proses_ibub');
        })->count();

        // Team Perpajakan: dokumen dengan current_handler = 'perpajakan' atau status sent_to_perpajakan
        $perpajakanCount = $perpajakanQuery->where(function ($q) {
            $q->where('current_handler', 'perpajakan')
                ->orWhere('status', 'sent_to_perpajakan')
                ->orWhere('status', 'proses_perpajakan');
        })->count();

        // Team Akutansi: dokumen dengan current_handler = 'akutansi' atau status sent_to_akutansi
        $akutansiCount = $akutansiQuery->where(function ($q) {
            $q->where('current_handler', 'akutansi')
                ->orWhere('status', 'sent_to_akutansi')
                ->orWhere('status', 'proses_akutansi');
        })->count();

        return [
            'total_documents' => $total,
            'completed_documents' => $completedCount,
            'ibu_tarapul' => $ibuTarapulCount,
            'ibu_yuni' => $ibuYuniCount,
            'perpajakan' => $perpajakanCount,
            'akutansi' => $akutansiCount
        ];
    }

    /**
     * Get document detail for owner recap page
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Load required relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return HTML partial for detail view
        $html = $this->generateDocumentDetailHtml($dokumen);

        return response($html);
    }

    /**
     * Generate HTML for document detail with all data (initial, perpajakan, akutansi)
     */
    private function generateDocumentDetailHtml($dokumen)
    {
        $html = '<div class="detail-grid">';

        // Document Information Section (Basic Data - Data Awal)
        $detailItems = [
            'Tanggal Masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-',
            'Bulan' => $dokumen->bulan ?? '-',
            'Tahun' => $dokumen->tahun ?? '-',
            'No SPP' => $dokumen->nomor_spp ?? '-',
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah ?? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
            'Kategori' => $dokumen->kategori ?? '-',
            'Jenis Dokumen' => $dokumen->jenis_dokumen ?? '-',
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
            'Kebun' => $dokumen->kebun ?? '-',
            'Bagian' => $dokumen->bagian ?? '-',
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
            'Nomor Miro' => $dokumen->nomor_miro ?? '-',
            'Status' => $this->getStatusDisplayName($dokumen->status),
            'Current Handler' => $this->getRoleDisplayName($dokumen->current_handler),
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

        if ($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi' || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'akutansi' || $dokumen->current_handler == 'pembayaran') {
            // Visual Separator for Perpajakan Data
            $html .= '<div class="detail-section-separator">
                <div class="separator-content">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Data Perpajakan</span>
                    <span class="tax-badge">DITAMBAHKAN OLEH TEAM PERPAJAKAN</span>
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

        // Data Akutansi Section - Show if document has akutansi data or sent to pembayaran
        $hasAkutansiData = !empty($dokumen->nomor_miro);
        if ($hasAkutansiData || $dokumen->status == 'sent_to_pembayaran' || $dokumen->current_handler == 'pembayaran') {
            $html .= '<div class="detail-section-separator">
                <div class="separator-content">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Data Akutansi</span>
                    <span class="tax-badge" style="background: rgba(255, 255, 255, 0.2);">DITAMBAHKAN OLEH TEAM AKUTANSI</span>
                </div>
            </div>';

            // Akutansi Information Section
            $html .= '<div class="detail-grid tax-section">';

            $akutansiFields = [
                'Nomor MIRO' => $dokumen->nomor_miro ?: '<span class="empty-field">Belum diisi</span>',
            ];

            foreach ($akutansiFields as $label => $value) {
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

        // Data Pembayaran Section - Show if document has payment data or status_pembayaran is set
        $hasPembayaranData = !empty($dokumen->link_bukti_pembayaran) || !empty($dokumen->status_pembayaran);
        if ($hasPembayaranData || $dokumen->current_handler == 'pembayaran' || $dokumen->status == 'sent_to_pembayaran') {
            // Get status_pembayaran and link_bukti_pembayaran from database if not in model
            $statusPembayaran = $dokumen->status_pembayaran ?? \DB::table('dokumens')->where('id', $dokumen->id)->value('status_pembayaran');
            $linkBuktiPembayaran = $dokumen->link_bukti_pembayaran ?? \DB::table('dokumens')->where('id', $dokumen->id)->value('link_bukti_pembayaran');

            $html .= '<div class="detail-section-separator">
                <div class="separator-content" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745;">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Data Pembayaran</span>
                    <span class="tax-badge" style="background: rgba(255, 255, 255, 0.2);">DITAMBAHKAN OLEH TEAM PEMBAYARAN</span>
                </div>
            </div>';

            // Pembayaran Information Section
            $html .= '<div class="detail-grid tax-section">';

            $pembayaranFields = [
                'Status Pembayaran' => $statusPembayaran ? ucfirst(str_replace('_', ' ', $statusPembayaran)) : '<span class="empty-field">Belum diisi</span>',
                'Link Bukti Pembayaran' => $linkBuktiPembayaran
                    ? sprintf(
                        '<a href="%s" target="_blank" class="tax-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
                        htmlspecialchars($linkBuktiPembayaran),
                        htmlspecialchars($linkBuktiPembayaran)
                    )
                    : '<span class="empty-field">Belum diisi</span>',
            ];

            foreach ($pembayaranFields as $label => $value) {
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
            return sprintf(
                '<a href="%s" target="_blank" class="tax-link">%s <i class="fa-solid fa-external-link-alt"></i></a>',
                htmlspecialchars($link),
                htmlspecialchars($link)
            );
        }

        return htmlspecialchars($link);
    }

    /**
     * Get filter data for dropdowns
     */
    private function getFilterData(): array
    {
        // Get distinct bagian values
        $bagianList = Dokumen::whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->distinct()
            ->orderBy('bagian')
            ->pluck('bagian', 'bagian')
            ->toArray();

        // Get distinct vendor/dibayar_kepada values
        $vendorList = [];
        try {
            // From dibayar_kepadas table
            $vendorList = DB::table('dibayar_kepadas')
                ->whereNotNull('nama_penerima')
                ->where('nama_penerima', '!=', '')
                ->distinct()
                ->orderBy('nama_penerima')
                ->pluck('nama_penerima', 'nama_penerima')
                ->toArray();

            // Also include from dokumens.dibayar_kepada column (legacy)
            $legacyVendors = Dokumen::whereNotNull('dibayar_kepada')
                ->where('dibayar_kepada', '!=', '')
                ->distinct()
                ->pluck('dibayar_kepada', 'dibayar_kepada')
                ->toArray();

            $vendorList = array_merge($vendorList, $legacyVendors);
            $vendorList = array_unique($vendorList);
            asort($vendorList);
        } catch (\Exception $e) {
            \Log::error('Error fetching vendor list: ' . $e->getMessage());
        }

        // Get distinct kebun values
        $kebunList = Dokumen::whereNotNull('kebun')
            ->where('kebun', '!=', '')
            ->distinct()
            ->orderBy('kebun')
            ->pluck('kebun', 'kebun')
            ->toArray();

        // Get Kriteria CF, Sub Kriteria, Item Sub Kriteria from cash_bank database
        $kriteriaCfList = [];
        $subKriteriaList = [];
        $itemSubKriteriaList = [];

        try {
            $kriteriaCfList = \App\Models\KategoriKriteria::on('cash_bank')
                ->where('tipe', 'Keluar')
                ->orderBy('nama_kriteria')
                ->pluck('nama_kriteria', 'id_kategori_kriteria')
                ->toArray();

            $subKriteriaList = \App\Models\SubKriteria::on('cash_bank')
                ->orderBy('nama_sub_kriteria')
                ->pluck('nama_sub_kriteria', 'id_sub_kriteria')
                ->toArray();

            $itemSubKriteriaList = \App\Models\ItemSubKriteria::on('cash_bank')
                ->orderBy('nama_item_sub_kriteria')
                ->pluck('nama_item_sub_kriteria', 'id_item_sub_kriteria')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error fetching kriteria data from cash_bank: ' . $e->getMessage());
        }

        return [
            'bagian' => $bagianList,
            'vendor' => $vendorList,
            'kriteria_cf' => $kriteriaCfList,
            'sub_kriteria' => $subKriteriaList,
            'item_sub_kriteria' => $itemSubKriteriaList,
            'kebun' => $kebunList,
        ];
    }

    /**
     * Rekapan keterlambatan per role
     */
    public function rekapanKeterlambatanByRole(Request $request, $roleCode)
    {
        $now = Carbon::now();

        // Validasi role
        $validRoles = ['ibuA', 'ibuB', 'perpajakan', 'akutansi', 'pembayaran'];
        if (!in_array($roleCode, $validRoles)) {
            abort(404, 'Role tidak ditemukan');
        }

        // Role configuration
        $roleConfig = [
            'ibuA' => ['name' => 'Ibu Tara', 'code' => 'ibuA'],
            'ibuB' => ['name' => 'Team Verifikasi', 'code' => 'ibuB'],
            'perpajakan' => ['name' => 'Team Perpajakan', 'code' => 'perpajakan'],
            'akutansi' => ['name' => 'Team Akutansi', 'code' => 'akutansi'],
            'pembayaran' => ['name' => 'Pembayaran', 'code' => 'pembayaran'],
        ];


        // Query dokumen berdasarkan role dengan umur dokumen sejak received_at
        // Untuk pembayaran, gunakan left join karena mungkin tidak selalu ada di dokumen_role_data
        if ($roleCode === 'pembayaran') {
            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
                ->leftJoin('dokumen_role_data', function ($join) use ($roleCode) {
                    $join->on('dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                        ->where('dokumen_role_data.role_code', '=', $roleCode);
                })
                ->where(function ($q) {
                    $q->where(function ($subQ) {
                        // Dokumen yang ada di dokumen_role_data dengan received_at
                        $subQ->whereNotNull('dokumen_role_data.received_at')
                            ->whereNull('dokumen_role_data.processed_at');
                    })->orWhere(function ($subQ) {
                        // Dokumen yang dikirim ke pembayaran (menggunakan sent_to_pembayaran_at)
                        $subQ->whereNotNull('dokumens.sent_to_pembayaran_at')
                            ->where(function ($statusQ) {
                            $statusQ->whereNull('dokumens.status_pembayaran')
                                ->orWhere('dokumens.status_pembayaran', '!=', 'sudah_dibayar');
                        });
                    });
                })
                ->where(function ($q) {
                    $q->where('dokumens.current_handler', 'pembayaran')
                        ->orWhere('dokumens.status', 'sent_to_pembayaran')
                        ->orWhere('dokumens.status', 'proses_pembayaran');
                })
                ->select(
                    'dokumens.*',
                    'dokumen_role_data.role_code as delay_role_code',
                    'dokumen_role_data.received_at as delay_received_at',
                    'dokumen_role_data.processed_at as delay_processed_at',
                    'dokumen_role_data.deadline_at as delay_deadline_at'
                );
        } else {
            // Query ALL documents that have ever been in this role (permanent tracking)
            // This shows both active documents AND completed documents (already sent to next role)
            // Active = current_handler is still this role
            // Completed = current_handler has moved to another role

            // Filter by status: 'active', 'completed', or 'all' (default)
            $statusFilter = $request->get('status_filter', 'all');

            // Map role code to expected handler
            $roleHandlerMapping = [
                'ibuB' => 'ibuB',
                'perpajakan' => 'perpajakan',
                'akutansi' => 'akutansi',
            ];
            $expectedHandler = $roleHandlerMapping[$roleCode] ?? $roleCode;

            // Query based on dokumen_role_data.received_at (permanent record)
            $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData'])
                ->join('dokumen_role_data', 'dokumens.id', '=', 'dokumen_role_data.dokumen_id')
                ->whereRaw('LOWER(dokumen_role_data.role_code) = ?', [strtolower($roleCode)])
                ->whereNotNull('dokumen_role_data.received_at');

            // Apply status filter based on current_handler (not processed_at)
            // Active = document is still being handled by this role
            // Completed = document has moved to another role
            if ($statusFilter === 'active') {
                $query->whereRaw('LOWER(dokumens.current_handler) = ?', [strtolower($expectedHandler)]);
            } elseif ($statusFilter === 'completed') {
                $query->whereRaw('LOWER(dokumens.current_handler) != ?', [strtolower($expectedHandler)]);
            }
            // 'all' = no additional filter

            $query->select(
                'dokumens.*',
                'dokumen_role_data.role_code as delay_role_code',
                'dokumen_role_data.received_at as delay_received_at',
                'dokumen_role_data.processed_at as delay_processed_at',
                'dokumen_role_data.deadline_at as delay_deadline_at'
            );
        }




        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim((string) $request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('dokumens.nomor_agenda', 'like', '%' . $search . '%')
                        ->orWhere('dokumens.nomor_spp', 'like', '%' . $search . '%')
                        ->orWhere('dokumens.uraian_spp', 'like', '%' . $search . '%');
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('dokumens.tahun', $request->year);
        }

        // Apply advanced filters (similar to owner dashboard)
        if ($request->has('filter_bagian') && $request->filter_bagian) {
            $query->where('dokumens.bagian', $request->filter_bagian);
        }

        if ($request->has('filter_vendor') && $request->filter_vendor) {
            $query->where(function ($q) use ($request) {
                $q->where('dokumens.nama_pengirim', 'like', '%' . $request->filter_vendor . '%')
                    ->orWhereHas('dibayarKepadas', function ($subQ) use ($request) {
                        $subQ->where('nama_penerima', 'like', '%' . $request->filter_vendor . '%');
                    });
            });
        }

        if ($request->has('filter_kriteria_cf') && $request->filter_kriteria_cf) {
            try {
                $kriteria = \App\Models\KategoriKriteria::on('cash_bank')->find($request->filter_kriteria_cf);
                if ($kriteria) {
                    $query->where('dokumens.kategori', $kriteria->nama_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_kriteria_cf: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_sub_kriteria') && $request->filter_sub_kriteria) {
            try {
                $subKriteria = \App\Models\SubKriteria::on('cash_bank')->find($request->filter_sub_kriteria);
                if ($subKriteria) {
                    $query->where('dokumens.jenis_dokumen', $subKriteria->nama_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_sub_kriteria: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_item_sub_kriteria') && $request->filter_item_sub_kriteria) {
            try {
                $itemSubKriteria = \App\Models\ItemSubKriteria::on('cash_bank')->find($request->filter_item_sub_kriteria);
                if ($itemSubKriteria) {
                    $query->where('dokumens.jenis_sub_pekerjaan', $itemSubKriteria->nama_item_sub_kriteria);
                }
            } catch (\Exception $e) {
                \Log::warning('Cash_bank database not available for filter_item_sub_kriteria: ' . $e->getMessage());
            }
        }

        if ($request->has('filter_kebun') && $request->filter_kebun) {
            $query->where('dokumens.kebun', $request->filter_kebun);
        }

        // Order by received_at or sent_to_pembayaran_at
        if ($roleCode === 'pembayaran') {
            $query->orderByRaw('COALESCE(dokumen_role_data.received_at, dokumens.sent_to_pembayaran_at) ASC');
        } else {
            $query->orderBy('dokumen_role_data.received_at', 'asc');
        }

        // Debug: Log SQL query and count before get()
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, SQL Query: " . $query->toSql());
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Query Bindings: " . json_encode($query->getBindings()));
        $countBeforeGet = $query->count();
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Count before get(): {$countBeforeGet}");

        // Get all documents first to calculate age and filter by age
        $allDokumens = $query->get();

        // Debug: Log count of documents retrieved
        \Log::info("Rekapan Keterlambatan - Role: {$roleCode}, Documents retrieved: " . $allDokumens->count());

        // Calculate age for each document and filter by age if needed
        $filterAge = $request->get('filter_age');
        $filteredDokumens = $allDokumens->map(function ($dokumen) use ($now, $roleCode) {
            // Get received_at and processed_at from the joined dokumen_role_data
            $receivedAt = null;
            $processedAt = null;

            // Check if processed_at exists (completed document)
            if (isset($dokumen->delay_processed_at) && $dokumen->delay_processed_at) {
                $processedAt = Carbon::parse($dokumen->delay_processed_at);
            }

            // Get received_at
            if (isset($dokumen->delay_received_at) && $dokumen->delay_received_at) {
                $receivedAt = Carbon::parse($dokumen->delay_received_at);
            }
            // Fallback for pembayaran role
            elseif ($roleCode === 'pembayaran' && isset($dokumen->sent_to_pembayaran_at) && $dokumen->sent_to_pembayaran_at) {
                $receivedAt = Carbon::parse($dokumen->sent_to_pembayaran_at);
            }
            // Third try: Load roleData relationship
            else {
                if (!$dokumen->relationLoaded('roleData')) {
                    $dokumen->load('roleData');
                }
                $roleCodeLower = strtolower($roleCode);
                $roleData = $dokumen->roleData->first(function ($rd) use ($roleCodeLower) {
                    return strtolower($rd->role_code) === $roleCodeLower;
                });
                if ($roleData) {
                    if ($roleData->received_at) {
                        $receivedAt = Carbon::parse($roleData->received_at);
                    }
                    if ($roleData->processed_at) {
                        $processedAt = Carbon::parse($roleData->processed_at);
                    }
                }
            }

            // If still no received_at, try direct query
            if (!$receivedAt) {
                $roleDataDirect = \App\Models\DokumenRoleData::where('dokumen_id', $dokumen->id)
                    ->whereRaw('LOWER(role_code) = ?', [strtolower($roleCode)])
                    ->whereNotNull('received_at')
                    ->first();
                if ($roleDataDirect) {
                    $receivedAt = Carbon::parse($roleDataDirect->received_at);
                    if ($roleDataDirect->processed_at) {
                        $processedAt = Carbon::parse($roleDataDirect->processed_at);
                    }
                }
            }

            // IMPORTANT: Determine completion status correctly
            // A document is COMPLETED for this role if:
            // 1. processed_at is set (document was processed)
            // 2. AND current_handler has moved to a different role (document is no longer here)
            // If current_handler is still this role, document is ACTIVE even if processed_at is set
            $roleHandlerMapping = [
                'ibuB' => 'ibuB',
                'perpajakan' => 'perpajakan',
                'akutansi' => 'akutansi',
            ];
            $expectedHandler = $roleHandlerMapping[$roleCode] ?? $roleCode;
            $currentHandler = $dokumen->current_handler ?? '';

            // Document is completed if processed_at exists AND current_handler has moved on
            $isCompleted = ($processedAt !== null) && (strtolower($currentHandler) !== strtolower($expectedHandler));

            // Calculate age based on completion status
            // - Active documents: compare received_at to NOW (time keeps running)
            // - Completed documents: compare received_at to processed_at (PERMANENT time)
            $endTime = $isCompleted ? $processedAt : $now;

            $ageHours = $receivedAt ? $receivedAt->diffInHours($endTime) : 0;
            $ageDays = $receivedAt ? $endTime->diffInDays($receivedAt, false) : 0;
            $ageDays = max(0, $ageDays);

            $dokumen->is_completed = $isCompleted;
            $dokumen->age_days = $ageDays;
            $dokumen->age_hours = $ageHours;
            $dokumen->age_formatted = $this->formatAge($ageDays);
            $dokumen->effective_received_at = $receivedAt ? $receivedAt->format('Y-m-d H:i:s') : null;
            $dokumen->effective_processed_at = $processedAt ? $processedAt->format('Y-m-d H:i:s') : null;

            return $dokumen;
        });



        // Filter by age if filter_age is set (using hours to match dashboard)
        if ($filterAge) {
            $filteredDokumens = $filteredDokumens->filter(function ($dokumen) use ($filterAge) {
                $ageHours = $dokumen->age_hours ?? 0;
                if ($filterAge === '1') {
                    // AMAN: < 24 hours
                    return $ageHours < 24;
                } elseif ($filterAge === '2') {
                    // PERINGATAN: 24-72 hours
                    return $ageHours >= 24 && $ageHours < 72;
                } elseif ($filterAge === '3+') {
                    // TERLAMBAT: > 72 hours
                    return $ageHours >= 72;
                }
                return true;
            });
        }


        // Paginate the filtered results
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $currentItems = $filteredDokumens->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $dokumens = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredDokumens->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Calculate card statistics berdasarkan umur dokumen
        // Include ALL documents (active + completed) and use permanent time for completed
        $cardStats = [];

        // Calculate card statistics hanya untuk role yang memerlukan card
        if (in_array($roleCode, ['ibuB', 'perpajakan', 'akutansi'])) {
            $roleCodeLower = strtolower($roleCode);

            // Get ALL documents that have ever been in this role
            $allRoleDocs = DokumenRoleData::where('dokumen_role_data.role_code', $roleCode)
                ->whereNotNull('dokumen_role_data.received_at')
                ->join('dokumens', 'dokumen_role_data.dokumen_id', '=', 'dokumens.id')
                ->select('dokumen_role_data.*')
                ->get();

            // Card 1: Dokumen dengan umur < 24 jam (hijau) - AMAN
            $card1Count = 0;
            // Card 2: Dokumen dengan umur 24-72 jam (kuning) - PERINGATAN
            $card2Count = 0;
            // Card 3: Dokumen dengan umur > 72 jam (merah) - TERLAMBAT
            $card3Count = 0;

            foreach ($allRoleDocs as $doc) {
                $receivedAt = Carbon::parse($doc->received_at);

                // Use processed_at as end time for completed documents (PERMANENT)
                // Use now for active documents (time keeps running)
                $endTime = $doc->processed_at ? Carbon::parse($doc->processed_at) : $now;
                $hoursDiff = $receivedAt->diffInHours($endTime);

                if ($hoursDiff < 24) {
                    $card1Count++;
                } elseif ($hoursDiff < 72) {
                    $card2Count++;
                } else {
                    $card3Count++;
                }
            }
        } else {
            // Untuk role lain (ibuA, pembayaran), set default values
            $card1Count = 0;
            $card2Count = 0;
            $card3Count = 0;
        }



        // Only set cardStats for roles that need cards
        if (in_array($roleCode, ['ibuB', 'perpajakan', 'akutansi'])) {
            $cardStats = [
                'card1' => [
                    'count' => $card1Count,
                    'label' => '1 Hari',
                    'color' => 'green',
                ],
                'card2' => [
                    'count' => $card2Count,
                    'label' => '2 Hari',
                    'color' => 'yellow',
                ],
                'card3' => [
                    'count' => $card3Count,
                    'label' => '3+ Hari',
                    'color' => 'red',
                ],
            ];
        } else {
            // Empty cardStats for roles that don't need cards
            $cardStats = [
                'card1' => ['count' => 0, 'label' => '-', 'color' => 'green'],
                'card2' => ['count' => 0, 'label' => '-', 'color' => 'yellow'],
                'card3' => ['count' => 0, 'label' => '-', 'color' => 'red'],
            ];
        }

        // Calculate total statistics
        if ($roleCode === 'pembayaran') {
            $totalDocuments = Dokumen::where(function ($q) {
                $q->where('current_handler', 'pembayaran')
                    ->orWhere('status', 'sent_to_pembayaran')
                    ->orWhere('status', 'proses_pembayaran');
            })
                ->where(function ($q) {
                    $q->whereNull('status_pembayaran')
                        ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                })
                ->count();
        } else {
            $totalDocuments = DokumenRoleData::where('role_code', $roleCode)
                ->whereNotNull('received_at')
                ->whereNull('processed_at')
                ->count();
        }

        // Get available years
        $availableYears = Dokumen::selectRaw('DISTINCT tahun')
            ->whereNotNull('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();


        // Get filter data for dropdowns
        $filterData = $this->getFilterData();

        return view('owner.rekapanKeterlambatanByRole', compact(
            'dokumens',
            'cardStats',
            'totalDocuments',
            'availableYears',
            'roleConfig',
            'roleCode',
            'filterData'
        ))
            ->with('title', 'Rekapan Keterlambatan - ' . $roleConfig[$roleCode]['name'])
            ->with('module', 'owner')
            ->with('menuDashboard', '')
            ->with('menuRekapan', '')
            ->with('menuRekapanKeterlambatan', 'active')
            ->with('dashboardUrl', '/owner/dashboard');
    }


    /**
     * Format age in days to readable format
     */
    private function formatAge($days)
    {
        if ($days == 0) {
            return 'Hari ini';
        } elseif ($days == 1) {
            return '1 hari';
        } elseif ($days < 30) {
            return $days . ' hari';
        } elseif ($days == 30) {
            return '1 bulan';
        } elseif ($days % 30 == 0) {
            return ($days / 30) . ' bulan';
        } else {
            $months = floor($days / 30);
            $remainingDays = $days % 30;
            if ($months > 0 && $remainingDays > 0) {
                return $months . ' bulan ' . $remainingDays . ' hari';
            } elseif ($months > 0) {
                return $months . ' bulan';
            } else {
                return $days . ' hari';
            }
        }
    }
}