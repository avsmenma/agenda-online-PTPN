<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\TuTk;
use App\Models\TuTkPupuk;
use App\Models\TuTkVd;
use App\Models\TuTkTan;
use App\Models\PaymentLog;
use App\Models\DocumentPositionTracking;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DokumenHelper;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DashboardPembayaranController extends Controller
{
    public function index(){
        // Get statistics
        $totalDokumen = Dokumen::count();
        $totalSelesai = Dokumen::where('status', 'selesai')->count();
        $totalProses = Dokumen::where('status', 'sedang diproses')->count();
        $totalDikembalikan = Dokumen::where('status', 'dikembalikan')->count();

        // Get latest documents (5 most recent)
        $dokumenTerbaru = Dokumen::latest('tanggal_masuk')
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
        return view('pembayaranNEW.dashboardPembayaran', $data);
    }

    public function dokumens(Request $request){
        // Get status filter and search from request
        $statusFilter = $request->get('status_filter');
        $search = $request->get('search');
        $perPage = $request->get('per_page', session('pembayaran_per_page', 10)); // Default 10, bisa diubah user
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int)$perPage : 10; // Validate per_page value
        session(['pembayaran_per_page' => $perPage]); // Save to session

        // Build query for pembayaran documents
        // Include all documents with nomor_agenda (same as rekapan method)
        // Filter by status will determine which documents to show
        $query = \App\Models\Dokumen::whereNotNull('nomor_agenda');

        // Note: Status filter will be applied after computing computed_status
        // to ensure consistency with how status is displayed

        // Apply search filter if provided
        if ($search && trim($search) !== '') {
            $searchTerm = trim($search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('nomor_agenda', 'like', "%{$searchTerm}%")
                  ->orWhere('nomor_spp', 'like', "%{$searchTerm}%")
                  ->orWhere('uraian_spp', 'like', "%{$searchTerm}%")
                  ->orWhere('dibayar_kepada', 'like', "%{$searchTerm}%")
                  ->orWhere('nomor_mirror', 'like', "%{$searchTerm}%")
                  ->orWhere('no_berita_acara', 'like', "%{$searchTerm}%")
                  ->orWhere('no_spk', 'like', "%{$searchTerm}%");
                
                // Search in nilai_rupiah - handle various formats
                $numericSearch = preg_replace('/[^0-9]/', '', $searchTerm);
                if (is_numeric($numericSearch) && $numericSearch > 0) {
                    $q->orWhereRaw('CAST(nilai_rupiah AS CHAR) LIKE ?', ['%' . $numericSearch . '%']);
                }
            })->orWhereHas('dibayarKepadas', function($q) use ($searchTerm) {
                $q->where('nama_penerima', 'like', "%{$searchTerm}%");
            });
        }

        // Helper function to calculate computed status for pembayaran role
        // Status khusus role pembayaran: "belum_siap_bayar" atau "siap_bayar" atau "sudah_dibayar"
        $getComputedStatus = function($doc) {
            // Cek apakah dokumen sudah dibayar berdasarkan:
            // 1. Ada tanggal_dibayar, ATAU
            // 2. Ada link_bukti_pembayaran, ATAU
            // 3. status_pembayaran = 'sudah_dibayar' (berbagai format)
            if ($doc->tanggal_dibayar || 
                $doc->link_bukti_pembayaran ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH_DIBAYAR' ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            
            // Status "Siap Bayar": hanya setelah diproses akutansi atau dikirim ke pembayaran
            // Status dokumen yang dianggap "Siap Bayar":
            // - processed_by_akutansi (akutansi sudah selesai memproses)
            // - sent_to_pembayaran (sudah dikirim ke pembayaran)
            // - processed_by_pembayaran (sedang diproses pembayaran)
            // - current_handler = pembayaran
            if (in_array($doc->status, ['processed_by_akutansi', 'sent_to_pembayaran', 'processed_by_pembayaran']) ||
                ($doc->current_handler === 'pembayaran' && in_array($doc->status, ['sedang diproses', 'sent_to_pembayaran']))) {
                return 'siap_bayar';
            }
            
            // Default: "Belum Siap Bayar" untuk semua status lainnya
            // Termasuk: draft, menunggu_di_approve, sent_to_ibub, processed_by_ibub,
            // sent_to_perpajakan, processed_by_perpajakan, sent_to_akutansi, dll
            return 'belum_siap_bayar';
        };

        // Get all documents with ordering and eager load relationships (before pagination)
        $allDokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
            ->orderByRaw("CASE
                WHEN status IN ('processed_by_akutansi', 'sent_to_pembayaran', 'processed_by_pembayaran') OR current_handler = 'pembayaran' THEN 1
                WHEN status IN ('sent_to_akutansi', 'processed_by_perpajakan', 'sent_to_perpajakan') THEN 2
                ELSE 3
            END")
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add computed status to each document
        $allDokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Tampilkan semua dokumen (termasuk yang belum siap bayar) untuk real-time visibility
        // Dokumen muncul sejak awal dibuat, tidak perlu menunggu sent_to_pembayaran
        
        // Apply status filter if provided (for filtering between belum_siap_bayar, siap_bayar, dan sudah_dibayar)
        if ($statusFilter && in_array($statusFilter, ['belum_siap_bayar', 'siap_bayar', 'sudah_dibayar'])) {
            $allDokumens = $allDokumens->filter(function($doc) use ($statusFilter) {
                return $doc->computed_status === $statusFilter;
            })->values(); // Re-index the collection
        }

        // Paginate manually using LengthAwarePaginator
        $currentPage = Paginator::resolveCurrentPage('page');
        $total = $allDokumens->count();
        $currentItems = $allDokumens->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // Get all query parameters except 'page' for pagination links
        $queryParams = request()->query();
        unset($queryParams['page']);
        
        $dokumens = new LengthAwarePaginator(
            $currentItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => $queryParams // Preserve all query parameters except page
            ]
        );

        // Available columns for customization (exclude 'status' as it's always shown as a special column)
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'tanggal_masuk' => 'TGL Masuk',
            'nomor_spp' => 'No SPP',
            'uraian_spp' => 'Uraian SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nomor_mirror' => 'Nomor Miro',
            'tanggal_spp' => 'TGL SPP',
            'kategori' => 'Kategori',
            'kebun' => 'Kebun',
            'jenis_dokumen' => 'Jenis Dokumen',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'nama_pengirim' => 'Nama Pengirim',
            'dibayar_kepada' => 'Dibayar Kepada',
            'no_berita_acara' => 'No Berita Acara',
            'tanggal_berita_acara' => 'TGL BA',
            'no_spk' => 'No SPK',
            'tanggal_spk' => 'TGL SPK',
            'tanggal_berakhir_spk' => 'TGL Berakhir SPK',
            'status_pembayaran' => 'Status Pembayaran',
        ];

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Filter out 'status' and 'aksi' from selectedColumns if present
        $selectedColumns = array_filter($selectedColumns, function($col) {
            return $col !== 'status' && $col !== 'aksi';
        });
        $selectedColumns = array_values($selectedColumns); // Re-index array

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['pembayaran_dokumens_table_columns' => $selectedColumns]);
        } else {
            // Load from session if available, and filter out 'status'
            $selectedColumns = session('pembayaran_dokumens_table_columns', [
                'nomor_agenda',
                'nomor_spp',
                'tanggal_masuk',
                'nilai_rupiah',
                'dibayar_kepada'
            ]);
            // Filter out 'status' and 'aksi' if they exist in session
            $selectedColumns = array_filter($selectedColumns, function($col) {
                return $col !== 'status' && $col !== 'aksi';
            });
            $selectedColumns = array_values($selectedColumns);
            // Update session to remove 'status' if it was present
            session(['pembayaran_dokumens_table_columns' => $selectedColumns]);
        }

        $data = array(
            "title" => "Daftar Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => 'Active',
            'dokumens' => $dokumens,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'availableColumns' => $availableColumns,
            'selectedColumns' => $selectedColumns,
            'perPage' => $perPage,
        );
        return view('pembayaranNEW.dokumens.daftarPembayaran', $data);
    }

    public function createDokumen(){
        $data = array(
            "title" => "Tambah Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuTambahDokumen' => 'Active',
        );
        // TODO: Update this to pembayaranNEW when tambahPembayaran.blade.php is available in pembayaranNEW folder
        return view('pembayaran.dokumens.tambahPembayaran', $data);
    }

    public function storeDokumen(Request $request){
        // Implementation for storing document
        return redirect()->route('dokumensPembayaran.index')->with('success', 'Pembayaran berhasil ditambahkan');
    }

    public function editDokumen($id){
        $data = array(
            "title" => "Edit Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuEditDokumen' => 'Active',
        );
        return view('pembayaranNEW.dokumens.editPembayaran', $data);
    }

    public function updateDokumen(Request $request, $id){
        // Implementation for updating document
        return redirect()->route('dokumensPembayaran.index')->with('success', 'Pembayaran berhasil diperbarui');
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'status_pembayaran' => 'required|in:siap_dibayar,sudah_dibayar',
            ], [
                'status_pembayaran.required' => 'Status pembayaran wajib dipilih.',
                'status_pembayaran.in' => 'Status pembayaran tidak valid.',
            ]);

            // Store old value for logging
            $oldStatusPembayaran = $dokumen->status_pembayaran;

            DB::transaction(function () use ($dokumen, $validated) {
                $updateData = [
                    'status_pembayaran' => $validated['status_pembayaran'],
                ];

                // If status is sudah_dibayar, also update general status
                if ($validated['status_pembayaran'] === 'sudah_dibayar') {
                    $updateData['status'] = 'completed';
                }

                $dokumen->update($updateData);
            });

            $dokumen->refresh();

            // Log status change
            if ($oldStatusPembayaran != $dokumen->status_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'status_pembayaran',
                        $oldStatusPembayaran ? ucfirst(str_replace('_', ' ', $oldStatusPembayaran)) : null,
                        ucfirst(str_replace('_', ' ', $dokumen->status_pembayaran)),
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log status change: ' . $logException->getMessage());
                }
            }

            Log::info('Status pembayaran successfully updated', [
                'document_id' => $dokumen->id,
                'status_pembayaran' => $validated['status_pembayaran']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating payment status: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage(), [
                'document_id' => $dokumen->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update pembayaran (tanggal pembayaran dan/atau link bukti)
     * Status otomatis berubah menjadi 'sudah_dibayar' jika salah satu sudah diisi
     */
    public function updatePembayaran(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'tanggal_dibayar' => 'nullable|date',
                'link_bukti_pembayaran' => 'nullable|url|max:1000',
            ], [
                'tanggal_dibayar.date' => 'Format tanggal tidak valid.',
                'link_bukti_pembayaran.url' => 'Format link tidak valid.',
                'link_bukti_pembayaran.max' => 'Link maksimal 1000 karakter.',
            ]);

            // Minimal salah satu harus diisi
            if (empty($validated['tanggal_dibayar']) && empty($validated['link_bukti_pembayaran'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal salah satu field (tanggal pembayaran atau link bukti) harus diisi.'
                ], 422);
            }

            // Store old values for logging
            $oldTanggalDibayar = $dokumen->tanggal_dibayar;
            $oldLinkBukti = $dokumen->link_bukti_pembayaran;
            $oldStatusPembayaran = $dokumen->status_pembayaran;

            DB::transaction(function () use ($dokumen, $validated) {
                $updateData = [];

                // Update tanggal_dibayar jika diisi (jika dikirim dalam request)
                if (isset($validated['tanggal_dibayar']) && !empty($validated['tanggal_dibayar'])) {
                    $updateData['tanggal_dibayar'] = $validated['tanggal_dibayar'];
                }

                // Update link_bukti_pembayaran jika diisi (jika dikirim dalam request)
                if (isset($validated['link_bukti_pembayaran']) && !empty($validated['link_bukti_pembayaran'])) {
                    $updateData['link_bukti_pembayaran'] = $validated['link_bukti_pembayaran'];
                }

                // Jika salah satu sudah diisi (baik yang baru atau yang sudah ada), update status menjadi 'sudah_dibayar'
                $hasTanggal = !empty($updateData['tanggal_dibayar']) || !empty($dokumen->tanggal_dibayar);
                $hasLink = !empty($updateData['link_bukti_pembayaran']) || !empty($dokumen->link_bukti_pembayaran);
                
                if ($hasTanggal || $hasLink) {
                    $updateData['status_pembayaran'] = 'sudah_dibayar';
                    $updateData['status'] = 'completed';
                }

                $dokumen->update($updateData);
            });

            $dokumen->refresh();

            // Log changes
            if ($oldTanggalDibayar != $dokumen->tanggal_dibayar) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'tanggal_dibayar',
                        $oldTanggalDibayar ? $oldTanggalDibayar->format('d/m/Y') : null,
                        $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : null,
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log tanggal_dibayar change: ' . $logException->getMessage());
                }
            }

            if ($oldLinkBukti != $dokumen->link_bukti_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'link_bukti_pembayaran',
                        $oldLinkBukti,
                        $dokumen->link_bukti_pembayaran,
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log link upload: ' . $logException->getMessage());
                }
            }

            if ($oldStatusPembayaran != $dokumen->status_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'status_pembayaran',
                        $oldStatusPembayaran ? ucfirst(str_replace('_', ' ', $oldStatusPembayaran)) : null,
                        ucfirst(str_replace('_', ' ', $dokumen->status_pembayaran)),
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log status change: ' . $logException->getMessage());
                }
            }

            Log::info('Pembayaran successfully updated', [
                'document_id' => $dokumen->id,
                'tanggal_dibayar' => $dokumen->tanggal_dibayar,
                'has_link_bukti' => !empty($dokumen->link_bukti_pembayaran),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diperbarui.',
                'is_complete' => !empty($dokumen->tanggal_dibayar) && !empty($dokumen->link_bukti_pembayaran),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating payment: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating payment: ' . $e->getMessage(), [
                'document_id' => $dokumen->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload link bukti pembayaran
     */
    public function uploadBukti(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'link_bukti_pembayaran' => 'required|url|max:1000',
            ], [
                'link_bukti_pembayaran.required' => 'Link bukti pembayaran wajib diisi.',
                'link_bukti_pembayaran.url' => 'Format link tidak valid.',
                'link_bukti_pembayaran.max' => 'Link maksimal 1000 karakter.',
            ]);

            // Store old value for logging
            $oldLinkBukti = $dokumen->link_bukti_pembayaran;

            DB::transaction(function () use ($dokumen, $validated) {
                $dokumen->update([
                    'link_bukti_pembayaran' => $validated['link_bukti_pembayaran'],
                ]);
            });

            $dokumen->refresh();

            // Log link upload
            if ($oldLinkBukti != $dokumen->link_bukti_pembayaran) {
                try {
                    \App\Helpers\ActivityLogHelper::logDataEdited(
                        $dokumen,
                        'link_bukti_pembayaran',
                        $oldLinkBukti,
                        $dokumen->link_bukti_pembayaran,
                        'pembayaran'
                    );
                } catch (\Exception $logException) {
                    \Log::error('Failed to log link upload: ' . $logException->getMessage());
                }
            }

            Log::info('Link bukti pembayaran successfully uploaded', [
                'document_id' => $dokumen->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Link bukti pembayaran berhasil disimpan.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error uploading payment proof: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading payment proof: ' . $e->getMessage(), [
                'document_id' => $dokumen->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan link bukti pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyDokumen($id){
        // Implementation for deleting document
        return redirect()->route('dokumensPembayaran.index')->with('success', 'Pembayaran berhasil dihapus');
    }

    public function pengembalian(){
        // Redirect ke daftar pembayaran karena tidak ada view pengembalian khusus
        return redirect()->route('dokumensPembayaran.index')->with('info', 'Halaman pengembalian diarahkan ke daftar pembayaran');
    }

    public function rekapanKeterlambatan(){
        $data = array(
            "title" => "Rekap Keterlambatan",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuRekapKeterlambatan' => 'Active',
        );
        return view('pembayaranNEW.dokumens.rekapanKeterlambatan', $data);
    }

    public function rekapanTuTk(Request $request){
        // Get data source parameter (input_ks, input_pupuk, input_tan, input_vd)
        // Prioritize request parameter over session
        $dataSource = $request->get('data_source');
        if (!$dataSource || !in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = session('tu_tk_data_source', 'input_ks');
        }
        $dataSource = in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd']) ? $dataSource : 'input_ks';
        session(['tu_tk_data_source' => $dataSource]);

        // Get filter parameters
        $statusPembayaran = $request->get('status_pembayaran');
        $kategori = $request->get('kategori');
        $umurHutang = $request->get('umur_hutang');
        $vendor = $request->get('vendor');
        $posisiDokumen = $request->get('posisi_dokumen');
        $search = $request->get('search');

        // Select model based on data source
        $model = null;
        $belumDibayarField = 'BELUM_DIBAYAR';
        $jumlahDibayarField = 'JUMLAH_DIBAYAR';
        
        switch ($dataSource) {
            case 'input_pupuk':
                $model = TuTkPupuk::class;
                $belumDibayarField = 'BELUM_DIBAYAR_1';
                break;
            case 'input_vd':
                $model = TuTkVd::class;
                $belumDibayarField = 'BELUM_DIBAYAR_1';
                break;
            case 'input_tan':
                $model = TuTkTan::class;
                $belumDibayarField = 'BELUM_DIBAYAR_1';
                break;
            case 'input_ks':
            default:
                $model = TuTk::class;
                break;
        }

        // Base query
        $query = $model::query();

        // Apply filters
        if ($statusPembayaran) {
            $query->statusPembayaran($statusPembayaran);
        }

        // Kategori filter only for input_ks, input_vd, and input_tan (tu_tk_2023, tu_tk_vd_2023, and tu_tk_tan_2023 have KATEGORI column)
        if ($kategori && ($dataSource === 'input_ks' || $dataSource === 'input_vd' || $dataSource === 'input_tan')) {
            $query->kategori($kategori);
        }

        if ($umurHutang) {
            $query->umurHutang($umurHutang);
        }

        if ($vendor) {
            $query->vendor($vendor);
        }

        if ($posisiDokumen) {
            $query->posisiDokumen($posisiDokumen);
        }

        if ($search) {
            $query->search($search);
        }

        // Get all data for statistics (before pagination)
        $allData = $query->get();

        // Calculate Dashboard Scorecards
        $totalOutstanding = $allData->sum(function($item) use ($belumDibayarField) {
            return (float) ($item->{$belumDibayarField} ?? 0);
        });

        $totalDokumenBelumLunas = $allData->filter(function($item) use ($belumDibayarField) {
            $belumDibayar = (float) ($item->{$belumDibayarField} ?? 0);
            return $belumDibayar > 0;
        })->count();

        // Calculate total terbayar tahun ini - sum of all JUMLAH_DIBAYAR
        $totalTerbayarTahunIni = $model::whereNotNull($jumlahDibayarField)
            ->whereRaw("COALESCE({$jumlahDibayarField}, 0) > 0")
            ->sum($jumlahDibayarField);

        // Jatuh tempo minggu ini (umur hutang > 60 hari)
        $jatuhTempoMingguIni = $allData->filter(function($item) use ($belumDibayarField) {
            $umurHari = (int) ($item->UMUR_HUTANG_HARI ?? 0);
            return $umurHari > 60 && (float) ($item->{$belumDibayarField} ?? 0) > 0;
        })->count();

        // Get unique values for filter dropdowns
        $kategoris = ($dataSource === 'input_ks' || $dataSource === 'input_vd' || $dataSource === 'input_tan') 
            ? $model::distinct()->pluck('KATEGORI')->filter()->sort()->values() 
            : collect([]);
        $vendors = $model::distinct()->pluck('VENDOR')->filter()->sort()->values()->take(100); // Limit to 100 for performance
        $posisiDokumens = $model::distinct()->pluck('POSISI_DOKUMEN')->filter()->sort()->values();

        // Get 5 dokumen terlama belum dibayar for dashboard widget
        $dokumenTerlama = $model::whereRaw("COALESCE({$belumDibayarField}, 0) > 0")
            ->orderBy('UMUR_HUTANG_HARI', 'desc')
            ->take(5)
            ->get();

        // Paginate results
        $perPage = $request->get('per_page', session('tu_tk_per_page', 25));
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int)$perPage : 25;
        session(['tu_tk_per_page' => $perPage]);
        
        $dokumens = $query->orderBy('UMUR_HUTANG_HARI', 'desc')
                         ->orderBy('TANGGAL_MASUK_DOKUMEN', 'desc')
                         ->paginate($perPage)
                         ->withQueryString();

        $data = array(
            "title" => "Rekapan TU/TK",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
            'menuRekapanTuTk' => 'Active',
            // Data source
            'dataSource' => $dataSource,
            'belumDibayarField' => $belumDibayarField,
            'jumlahDibayarField' => $jumlahDibayarField,
            // Dashboard Scorecards
            'totalOutstanding' => $totalOutstanding,
            'totalDokumenBelumLunas' => $totalDokumenBelumLunas,
            'totalTerbayarTahunIni' => $totalTerbayarTahunIni ?? 0,
            'jatuhTempoMingguIni' => $jatuhTempoMingguIni,
            'dokumenTerlama' => $dokumenTerlama,
            // Data
            'dokumens' => $dokumens,
            // Filter options
            'kategoris' => $kategoris,
            'vendors' => $vendors,
            'posisiDokumens' => $posisiDokumens,
            // Current filters
            'statusPembayaran' => $statusPembayaran,
            'kategori' => $kategori,
            'umurHutang' => $umurHutang,
            'vendor' => $vendor,
            'posisiDokumen' => $posisiDokumen,
            'search' => $search,
            'perPage' => $perPage,
        );

        return view('pembayaranNEW.dokumens.rekapanTuTk', $data);
    }

    /**
     * Store payment installment for TuTk document
     */
    public function storePaymentInstallment(Request $request)
    {
        // Get data source from request
        $dataSource = $request->get('data_source', 'input_ks');
        if (!in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = 'input_ks';
        }

        // Select model and primary key based on data source
        if ($dataSource === 'input_pupuk') {
            $model = TuTkPupuk::class;
            $primaryKey = 'EXTRA_COL_0';
            $tableName = 'tu_tk_pupuk_2023';
            $belumDibayarField = 'BELUM_DIBAYAR_1';
        } elseif ($dataSource === 'input_vd') {
            $model = TuTkVd::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_vd_2023';
            $belumDibayarField = 'BELUM_DIBAYAR_1';
        } elseif ($dataSource === 'input_tan') {
            $model = TuTkTan::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_tan_2023';
            $belumDibayarField = 'BELUM_DIBAYAR_1';
        } else {
            $model = TuTk::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_2023';
            $belumDibayarField = 'BELUM_DIBAYAR1';
        }

        $request->validate([
            'kontrol' => "required|exists:{$tableName},{$primaryKey}",
            'payment_sequence' => 'required|integer|min:1|max:6',
            'tanggal_bayar' => 'required|date',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $tuTk = $model::where($primaryKey, $request->kontrol)->firstOrFail();
            
            // Create payment log with data_source
            $paymentLog = PaymentLog::create([
                'tu_tk_kontrol' => $request->kontrol,
                'data_source' => $dataSource,
                'payment_sequence' => $request->payment_sequence,
                'tanggal_bayar' => $request->tanggal_bayar,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->user()->name ?? 'System',
            ]);

            // Update TuTk fields based on payment sequence
            $tanggalField = 'TANGGAL_BAYAR_' . $this->getRomanNumeral($request->payment_sequence);
            // Field mapping: JUMLAH1, JUMLAH2, JUMLAH3, JUMLAH4, JUMLAH5, JUMLAH6
            $jumlahField = 'JUMLAH' . ($request->payment_sequence == 1 ? '1' : $request->payment_sequence);

            $tuTk->update([
                $tanggalField => $request->tanggal_bayar,
                $jumlahField => $request->jumlah,
            ]);

            // Recalculate total payment
            $totalDibayar = (float)($tuTk->JUMLAH1 ?? 0) 
                          + (float)($tuTk->JUMLAH2 ?? 0)
                          + (float)($tuTk->JUMLAH3 ?? 0)
                          + (float)($tuTk->JUMLAH4 ?? 0)
                          + (float)($tuTk->JUMLAH5 ?? 0)
                          + (float)($tuTk->JUMLAH6 ?? 0);

            $belumDibayar = (float)($tuTk->NILAI ?? 0) - $totalDibayar;

            // If fully paid, set completion date
            if ($belumDibayar <= 0) {
                $tuTk->update([
                    'TANGGAL_BAYAR_RAMPUNG' => $request->tanggal_bayar,
                ]);
            }

            $tuTk->update([
                'JUMLAH_DIBAYAR' => $totalDibayar,
                $belumDibayarField => max(0, $belumDibayar),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'payment_log' => $paymentLog,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment logs for a TuTk document
     */
    public function getPaymentLogs(Request $request, $kontrol)
    {
        // Get data source from request
        $dataSource = $request->get('data_source', 'input_ks');
        if (!in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = 'input_ks';
        }

        $paymentLogs = PaymentLog::where('tu_tk_kontrol', $kontrol)
            ->where('data_source', $dataSource)
            ->orderBy('payment_sequence')
            ->orderBy('tanggal_bayar')
            ->get();

        return response()->json($paymentLogs);
    }

    /**
     * Get position tracking timeline for a TuTk document
     */
    public function getPositionTimeline(Request $request, $kontrol)
    {
        // Get data source from request
        $dataSource = $request->get('data_source', 'input_ks');
        if (!in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = 'input_ks';
        }

        // Select model and primary key based on data source
        if ($dataSource === 'input_pupuk') {
            $model = TuTkPupuk::class;
            $primaryKey = 'EXTRA_COL_0';
        } elseif ($dataSource === 'input_vd') {
            $model = TuTkVd::class;
            $primaryKey = 'KONTROL';
        } elseif ($dataSource === 'input_tan') {
            $model = TuTkTan::class;
            $primaryKey = 'KONTROL';
        } else {
            $model = TuTk::class;
            $primaryKey = 'KONTROL';
        }

        $tuTk = $model::where($primaryKey, $kontrol)->firstOrFail();
        
        // Get position tracking history
        $positionTrackings = DocumentPositionTracking::where('tu_tk_kontrol', $kontrol)
            ->where('data_source', $dataSource)
            ->orderBy('changed_at', 'desc')
            ->get();

        // Get payment logs for timeline
        $paymentLogs = PaymentLog::where('tu_tk_kontrol', $kontrol)
            ->where('data_source', $dataSource)
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        // Combine and sort by date
        $timeline = collect();
        
        foreach ($positionTrackings as $tracking) {
            $timeline->push([
                'type' => 'position',
                'date' => $tracking->changed_at,
                'title' => 'Perubahan Posisi Dokumen',
                'description' => $tracking->posisi_lama 
                    ? "Dari: {$tracking->posisi_lama} → Ke: {$tracking->posisi_baru}"
                    : "Posisi: {$tracking->posisi_baru}",
                'changed_by' => $tracking->changed_by,
                'keterangan' => $tracking->keterangan,
                'icon' => 'fa-map-marker-alt',
                'color' => '#083E40',
            ]);
        }

        foreach ($paymentLogs as $log) {
            $timeline->push([
                'type' => 'payment',
                'date' => $log->tanggal_bayar,
                'title' => "Pembayaran ke-{$log->payment_sequence}",
                'description' => "Jumlah: Rp " . number_format($log->jumlah, 0, ',', '.'),
                'changed_by' => $log->created_by,
                'keterangan' => $log->keterangan,
                'icon' => 'fa-money-bill-wave',
                'color' => '#889717',
            ]);
        }

        // Sort by date descending
        $timeline = $timeline->sortByDesc('date')->values();

        return response()->json([
            'tu_tk' => $tuTk,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Update document position
     */
    public function updateDocumentPosition(Request $request)
    {
        // Get data source from request
        $dataSource = $request->get('data_source', 'input_ks');
        if (!in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = 'input_ks';
        }

        // Select model and primary key based on data source
        if ($dataSource === 'input_pupuk') {
            $model = TuTkPupuk::class;
            $primaryKey = 'EXTRA_COL_0';
            $tableName = 'tu_tk_pupuk_2023';
        } elseif ($dataSource === 'input_vd') {
            $model = TuTkVd::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_vd_2023';
        } elseif ($dataSource === 'input_tan') {
            $model = TuTkTan::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_tan_2023';
        } else {
            $model = TuTk::class;
            $primaryKey = 'KONTROL';
            $tableName = 'tu_tk_2023';
        }

        $request->validate([
            'kontrol' => "required|exists:{$tableName},{$primaryKey}",
            'posisi_baru' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $tuTk = $model::where($primaryKey, $request->kontrol)->firstOrFail();
            $posisiLama = $tuTk->POSISI_DOKUMEN;

            // Log position change with data_source
            DocumentPositionTracking::logPositionChange(
                $request->kontrol,
                $request->posisi_baru,
                $posisiLama,
                $request->keterangan,
                auth()->user()->name ?? 'System',
                $dataSource
            );

            // Update position
            $tuTk->update([
                'POSISI_DOKUMEN' => $request->posisi_baru,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Posisi dokumen berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui posisi dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export rekapan TuTk to Excel or PDF
     */
    public function exportRekapanTuTk(Request $request)
    {
        $exportType = $request->get('export', 'excel'); // excel or pdf
        
        // Get data source from request
        $dataSource = $request->get('data_source', 'input_ks');
        if (!in_array($dataSource, ['input_ks', 'input_pupuk', 'input_tan', 'input_vd'])) {
            $dataSource = 'input_ks';
        }

        // Select model based on data source
        if ($dataSource === 'input_pupuk') {
            $model = TuTkPupuk::class;
        } elseif ($dataSource === 'input_vd') {
            $model = TuTkVd::class;
        } elseif ($dataSource === 'input_tan') {
            $model = TuTkTan::class;
        } else {
            $model = TuTk::class;
        }
        
        // Get filters from request (same as rekapanTuTk method)
        $selectedYear = $request->get('tahun', date('Y'));
        $statusFilter = $request->get('status_pembayaran');
        $kategoriFilter = $request->get('kategori');
        $umurHutangFilter = $request->get('umur_hutang');
        $vendorFilter = $request->get('vendor');
        $posisiDokumenFilter = $request->get('posisi_dokumen');
        $search = $request->get('search');

        // Build query (same logic as rekapanTuTk)
        $query = $model::query();

        if ($statusFilter) {
            $query->statusPembayaran($statusFilter);
        }

        // Kategori filter only for input_ks, input_vd, and input_tan
        if ($kategoriFilter && ($dataSource === 'input_ks' || $dataSource === 'input_vd' || $dataSource === 'input_tan')) {
            $query->kategori($kategoriFilter);
        }

        if ($umurHutangFilter) {
            $query->umurHutang($umurHutangFilter);
        }

        if ($vendorFilter) {
            $query->vendor($vendorFilter);
        }

        if ($posisiDokumenFilter) {
            $query->posisiDokumen($posisiDokumenFilter);
        }

        if ($search) {
            $query->search($search);
        }

        // Get all results (no pagination for export)
        $dokumens = $query->orderBy('UMUR_HUTANG_HARI', 'desc')
                         ->orderBy('TANGGAL_MASUK_DOKUMEN', 'desc')
                         ->get();

        if ($exportType === 'excel') {
            return $this->exportTuTkToExcel($dokumens, $dataSource);
        } else {
            return $this->exportTuTkToPDF($dokumens, $dataSource);
        }
    }

    /**
     * Export TuTk to Excel
     */
    private function exportTuTkToExcel($dokumens, $dataSource = 'input_ks')
    {
        $sourceLabel = $dataSource === 'input_pupuk' ? 'Pupuk' : 'KS';
        $filename = 'Rekapan_TU_TK_' . $sourceLabel . '_' . date('Y-m-d_His') . '.csv';
        
        $belumDibayarField = $dataSource === 'input_pupuk' ? 'BELUM_DIBAYAR_1' : 'BELUM_DIBAYAR1';
        $hasKategori = $dataSource === 'input_ks';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($dokumens, $belumDibayarField, $hasKategori) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            $headerRow = [
                'No',
                'Agenda',
                'No. SPP',
                'Tgl SPP',
                'Vendor',
            ];
            
            if ($hasKategori) {
                $headerRow[] = 'Kategori';
            }
            
            $headerRow = array_merge($headerRow, [
                'Nilai',
                'Dibayar',
                'Belum Dibayar',
                'Status Pembayaran',
                'Persentase',
                'Umur Hutang (Hari)',
                'Posisi Dokumen',
            ]);
            
            fputcsv($file, $headerRow, ';');

            // Data rows
            foreach ($dokumens as $index => $dokumen) {
                $status = $dokumen->status_pembayaran ?? 'belum_lunas';
                $persentase = $dokumen->persentase_pembayaran ?? 0;
                
                $row = [
                    $index + 1,
                    $dokumen->AGENDA ?? '-',
                    $dokumen->NO_SPP ?? '-',
                    $dokumen->TGL_SPP ?? '-',
                    $dokumen->VENDOR ?? '-',
                ];
                
                if ($hasKategori) {
                    $row[] = $dokumen->KATEGORI ?? '-';
                }
                
                $belumDibayarValue = $dokumen->{$belumDibayarField} ?? 0;
                
                $row = array_merge($row, [
                    number_format($dokumen->NILAI ?? 0, 0, ',', '.'),
                    number_format($dokumen->JUMLAH_DIBAYAR ?? 0, 0, ',', '.'),
                    number_format($belumDibayarValue, 0, ',', '.'),
                    $status == 'lunas' ? 'Lunas' : ($status == 'parsial' ? 'Parsial' : 'Belum Lunas'),
                    number_format($persentase, 2) . '%',
                    $dokumen->UMUR_HUTANG_HARI ?? 0,
                    $dokumen->POSISI_DOKUMEN ?? '-',
                ]);
                
                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export TuTk to PDF
     */
    private function exportTuTkToPDF($dokumens, $dataSource = 'input_ks')
    {
        $sourceLabel = $dataSource === 'input_pupuk' ? 'Pupuk' : 'KS';
        $belumDibayarField = $dataSource === 'input_pupuk' ? 'BELUM_DIBAYAR_1' : 'BELUM_DIBAYAR1';
        
        $data = [
            'title' => 'Rekapan TU/TK - ' . $sourceLabel,
            'dokumens' => $dokumens,
            'exportDate' => now()->format('d F Y H:i:s'),
            'dataSource' => $dataSource,
            'belumDibayarField' => $belumDibayarField,
            'hasKategori' => $dataSource === 'input_ks',
        ];

        $pdf = \PDF::loadView('pembayaranNEW.dokumens.export-tu-tk-pdf', $data);
        return $pdf->download('Rekapan_TU_TK_' . $sourceLabel . '_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Helper: Convert number to Roman numeral
     */
    private function getRomanNumeral($number)
    {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
        return $romans[$number] ?? 'I';
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
        // Perhatikan: di database menggunakan camelCase (ibuA, ibuB), bukan snake_case (ibu_a, ibu_b)
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'ibuA', 'ibuB', 'ibu_a', 'ibu_b'];

        // Base query - semua dokumen yang sudah melewati proses awal
        $query = Dokumen::whereNotNull('nomor_agenda');

        // Apply status filter based on new logic
        if ($statusPembayaran) {
            if ($statusPembayaran === 'belum_siap_dibayar') {
                // Belum siap = masih di akutansi, perpajakan, ibu_a, ibu_b
                $query->whereIn('current_handler', $belumSiapHandlers);
            } elseif ($statusPembayaran === 'siap_dibayar') {
                // Siap dibayar = sudah di pembayaran tapi belum dibayar
                $query->where(function($q) {
                    $q->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
                })->where(function($q) {
                    $q->whereNull('status_pembayaran')
                      ->orWhere('status_pembayaran', '!=', 'sudah_dibayar')
                      ->orWhere('status_pembayaran', '!=', 'SUDAH DIBAYAR')
                      ->orWhere('status_pembayaran', '!=', 'SUDAH_DIBAYAR');
                });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                // Sudah dibayar - cek berbagai format (dari CSV: "SUDAH DIBAYAR", dari aplikasi: "sudah_dibayar")
                $query->where(function($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                      ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                      ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR');
                });
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

        // Apply rekapan detail filters (only for rekapan_table mode)
        if ($mode === 'rekapan_table') {
            // Filter by Dibayar Kepada (Vendor)
            $filterDibayarKepada = request('filter_dibayar_kepada_column');
            if ($filterDibayarKepada) {
                $query->where('dibayar_kepada', $filterDibayarKepada);
            }

            // Filter by Kategori
            $filterKategori = request('filter_kategori_column');
            if ($filterKategori) {
                $query->where('kategori', $filterKategori);
            }

            // Filter by Jenis Dokumen
            $filterJenisDokumen = request('filter_jenis_dokumen_column');
            if ($filterJenisDokumen) {
                $query->where('jenis_dokumen', $filterJenisDokumen);
            }

            // Filter by Jenis Sub Pekerjaan
            $filterJenisSubPekerjaan = request('filter_jenis_sub_pekerjaan_column');
            if ($filterJenisSubPekerjaan) {
                $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
            }

            // Filter by Jenis Pembayaran
            $filterJenisPembayaran = request('filter_jenis_pembayaran_column');
            if ($filterJenisPembayaran) {
                $query->where('jenis_pembayaran', $filterJenisPembayaran);
            }

            // Filter by Kebun (check both kebun and nama_kebuns fields)
            $filterKebun = request('filter_jenis_kebuns_column');
            if ($filterKebun) {
                $query->where(function($q) use ($filterKebun) {
                    $q->where('kebun', $filterKebun)
                      ->orWhere('nama_kebuns', $filterKebun);
                });
            }
        }

        // Helper function to calculate computed status
        // Di halaman pembayaran, hanya ada 2 status: siap_dibayar dan sudah_dibayar
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            // Cek apakah dokumen sudah dibayar berdasarkan:
            // 1. Ada tanggal_dibayar, ATAU
            // 2. Ada link_bukti_pembayaran, ATAU
            // 3. status_pembayaran = 'sudah_dibayar' (berbagai format)
            if ($doc->tanggal_dibayar || 
                $doc->link_bukti_pembayaran ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH_DIBAYAR' ||
                strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar') {
                return 'sudah_dibayar';
            }
            
            // Jika sudah di pembayaran atau sudah dikirim ke pembayaran
            if ($doc->current_handler === 'pembayaran' || $doc->status === 'sent_to_pembayaran') {
                return 'siap_dibayar';
            }
            
            // Jika masih di handler lain (akutansi, perpajakan, ibuA, ibuB)
            // Status ini tidak muncul di halaman pembayaran, tapi tetap dihitung untuk total
            return 'belum_siap_dibayar';
        };

        // For rekapan table mode - group by vendor
        $rekapanByVendor = null;
        if ($mode === 'rekapan_table' && !empty($selectedColumns)) {
            $allDocsForRekapan = $query->orderBy('dibayar_kepada')->get();

            // Add computed status to each document
            $allDocsForRekapan->each(function($doc) use ($getComputedStatus) {
                $doc->computed_status = $getComputedStatus($doc);
            });

            // Filter: Hanya tampilkan dokumen dengan status 'siap_dibayar' atau 'sudah_dibayar'
            // Status 'belum_siap_dibayar' tidak muncul di halaman pembayaran
            $allDocsForRekapan = $allDocsForRekapan->filter(function($doc) {
                return in_array($doc->computed_status, ['siap_dibayar', 'sudah_dibayar']);
            })->values();

            // Apply additional status filter if provided (for filtering between siap_dibayar and sudah_dibayar)
            if ($statusPembayaran && in_array($statusPembayaran, ['siap_dibayar', 'sudah_dibayar'])) {
                $allDocsForRekapan = $allDocsForRekapan->filter(function($doc) use ($statusPembayaran) {
                    return $doc->computed_status === $statusPembayaran;
                })->values();
            }

            // Group by vendor - dokumen tanpa vendor akan dikelompokkan sebagai null
            $rekapanByVendor = $allDocsForRekapan->groupBy(function($doc) {
                // Jika dibayar_kepada kosong atau null, gunakan null sebagai key
                return $doc->dibayar_kepada ?: null;
            })->map(function($docs, $vendor) {
                return [
                    'vendor' => $vendor ?: 'Tidak Diketahui',
                    'documents' => $docs,
                    'total_nilai' => $docs->sum('nilai_rupiah'),
                    'total_belum_dibayar' => $docs->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah'),
                    'total_siap_dibayar' => $docs->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah'),
                    'total_sudah_dibayar' => $docs->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah'),
                    'count' => $docs->count(),
                ];
            })->filter(function($vendorData) {
                // Filter out "Tidak Diketahui" group jika user tidak ingin melihatnya
                // Untuk sementara kita biarkan, tapi bisa diubah nanti jika diperlukan
                return true;
            });
        }

        // Get all results first (before pagination) to apply computed_status filter
        $allDokumens = $query->orderBy('created_at', 'desc')->get();

        // Add computed status to each document
        $allDokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Filter: Hanya tampilkan dokumen dengan status 'siap_dibayar' atau 'sudah_dibayar'
        // Status 'belum_siap_dibayar' tidak muncul di halaman pembayaran
        $allDokumens = $allDokumens->filter(function($doc) {
            return in_array($doc->computed_status, ['siap_dibayar', 'sudah_dibayar']);
        })->values();

        // Apply additional status filter if provided (for filtering between siap_dibayar and sudah_dibayar)
        if ($statusPembayaran && in_array($statusPembayaran, ['siap_dibayar', 'sudah_dibayar'])) {
            $allDokumens = $allDokumens->filter(function($doc) use ($statusPembayaran) {
                return $doc->computed_status === $statusPembayaran;
            })->values();
        }

        // Paginate the filtered results manually
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $total = $allDokumens->count();
        $currentItems = $allDokumens->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $dokumens = new LengthAwarePaginator(
            $currentItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->except('page')
            ]
        );

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

        // Helper function to create a new query with same filters
        $createFilteredQuery = function() use ($year, $month) {
            $q = Dokumen::whereNotNull('nomor_agenda');
            if ($year) {
                $q->whereYear('created_at', $year);
            }
            if ($month) {
                $q->whereMonth('created_at', $month);
            }
            return $q;
        };

        // Get unique values for Dibayar Kepada (Vendor)
        $availableDibayarKepada = $createFilteredQuery()
            ->whereNotNull('dibayar_kepada')
            ->where('dibayar_kepada', '!=', '')
            ->selectRaw('DISTINCT dibayar_kepada')
            ->orderBy('dibayar_kepada')
            ->pluck('dibayar_kepada', 'dibayar_kepada');

        // Get unique values for Kategori
        $availableKategori = $createFilteredQuery($year, $month)
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->selectRaw('DISTINCT kategori')
            ->orderBy('kategori')
            ->pluck('kategori', 'kategori');

        // Get unique values for Jenis Dokumen
        $availableJenisDokumen = $createFilteredQuery($year, $month)
            ->whereNotNull('jenis_dokumen')
            ->where('jenis_dokumen', '!=', '')
            ->selectRaw('DISTINCT jenis_dokumen')
            ->orderBy('jenis_dokumen')
            ->pluck('jenis_dokumen', 'jenis_dokumen');

        // Get unique values for Jenis Sub Pekerjaan
        $availableJenisSubPekerjaan = $createFilteredQuery($year, $month)
            ->whereNotNull('jenis_sub_pekerjaan')
            ->where('jenis_sub_pekerjaan', '!=', '')
            ->selectRaw('DISTINCT jenis_sub_pekerjaan')
            ->orderBy('jenis_sub_pekerjaan')
            ->pluck('jenis_sub_pekerjaan', 'jenis_sub_pekerjaan');

        // Get unique values for Jenis Pembayaran
        $availableJenisPembayaran = $createFilteredQuery($year, $month)
            ->whereNotNull('jenis_pembayaran')
            ->where('jenis_pembayaran', '!=', '')
            ->selectRaw('DISTINCT jenis_pembayaran')
            ->orderBy('jenis_pembayaran')
            ->pluck('jenis_pembayaran', 'jenis_pembayaran');

        // Get unique values for Kebun (from both kebun and nama_kebuns fields)
        // First get from kebun field
        $kebunFromKebun = $createFilteredQuery($year, $month)
            ->whereNotNull('kebun')
            ->where('kebun', '!=', '')
            ->distinct()
            ->pluck('kebun', 'kebun');
        
        // Then get from nama_kebuns field
        $kebunFromNamaKebuns = $createFilteredQuery($year, $month)
            ->whereNotNull('nama_kebuns')
            ->where('nama_kebuns', '!=', '')
            ->distinct()
            ->pluck('nama_kebuns', 'nama_kebuns');
        
        // Merge both collections and remove duplicates
        $availableKebuns = $kebunFromKebun->merge($kebunFromNamaKebuns)->unique()->sortKeys();

        // Available columns for rekapan table
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'dibayar_kepada' => 'Nama Vendor/Dibayar Kepada',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'jenis_sub_pekerjaan' => 'Jenis Subbagian',
            'nomor_mirror' => 'Nomor Miro',
            'nomor_spp' => 'No SPP',
            'uraian_spp' => 'Uraian SPP',
            'tanggal_spp' => 'TGL SPP',
            'tanggal_berita_acara' => 'TGL BA',
            'no_berita_acara' => 'Nomor BA',
            'tanggal_berakhir_ba' => 'TGL Akhir BA',
            'no_spk' => 'Nomor SPK',
            'tanggal_spk' => 'TGL SPK',
            'tanggal_berakhir_spk' => 'TGL Berakhir SPK',
            'kebun' => 'Kebun',
            'umur_dokumen_tanggal_masuk' => 'Umur(tgl Msk)',
            'umur_dokumen_tanggal_spp' => 'Umur(Tgl SPP)',
            'umur_dokumen_tanggal_ba' => 'Umur(Tgl BA)',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nilai_belum_siap_bayar' => 'Belum siap bayar',
            'nilai_siap_bayar' => 'sudah siap bayar',
            'nilai_sudah_dibayar' => 'sudah dibayar',
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
            // Dropdown data
            'availableDibayarKepada' => $availableDibayarKepada,
            'availableKategori' => $availableKategori,
            'availableJenisDokumen' => $availableJenisDokumen,
            'availableJenisSubPekerjaan' => $availableJenisSubPekerjaan,
            'availableJenisPembayaran' => $availableJenisPembayaran,
            'availableKebuns' => $availableKebuns,
        ];

        return view('pembayaranNEW.dokumens.rekapanDokumen', $data);
    }

    /**
     * Export rekapan pembayaran to Excel or PDF
     */
    public function exportRekapan(Request $request)
    {
        $exportType = $request->get('export', 'excel'); // excel or pdf
        $mode = $request->get('mode', 'normal'); // normal or rekapan_table
        $statusPembayaran = $request->get('status_pembayaran');
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');
        $selectedColumns = $request->get('columns', []);

        // Handler yang dianggap "belum siap dibayar"
        // Perhatikan: di database menggunakan camelCase (ibuA, ibuB), bukan snake_case (ibu_a, ibu_b)
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'ibuA', 'ibuB', 'ibu_a', 'ibu_b'];

        // Base query - semua dokumen yang sudah melewati proses awal
        $query = Dokumen::whereNotNull('nomor_agenda');

        // Apply status filter
        if ($statusPembayaran) {
            if ($statusPembayaran === 'belum_siap_dibayar') {
                $query->whereIn('current_handler', $belumSiapHandlers);
            } elseif ($statusPembayaran === 'siap_dibayar') {
                $query->where(function($q) {
                    $q->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran');
                })->where(function($q) {
                    $q->whereNull('status_pembayaran')
                      ->orWhere('status_pembayaran', '!=', 'sudah_dibayar')
                      ->orWhere('status_pembayaran', '!=', 'SUDAH DIBAYAR')
                      ->orWhere('status_pembayaran', '!=', 'SUDAH_DIBAYAR');
                });
            } elseif ($statusPembayaran === 'sudah_dibayar') {
                // Sudah dibayar - cek berbagai format (dari CSV: "SUDAH DIBAYAR", dari aplikasi: "sudah_dibayar")
                $query->where(function($q) {
                    $q->where('status_pembayaran', 'sudah_dibayar')
                      ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                      ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR');
                });
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

        // Apply rekapan detail filters (only for rekapan_table mode)
        if ($mode === 'rekapan_table') {
            // Filter by Dibayar Kepada (Vendor)
            $filterDibayarKepada = $request->get('filter_dibayar_kepada_column');
            if ($filterDibayarKepada) {
                $query->where('dibayar_kepada', $filterDibayarKepada);
            }

            // Filter by Kategori
            $filterKategori = $request->get('filter_kategori_column');
            if ($filterKategori) {
                $query->where('kategori', $filterKategori);
            }

            // Filter by Jenis Dokumen
            $filterJenisDokumen = $request->get('filter_jenis_dokumen_column');
            if ($filterJenisDokumen) {
                $query->where('jenis_dokumen', $filterJenisDokumen);
            }

            // Filter by Jenis Sub Pekerjaan
            $filterJenisSubPekerjaan = $request->get('filter_jenis_sub_pekerjaan_column');
            if ($filterJenisSubPekerjaan) {
                $query->where('jenis_sub_pekerjaan', $filterJenisSubPekerjaan);
            }

            // Filter by Jenis Pembayaran
            $filterJenisPembayaran = $request->get('filter_jenis_pembayaran_column');
            if ($filterJenisPembayaran) {
                $query->where('jenis_pembayaran', $filterJenisPembayaran);
            }

            // Filter by Kebun (check both kebun and nama_kebuns fields)
            $filterKebun = $request->get('filter_jenis_kebuns_column');
            if ($filterKebun) {
                $query->where(function($q) use ($filterKebun) {
                    $q->where('kebun', $filterKebun)
                      ->orWhere('nama_kebuns', $filterKebun);
                });
            }
        }

        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            // Jika sudah dibayar - cek berbagai format (dari CSV: "SUDAH DIBAYAR", dari aplikasi: "sudah_dibayar")
            $statusPembayaran = strtoupper(trim($doc->status_pembayaran ?? ''));
            if ($statusPembayaran === 'SUDAH_DIBAYAR' || 
                $statusPembayaran === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar') {
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

        // Get all documents (no pagination for export)
        // For rekapan_table mode, order by dibayar_kepada to match the display order
        if ($mode === 'rekapan_table') {
            $dokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
                ->orderBy('dibayar_kepada')
                ->get();
        } else {
            $dokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Add computed status to each document
        $dokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Available columns mapping - must match rekapan() method
        $availableColumns = [
            'nomor_agenda' => 'Nomor Agenda',
            'dibayar_kepada' => 'Nama Vendor/Dibayar Kepada',
            'jenis_pembayaran' => 'Jenis Pembayaran',
            'jenis_sub_pekerjaan' => 'Jenis Subbagian',
            'nomor_mirror' => 'Nomor Miro',
            'nomor_spp' => 'No SPP',
            'uraian_spp' => 'Uraian SPP',
            'tanggal_spp' => 'TGL SPP',
            'tanggal_berita_acara' => 'TGL BA',
            'no_berita_acara' => 'Nomor BA',
            'tanggal_berakhir_ba' => 'TGL Akhir BA',
            'no_spk' => 'Nomor SPK',
            'tanggal_spk' => 'TGL SPK',
            'tanggal_berakhir_spk' => 'TGL Berakhir SPK',
            'kebun' => 'Kebun',
            'umur_dokumen_tanggal_masuk' => 'Umur(tgl Msk)',
            'umur_dokumen_tanggal_spp' => 'Umur(Tgl SPP)',
            'umur_dokumen_tanggal_ba' => 'Umur(Tgl BA)',
            'nilai_rupiah' => 'Nilai Rupiah',
            'nilai_belum_siap_bayar' => 'Belum siap bayar',
            'nilai_siap_bayar' => 'sudah siap bayar',
            'nilai_sudah_dibayar' => 'sudah dibayar',
        ];

        // Default columns for normal mode
        $defaultColumns = ['nomor_agenda', 'nomor_spp', 'sent_to_pembayaran_at', 'dibayar_kepada', 'nilai_rupiah', 'computed_status', 'tanggal_dibayar'];
        
        // For rekapan_table mode, use selected columns or default
        if ($mode === 'rekapan_table' && !empty($selectedColumns)) {
            $columnsToExport = $selectedColumns;
        } else {
            $columnsToExport = $defaultColumns;
        }

        if ($exportType === 'excel') {
            return $this->exportToExcel($dokumens, $columnsToExport, $availableColumns, $mode, $statusPembayaran, $year, $month, $search);
        } else {
            return $this->exportToPDF($dokumens, $columnsToExport, $availableColumns, $mode, $statusPembayaran, $year, $month, $search);
        }
    }

    /**
     * Export to Excel (using CSV format that Excel can open)
     */
    private function exportToExcel($dokumens, $columns, $availableColumns, $mode, $statusFilter, $year, $month, $search)
    {
        $filename = 'Rekapan_Pembayaran_' . date('Y-m-d_His') . '.csv';
        
        // Header row
        $headers = [];
        foreach ($columns as $col) {
            if ($col === 'sent_to_pembayaran_at') {
                $headers[] = 'Tgl Diterima';
            } elseif ($col === 'computed_status') {
                $headers[] = 'Status';
            } elseif ($col === 'tanggal_dibayar') {
                $headers[] = 'Tgl Dibayar';
            } else {
                $headers[] = $availableColumns[$col] ?? ucfirst(str_replace('_', ' ', $col));
            }
        }
        
        // Build CSV content
        $csvContent = '';
        // Add BOM for UTF-8 (so Excel opens it correctly)
        $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        // Add header row
        $csvContent .= $this->arrayToCsv($headers) . "\n";
        
        // Add data rows
        foreach ($dokumens as $dokumen) {
            $row = [];
            foreach ($columns as $col) {
                $value = $this->getColumnValue($dokumen, $col);
                $row[] = $value;
            }
            $csvContent .= $this->arrayToCsv($row) . "\n";
        }
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
    
    /**
     * Convert array to CSV row
     */
    private function arrayToCsv($array, $delimiter = ';')
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $array, $delimiter);
        rewind($output);
        $data = fread($output, 1048576);
        fclose($output);
        return rtrim($data, "\n\r");
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($dokumens, $columns, $availableColumns, $mode, $statusFilter, $year, $month, $search)
    {
        // Handler yang dianggap "belum siap dibayar"
        // Perhatikan: di database menggunakan camelCase (ibuA, ibuB), bukan snake_case (ibu_a, ibu_b)
        $belumSiapHandlers = ['akutansi', 'perpajakan', 'ibuA', 'ibuB', 'ibu_a', 'ibu_b'];
        
        // Helper function to calculate computed status
        $getComputedStatus = function($doc) use ($belumSiapHandlers) {
            $statusPembayaran = strtoupper(trim($doc->status_pembayaran ?? ''));
            if ($statusPembayaran === 'SUDAH_DIBAYAR' || 
                $statusPembayaran === 'SUDAH DIBAYAR' ||
                $doc->status_pembayaran === 'sudah_dibayar') {
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

        // Add computed status to all documents first
        $dokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Calculate totals for subtotal and grand total
        $rekapanByVendor = null;
        $grandTotalNilai = 0;
        $grandTotalBelum = 0;
        $grandTotalSiap = 0;
        $grandTotalSudah = 0;

        if ($mode === 'rekapan_table' && !empty($columns)) {
            // Group by vendor - same logic as rekapan() method
            $rekapanByVendor = $dokumens->groupBy(function($doc) {
                return $doc->dibayar_kepada ?: null;
            })->map(function($docs, $vendor) {
                return [
                    'vendor' => $vendor ?: 'Tidak Diketahui',
                    'documents' => $docs,
                    'total_nilai' => $docs->sum('nilai_rupiah'),
                    'total_belum_dibayar' => $docs->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah'),
                    'total_siap_dibayar' => $docs->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah'),
                    'total_sudah_dibayar' => $docs->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah'),
                    'count' => $docs->count(),
                ];
            })->sortBy(function($vendorData) {
                // Sort vendors: "Tidak Diketahui" should come last, others alphabetically
                if ($vendorData['vendor'] === 'Tidak Diketahui') {
                    return 'zzz_' . $vendorData['vendor'];
                }
                return $vendorData['vendor'];
            });

            // Calculate grand totals
            foreach ($rekapanByVendor as $vendorData) {
                $grandTotalNilai += $vendorData['total_nilai'];
                $grandTotalBelum += $vendorData['total_belum_dibayar'];
                $grandTotalSiap += $vendorData['total_siap_dibayar'];
                $grandTotalSudah += $vendorData['total_sudah_dibayar'];
            }
        } else {
            // For normal mode, calculate grand totals from all documents
            $grandTotalNilai = $dokumens->sum('nilai_rupiah');
            $grandTotalBelum = $dokumens->where('computed_status', 'belum_siap_dibayar')->sum('nilai_rupiah');
            $grandTotalSiap = $dokumens->where('computed_status', 'siap_dibayar')->sum('nilai_rupiah');
            $grandTotalSudah = $dokumens->where('computed_status', 'sudah_dibayar')->sum('nilai_rupiah');
        }

        // Find the index of first value column
        $valueColumns = ['nilai_rupiah', 'nilai_belum_siap_bayar', 'nilai_siap_bayar', 'nilai_sudah_dibayar'];
        $firstValueIndex = null;
        foreach($columns as $idx => $col) {
            if (in_array($col, $valueColumns)) {
                $firstValueIndex = $idx;
                break;
            }
        }
        $colspanCount = $firstValueIndex !== null ? $firstValueIndex + 1 : count($columns) + 1;

        // Prepare data for PDF view
        $pdfData = [
            'dokumens' => $dokumens,
            'columns' => $columns,
            'availableColumns' => $availableColumns,
            'statusFilter' => $statusFilter,
            'year' => $year,
            'month' => $month,
            'search' => $search,
            'mode' => $mode,
            'rekapanByVendor' => $rekapanByVendor,
            'grandTotalNilai' => $grandTotalNilai,
            'grandTotalBelum' => $grandTotalBelum,
            'grandTotalSiap' => $grandTotalSiap,
            'grandTotalSudah' => $grandTotalSudah,
            'firstValueIndex' => $firstValueIndex,
            'colspanCount' => $colspanCount,
        ];

        // Return view that can be printed as PDF using browser print
        return view('pembayaranNEW.dokumens.export-pdf', $pdfData);
    }

    /**
     * Get column value for export
     */
    private function getColumnValue($dokumen, $column)
    {
        switch ($column) {
            case 'nomor_agenda':
                return $dokumen->nomor_agenda ?? '-';
            case 'nomor_spp':
                return $dokumen->nomor_spp ?? '-';
            case 'sent_to_pembayaran_at':
                return $dokumen->sent_to_pembayaran_at ? $dokumen->sent_to_pembayaran_at->format('d/m/Y') : '-';
            case 'dibayar_kepada':
                if ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0) {
                    return $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ');
                }
                return $dokumen->dibayar_kepada ?? '-';
            case 'nilai_rupiah':
                return 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.');
            case 'computed_status':
                $status = $dokumen->computed_status ?? 'belum_siap_dibayar';
                if ($status === 'sudah_dibayar') return 'Sudah Dibayar';
                if ($status === 'siap_dibayar') return 'Siap Dibayar';
                return 'Belum Siap Dibayar';
            case 'tanggal_dibayar':
                return $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('d/m/Y') : '-';
            case 'jenis_pembayaran':
                return $dokumen->jenis_pembayaran ?? '-';
            case 'jenis_sub_pekerjaan':
                return $dokumen->jenis_sub_pekerjaan ?? '-';
            case 'nomor_mirror':
                return $dokumen->nomor_mirror ?? '-';
            case 'tanggal_spp':
                return $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-';
            case 'uraian_spp':
                return $dokumen->uraian_spp ?? '-';
            case 'kebun':
                return $dokumen->kebun ?? $dokumen->nama_kebuns ?? '-';
            case 'tanggal_berita_acara':
                return $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-';
            case 'no_berita_acara':
                return $dokumen->no_berita_acara ?? '-';
            case 'tanggal_berakhir_ba':
                return $dokumen->tanggal_berakhir_ba ? $dokumen->tanggal_berakhir_ba->format('d/m/Y') : '-';
            case 'no_spk':
                return $dokumen->no_spk ?? '-';
            case 'tanggal_spk':
                return $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-';
            case 'tanggal_berakhir_spk':
                return $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-';
            case 'umur_dokumen_tanggal_masuk':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_masuk) {
                    $tanggalMasuk = \Carbon\Carbon::parse($dokumen->tanggal_masuk)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalMasuk->lte($hariIni) ? (int) $tanggalMasuk->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'umur_dokumen_tanggal_spp':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_spp) {
                    $tanggalSpp = \Carbon\Carbon::parse($dokumen->tanggal_spp)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalSpp->lte($hariIni) ? (int) $tanggalSpp->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'umur_dokumen_tanggal_ba':
                // Jika sudah dibayar, tampilkan 0
                if (isset($dokumen->computed_status) && $dokumen->computed_status === 'sudah_dibayar') {
                    return '0 HARI';
                }
                if ($dokumen->tanggal_berita_acara) {
                    $tanggalBa = \Carbon\Carbon::parse($dokumen->tanggal_berita_acara)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $days = $tanggalBa->lte($hariIni) ? (int) $tanggalBa->diffInDays($hariIni) : 0;
                    return $days . ' HARI';
                }
                return '-';
            case 'nilai_belum_siap_bayar':
                return $dokumen->computed_status === 'belum_siap_dibayar' 
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            case 'nilai_siap_bayar':
                return $dokumen->computed_status === 'siap_dibayar' 
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            case 'nilai_sudah_dibayar':
                return $dokumen->computed_status === 'sudah_dibayar' 
                    ? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.')
                    : '-';
            default:
                return $dokumen->$column ?? '-';
        }
    }

    public function diagram(){
        // Get current year or from request
        $selectedYear = request('year', date('Y'));

        // Get available years for filter
        $availableYears = Dokumen::selectRaw('YEAR(tanggal_masuk) as year')
            ->whereNotNull('tanggal_masuk')
            ->where(function($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran')
                      ->orWhere('created_by', 'pembayaran');
            })
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // If no years found, use current year
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        // Initialize monthly data (1-12 for all months)
        $monthlyData = array_fill(0, 12, 0);
        $keterlambatanData = array_fill(0, 12, 0);
        $ketepatanData = array_fill(0, 12, 0);
        $selesaiData = array_fill(0, 12, 0);
        $tidakSelesaiData = array_fill(0, 12, 0);

        // Get monthly document statistics
        $monthlyStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month, COUNT(*) as count')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where(function($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran')
                      ->orWhere('created_by', 'pembayaran');
            })
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Get keterlambatan data
        $keterlambatanStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month,
            AVG(CASE WHEN DATEDIFF(COALESCE(tanggal_selesai, NOW()), tanggal_masuk) > 7
                THEN DATEDIFF(COALESCE(tanggal_selesai, NOW()), tanggal_masuk) - 7
                ELSE 0 END) as avg_keterlambatan')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where(function($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran')
                      ->orWhere('created_by', 'pembayaran');
            })
            ->groupBy('month')
            ->pluck('avg_keterlambatan', 'month')
            ->toArray();

        // Get completion statistics
        $completionStats = Dokumen::selectRaw('MONTH(tanggal_masuk) as month,
            SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai_count,
            SUM(CASE WHEN status != "selesai" THEN 1 ELSE 0 END) as tidak_selesai_count')
            ->whereYear('tanggal_masuk', $selectedYear)
            ->where(function($query) {
                $query->where('current_handler', 'pembayaran')
                      ->orWhere('status', 'sent_to_pembayaran')
                      ->orWhere('created_by', 'pembayaran');
            })
            ->groupBy('month')
            ->get();

        // Fill the data arrays
        foreach ($monthlyStats as $month => $count) {
            $monthlyData[$month - 1] = $count;
        }

        foreach ($keterlambatanStats as $month => $keterlambatan) {
            $keterlambatanData[$month - 1] = min($keterlambatan, 100); // Cap at 100%
            $ketepatanData[$month - 1] = max(0, 100 - $keterlambatan); // Complement
        }

        foreach ($completionStats as $stat) {
            $selesaiData[$stat->month - 1] = $stat->selesai_count;
            $tidakSelesaiData[$stat->month - 1] = $stat->tidak_selesai_count;
        }

        // Indonesian month names
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $data = array(
            "title" => "Diagram Pembayaran",
            "module" => "pembayaran",
            "menuDashboard" => "",
            'menuDokumen' => 'Active',
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
        return view('pembayaranNEW.diagramPembayaran', $data);
    }

    /**
     * Get document detail for Pembayaran view
     */
    /**
     * Get payment data (tanggal_dibayar and link_bukti_pembayaran) for edit modal
     */
    public function getPaymentData(Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'tanggal_dibayar' => $dokumen->tanggal_dibayar ? $dokumen->tanggal_dibayar->format('Y-m-d') : '',
            'link_bukti_pembayaran' => $dokumen->link_bukti_pembayaran ?? '',
        ]);
    }

    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Allow access if document is handled by pembayaran or sent to pembayaran
        $allowedHandlers = ['pembayaran', 'akutansi', 'perpajakan', 'ibuB'];
        $allowedStatuses = ['sent_to_pembayaran', 'sedang diproses', 'selesai', 'sudah_dibayar'];

        if (!in_array($dokumen->current_handler, $allowedHandlers) && !in_array($dokumen->status, $allowedStatuses)) {
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

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
            'Bulan' => $dokumen->bulan,
            'Tahun' => $dokumen->tahun,
            'No SPP' => $dokumen->nomor_spp,
            'Tanggal SPP' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-',
            'Uraian SPP' => $dokumen->uraian_spp ?? '-',
            'Nilai Rp' => $dokumen->formatted_nilai_rupiah ?? 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.'),
            'Kategori' => $dokumen->kategori ?? '-',
            'Jenis Dokumen' => $dokumen->jenis_dokumen ?? '-',
            'SubBagian Pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '-',
            'Jenis Pembayaran' => $dokumen->jenis_pembayaran ?? '-',
            'Kebun' => $dokumen->kebun ?? '-',
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

        if ($hasPerpajakanData || $dokumen->status == 'sent_to_akutansi' || $dokumen->status == 'sent_to_pembayaran') {
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

        // Data Akutansi Section - Always show for documents sent to pembayaran
        $html .= '<div class="detail-section-separator">
            <div class="separator-content">
                <i class="fa-solid fa-calculator"></i>
                <span>Data Akutansi</span>
                <span class="tax-badge" style="background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);">DITAMBAHKAN OLEH TEAM AKUTANSI</span>
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
     * Set deadline for pembayaran
     */
    public function setDeadline(Request $request, Dokumen $dokumen)
    {
        // Only allow if current_handler is pembayaran
        if ($dokumen->current_handler !== 'pembayaran') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Enhanced logging with user context
            Log::info('=== SET DEADLINE PEMBAYARAN REQUEST START ===', [
                'document_id' => $dokumen->id,
                'current_handler' => $dokumen->current_handler,
                'current_status' => $dokumen->status,
                'deadline_exists' => $dokumen->deadline_at ? true : false,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role,
                'request_data' => $request->all()
            ]);

            // Use helper for validation
            $validation = DokumenHelper::canSetDeadline($dokumen);
            if (!$validation['can_set']) {
                Log::warning('Deadline set failed - Validation error', [
                    'document_id' => $dokumen->id,
                    'user_role' => Auth::user()?->role,
                    'validation_result' => $validation
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                    'debug_info' => $validation['debug']
                ], 403);
            }

            $validated = $request->validate([
                'deadline_days' => 'required|integer|min:1|max:30',
                'deadline_note' => 'nullable|string|max:500',
            ], [
                'deadline_days.required' => 'Periode deadline wajib dipilih.',
                'deadline_days.integer' => 'Periode deadline harus berupa angka.',
                'deadline_days.min' => 'Deadline minimal 1 hari.',
                'deadline_days.max' => 'Deadline maksimal 30 hari.',
                'deadline_note.max' => 'Catatan maksimal 500 karakter.',
            ]);

            $deadlineDays = (int) $validated['deadline_days'];
            $deadlineNote = isset($validated['deadline_note']) && trim($validated['deadline_note']) !== '' 
                ? trim($validated['deadline_note']) 
                : null;

            // Update using transaction
            DB::transaction(function () use ($dokumen, $deadlineDays, $deadlineNote) {
                $dokumen->update([
                    'deadline_at' => now()->addDays($deadlineDays),
                    'deadline_days' => $deadlineDays,
                    'deadline_note' => $deadlineNote,
                    'status' => 'sedang diproses',
                    'processed_at' => now(),
                ]);
            });

            Log::info('Deadline successfully set for Pembayaran', [
                'document_id' => $dokumen->id,
                'deadline_days' => $deadlineDays,
                'deadline_at' => $dokumen->fresh()->deadline_at
            ]);

            return response()->json([
                'success' => true,
                'message' => "Deadline berhasil ditetapkan ({$deadlineDays} hari). Dokumen sekarang terbuka untuk diproses.",
                'deadline' => $dokumen->fresh()->deadline_at->format('d M Y, H:i'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error setting Pembayaran deadline: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error setting deadline in Pembayaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan deadline'
            ], 500);
        }
    }

    /**
     * Check for new documents sent to pembayaran
     */
    public function checkUpdates(Request $request)
    {
        try {
            $lastChecked = $request->input('last_checked', 0);

            // Convert timestamp to Carbon instance
            $lastCheckedDate = $lastChecked > 0
                ? \Carbon\Carbon::createFromTimestamp($lastChecked)
                : \Carbon\Carbon::now();

            // Cek dokumen baru yang dikirim ke pembayaran
            $newDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'pembayaran')
                          ->where('status', 'sent_to_pembayaran');
                })
                ->where('updated_at', '>', $lastCheckedDate)
                ->latest('updated_at')
                ->take(10)
                ->get();

            $totalDocuments = Dokumen::where(function($query) {
                    $query->where('current_handler', 'pembayaran')
                          ->orWhere('status', 'sent_to_pembayaran');
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
                        'sent_at' => $doc->updated_at ? $doc->updated_at->format('d/m/Y H:i') : '-',
                        'sent_from' => 'Team Akutansi',
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
     * Show import form
     */
    public function showImportForm()
    {
        return view('pembayaranNEW.importCsv');
    }

    /**
     * Import CSV data
     */
    public function importCsv(Request $request)
    {
        // Validate request
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $fileName = 'import_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('imports', $fileName, 'public');

            // Execute import command
            $exitCode = \Illuminate\Support\Facades\Artisan::call('import:csv', [
                'path' => storage_path('app/public/' . $filePath)
            ]);

            if ($exitCode === 0) {
                // Redirect with success message
                return redirect()->back()->with('success', 'Data CSV berhasil diimport ke database!');
            } else {
                return redirect()->back()->with('error', 'Gagal import data CSV. Silakan cek log error.');
            }

        } catch (\Exception $e) {
            \Log::error('CSV Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat import CSV: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template
     */
    public function downloadCsvTemplate()
    {
        $templatePath = public_path('DATA 12.csv');

        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan');
        }

        return response()->download($templatePath, 'template_dokumen.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_dokumen.csv"',
        ]);
    }

    /**
     * Payment Analytics Page - Interactive Drill-down Dashboard
     * Menampilkan ringkasan tahunan dengan grid bulan dan tabel dokumen interaktif
     */
    public function analytics(Request $request)
    {
        try {
            // Get selected year from request, default to current year
            $selectedYear = $request->get('year', date('Y'));
            $selectedMonth = $request->get('month', null); // For initial filter (optional)
            $statusFilter = $request->get('status', 'sudah_dibayar'); // Default to 'sudah_dibayar' for analytics

            // Validate year
            if (!is_numeric($selectedYear) || $selectedYear < 2000 || $selectedYear > 2100) {
                $selectedYear = date('Y');
            }

            // Helper function to calculate computed status (same as dokumens method)
            $getComputedStatus = function($doc) {
                if ($doc->tanggal_dibayar || 
                    $doc->link_bukti_pembayaran ||
                    strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH_DIBAYAR' ||
                    strtoupper(trim($doc->status_pembayaran ?? '')) === 'SUDAH DIBAYAR' ||
                    $doc->status_pembayaran === 'sudah_dibayar') {
                    return 'sudah_dibayar';
                }
                
                if (in_array($doc->status, ['processed_by_akutansi', 'sent_to_pembayaran', 'processed_by_pembayaran']) ||
                    ($doc->current_handler === 'pembayaran' && in_array($doc->status, ['sedang diproses', 'sent_to_pembayaran']))) {
                    return 'siap_bayar';
                }
                
                return 'belum_siap_bayar';
            };

        // Get all documents with nomor_agenda
        // For sudah_dibayar status, filter by status_pembayaran or tanggal_dibayar at database level first
        $query = Dokumen::whereNotNull('nomor_agenda');
        
        if ($statusFilter === 'sudah_dibayar') {
            // Pre-filter untuk sudah_dibayar di database level
            // Check if tanggal_dibayar column exists before adding it to query
            $hasTanggalDibayarColumn = false;
            try {
                $hasTanggalDibayarColumn = Schema::hasColumn('dokumens', 'tanggal_dibayar');
            } catch (\Exception $e) {
                Log::warning('Failed to check if tanggal_dibayar column exists', ['error' => $e->getMessage()]);
            }
            
            $query->where(function($q) use ($hasTanggalDibayarColumn) {
                $q->where('status_pembayaran', 'sudah_dibayar')
                  ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR')
                  ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                  ->orWhereNotNull('link_bukti_pembayaran');
                
                // Only check tanggal_dibayar if column exists
                if ($hasTanggalDibayarColumn) {
                    $q->orWhereNotNull('tanggal_dibayar');
                }
            });
        }
        
        $allDokumens = $query->with(['dibayarKepadas', 'dokumenPos', 'dokumenPrs'])
            ->get();

        // Add computed status to each document
        $allDokumens->each(function($doc) use ($getComputedStatus) {
            $doc->computed_status = $getComputedStatus($doc);
        });

        // Filter by status (to ensure exact match with computed status)
        $allDokumens = $allDokumens->filter(function($doc) use ($statusFilter) {
            return $doc->computed_status === $statusFilter;
        })->values();

        // Filter by year (based on tanggal_dibayar for sudah_dibayar, or tahun/bulan field for others)
        $allDokumens = $allDokumens->filter(function($doc) use ($selectedYear, $statusFilter) {
            try {
                if ($statusFilter === 'sudah_dibayar') {
                    // Prioritize tanggal_dibayar if available
                    if ($doc->tanggal_dibayar) {
                        $tanggal = $doc->tanggal_dibayar instanceof \Carbon\Carbon 
                            ? $doc->tanggal_dibayar 
                            : \Carbon\Carbon::parse($doc->tanggal_dibayar);
                        return $tanggal->format('Y') == $selectedYear;
                    }
                    
                    // Fallback: use tahun field from dokumen if available
                    if (!empty($doc->tahun)) {
                        return (int)$doc->tahun == (int)$selectedYear;
                    }
                    
                    // Fallback: use tanggal_spp if available
                    if ($doc->tanggal_spp) {
                        $tanggalSpp = $doc->tanggal_spp instanceof \Carbon\Carbon 
                            ? $doc->tanggal_spp 
                            : \Carbon\Carbon::parse($doc->tanggal_spp);
                        return $tanggalSpp->format('Y') == $selectedYear;
                    }
                    
                    // Last fallback: use tanggal_masuk
                    if ($doc->tanggal_masuk) {
                        $tanggalMasuk = $doc->tanggal_masuk instanceof \Carbon\Carbon 
                            ? $doc->tanggal_masuk 
                            : \Carbon\Carbon::parse($doc->tanggal_masuk);
                        return $tanggalMasuk->format('Y') == $selectedYear;
                    }
                    
                    return false;
                } else {
                    // For other status, use tahun field or created_at
                    if (!empty($doc->tahun)) {
                        return (int)$doc->tahun == (int)$selectedYear;
                    }
                    
                    if (!$doc->created_at) {
                        return false;
                    }
                    $created = $doc->created_at instanceof \Carbon\Carbon 
                        ? $doc->created_at 
                        : \Carbon\Carbon::parse($doc->created_at);
                    return $created->format('Y') == $selectedYear;
                }
            } catch (\Exception $e) {
                \Log::warning('Error filtering document by year', [
                    'doc_id' => $doc->id,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        })->values();

        // Calculate yearly summary (Total Nominal & Total Jumlah Dokumen)
        $yearlySummary = [
            'total_nominal' => $allDokumens->sum('nilai_rupiah'),
            'total_dokumen' => $allDokumens->count(),
        ];

        // Calculate monthly statistics (12 months)
        $monthlyStats = [];
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        for ($month = 1; $month <= 12; $month++) {
            $monthDokumens = $allDokumens->filter(function($doc) use ($month, $statusFilter) {
                try {
                    if ($statusFilter === 'sudah_dibayar') {
                        // Prioritize tanggal_dibayar if available
                        if ($doc->tanggal_dibayar) {
                            $tanggal = $doc->tanggal_dibayar instanceof \Carbon\Carbon 
                                ? $doc->tanggal_dibayar 
                                : \Carbon\Carbon::parse($doc->tanggal_dibayar);
                            return (int)$tanggal->format('m') == $month;
                        }
                        
                        // Fallback: use bulan field from dokumen if available
                        if (!empty($doc->bulan)) {
                            $bulanMap = [
                                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
                                'January' => 1, 'February' => 2, 'March' => 3, 'May' => 5,
                                'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9,
                                'October' => 10, 'November' => 11, 'December' => 12
                            ];
                            $docBulan = isset($bulanMap[$doc->bulan]) ? $bulanMap[$doc->bulan] : null;
                            if ($docBulan && $docBulan == $month) {
                                return true;
                            }
                        }
                        
                        // Fallback: use tanggal_spp if available
                        if ($doc->tanggal_spp) {
                            $tanggalSpp = $doc->tanggal_spp instanceof \Carbon\Carbon 
                                ? $doc->tanggal_spp 
                                : \Carbon\Carbon::parse($doc->tanggal_spp);
                            return (int)$tanggalSpp->format('m') == $month;
                        }
                        
                        // Last fallback: use tanggal_masuk
                        if ($doc->tanggal_masuk) {
                            $tanggalMasuk = $doc->tanggal_masuk instanceof \Carbon\Carbon 
                                ? $doc->tanggal_masuk 
                                : \Carbon\Carbon::parse($doc->tanggal_masuk);
                            return (int)$tanggalMasuk->format('m') == $month;
                        }
                        
                        return false;
                    } else {
                        // For other status, use created_at or bulan field
                        if (!empty($doc->bulan)) {
                            $bulanMap = [
                                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
                                'January' => 1, 'February' => 2, 'March' => 3, 'May' => 5,
                                'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9,
                                'October' => 10, 'November' => 11, 'December' => 12
                            ];
                            $docBulan = isset($bulanMap[$doc->bulan]) ? $bulanMap[$doc->bulan] : null;
                            if ($docBulan && $docBulan == $month) {
                                return true;
                            }
                        }
                        
                        if (!$doc->created_at) {
                            return false;
                        }
                        $created = $doc->created_at instanceof \Carbon\Carbon 
                            ? $doc->created_at 
                            : \Carbon\Carbon::parse($doc->created_at);
                        return (int)$created->format('m') == $month;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error filtering document by month', [
                        'doc_id' => $doc->id,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            })->values();

            $monthlyStats[$month] = [
                'name' => $monthNames[$month],
                'count' => $monthDokumens->count(),
                'total_nominal' => $monthDokumens->sum('nilai_rupiah'),
                'dokumens' => $monthDokumens,
            ];
        }

        // Get documents for table (filtered by selected month if provided)
        $tableDokumens = $allDokumens;
        if ($selectedMonth && is_numeric($selectedMonth) && $selectedMonth >= 1 && $selectedMonth <= 12) {
            $tableDokumens = $allDokumens->filter(function($doc) use ($selectedMonth, $statusFilter) {
                try {
                    if ($statusFilter === 'sudah_dibayar') {
                        // Prioritize tanggal_dibayar if available
                        if ($doc->tanggal_dibayar) {
                            $tanggal = $doc->tanggal_dibayar instanceof \Carbon\Carbon 
                                ? $doc->tanggal_dibayar 
                                : \Carbon\Carbon::parse($doc->tanggal_dibayar);
                            return (int)$tanggal->format('m') == $selectedMonth;
                        }
                        
                        // Fallback: use bulan field from dokumen if available
                        if (!empty($doc->bulan)) {
                            $bulanMap = [
                                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
                                'January' => 1, 'February' => 2, 'March' => 3, 'May' => 5,
                                'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9,
                                'October' => 10, 'November' => 11, 'December' => 12
                            ];
                            $docBulan = isset($bulanMap[$doc->bulan]) ? $bulanMap[$doc->bulan] : null;
                            if ($docBulan && $docBulan == $selectedMonth) {
                                return true;
                            }
                        }
                        
                        // Fallback: use tanggal_spp if available
                        if ($doc->tanggal_spp) {
                            $tanggalSpp = $doc->tanggal_spp instanceof \Carbon\Carbon 
                                ? $doc->tanggal_spp 
                                : \Carbon\Carbon::parse($doc->tanggal_spp);
                            return (int)$tanggalSpp->format('m') == $selectedMonth;
                        }
                        
                        // Last fallback: use tanggal_masuk
                        if ($doc->tanggal_masuk) {
                            $tanggalMasuk = $doc->tanggal_masuk instanceof \Carbon\Carbon 
                                ? $doc->tanggal_masuk 
                                : \Carbon\Carbon::parse($doc->tanggal_masuk);
                            return (int)$tanggalMasuk->format('m') == $selectedMonth;
                        }
                        
                        return false;
                    } else {
                        // For other status, use bulan field or created_at
                        if (!empty($doc->bulan)) {
                            $bulanMap = [
                                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
                                'January' => 1, 'February' => 2, 'March' => 3, 'May' => 5,
                                'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9,
                                'October' => 10, 'November' => 11, 'December' => 12
                            ];
                            $docBulan = isset($bulanMap[$doc->bulan]) ? $bulanMap[$doc->bulan] : null;
                            if ($docBulan && $docBulan == $selectedMonth) {
                                return true;
                            }
                        }
                        
                        if (!$doc->created_at) {
                            return false;
                        }
                        $created = $doc->created_at instanceof \Carbon\Carbon 
                            ? $doc->created_at 
                            : \Carbon\Carbon::parse($doc->created_at);
                        return (int)$created->format('m') == $selectedMonth;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error filtering document by month for table', [
                        'doc_id' => $doc->id,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            })->values();

            // Sort by tanggal_dibayar or created_at descending
            $tableDokumens = $tableDokumens->sortByDesc(function($doc) use ($statusFilter) {
                try {
                    if ($statusFilter === 'sudah_dibayar' && $doc->tanggal_dibayar) {
                        return $doc->tanggal_dibayar instanceof \Carbon\Carbon 
                            ? $doc->tanggal_dibayar 
                            : \Carbon\Carbon::parse($doc->tanggal_dibayar);
                    }
                    return $doc->created_at instanceof \Carbon\Carbon 
                        ? $doc->created_at 
                        : ($doc->created_at ? \Carbon\Carbon::parse($doc->created_at) : now());
                } catch (\Exception $e) {
                    return now();
                }
            })->values();
        } else {
            // Sort all documents by tanggal_dibayar or created_at descending
            $tableDokumens = $tableDokumens->sortByDesc(function($doc) use ($statusFilter) {
                try {
                    if ($statusFilter === 'sudah_dibayar' && $doc->tanggal_dibayar) {
                        return $doc->tanggal_dibayar instanceof \Carbon\Carbon 
                            ? $doc->tanggal_dibayar 
                            : \Carbon\Carbon::parse($doc->tanggal_dibayar);
                    }
                    return $doc->created_at instanceof \Carbon\Carbon 
                        ? $doc->created_at 
                        : ($doc->created_at ? \Carbon\Carbon::parse($doc->created_at) : now());
                } catch (\Exception $e) {
                    return now();
                }
            })->values();
        }

        // Get available years for dropdown based on status filter
        // For "sudah_dibayar": get years from tanggal_dibayar (karena ini yang relevan)
        // Also include years from created_at as fallback
        $availableYears = [];
        
        if ($statusFilter === 'sudah_dibayar') {
            // Get years from tanggal_dibayar (primary source for sudah_dibayar)
            $yearsFromTanggalDibayar = Dokumen::whereNotNull('tanggal_dibayar')
                ->selectRaw('DISTINCT YEAR(tanggal_dibayar) as year')
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->filter()
                ->toArray();
            
            // Also get years from created_at as backup using more efficient query
            try {
                $yearsFromCreatedAtQuery = Dokumen::whereNotNull('nomor_agenda')
                    ->where(function($q) {
                        $q->where('status_pembayaran', 'sudah_dibayar')
                          ->orWhere('status_pembayaran', 'SUDAH_DIBAYAR')
                          ->orWhere('status_pembayaran', 'SUDAH DIBAYAR')
                          ->orWhereNotNull('tanggal_dibayar')
                          ->orWhereNotNull('link_bukti_pembayaran');
                    })
                    ->selectRaw('DISTINCT YEAR(created_at) as year')
                    ->whereNotNull('created_at')
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->filter()
                    ->toArray();
                
                $yearsFromCreatedAt = $yearsFromCreatedAtQuery;
            } catch (\Exception $e) {
                \Log::warning('Error getting years from created_at: ' . $e->getMessage());
                $yearsFromCreatedAt = [];
            }
            
            // Merge both sources and remove duplicates
            $availableYears = array_unique(array_merge($yearsFromTanggalDibayar, $yearsFromCreatedAt));
        } else {
            // For other status, get years from created_at using direct query
            try {
                $availableYears = Dokumen::whereNotNull('nomor_agenda')
                    ->selectRaw('DISTINCT YEAR(created_at) as year')
                    ->whereNotNull('created_at')
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->filter()
                    ->toArray();
            } catch (\Exception $e) {
                \Log::warning('Error getting available years: ' . $e->getMessage());
                $availableYears = [(int)date('Y')];
            }
        }
        
        // Sort descending (newest first)
        rsort($availableYears);
        
        // Ensure availableYears is not empty - add current year if empty
        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }
        
        // Ensure selectedYear is in availableYears, if not, use first available year or current year
        if (!in_array((int)$selectedYear, $availableYears)) {
            $selectedYear = !empty($availableYears) ? (int)$availableYears[0] : date('Y');
        }

        $data = [
            'title' => 'Analitik Pembayaran',
            'module' => 'pembayaran',
            'menuDashboard' => '',
            'menuDokumen' => 'Active',
            'menuDaftarDokumen' => '',
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'statusFilter' => $statusFilter,
            'yearlySummary' => $yearlySummary,
            'monthlyStats' => $monthlyStats,
            'dokumens' => $tableDokumens,
            'availableYears' => $availableYears,
        ];

        return view('pembayaranNEW.analytics', $data);
        
        } catch (\Exception $e) {
            Log::error('Error in analytics method: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return error page or redirect with error message
            return redirect()->route('pembayaran.rekapan')
                ->with('error', 'Terjadi kesalahan saat memuat halaman analitik: ' . $e->getMessage());
        }
    }
}

