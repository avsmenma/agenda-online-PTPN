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
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use App\Helpers\SearchHelper;
use App\Helpers\ActivityLogHelper;

class DokumenController extends Controller
{
    public function index(Request $request)
    {
        // IbuA only sees documents created by ibuA
        // Order by nomor_agenda descending (numerically) - so new documents with lower numbers appear in correct position
        // Example: 2010, 2009, 2006 (new), 2005, 2004, 2003
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'activityLogs'])
            ->where('created_by', 'ibuA')
            ->orderByRaw('CASE 
                WHEN nomor_agenda REGEXP "^[0-9]+$" THEN CAST(nomor_agenda AS UNSIGNED)
                ELSE 0
            END DESC')
            ->orderBy('nomor_agenda', 'DESC'); // Secondary sort for non-numeric or same numeric values

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
                    // Dokumen yang belum dikirim - no status record for ibuB exists
                    $query->whereDoesntHave('roleStatuses', function ($q) {
                        $q->where('role_code', 'ibub');
                    })->where('status', 'draft');
                    break;

                case 'menunggu_approval':
                    // Dokumen yang menunggu approval dari Reviewer (IbuB)
                    $query->whereHas('roleStatuses', function ($q) {
                        $q->where('role_code', 'ibub')
                            ->where('status', \App\Models\DokumenStatus::STATUS_PENDING);
                    });
                    break;

                case 'terkirim':
                    // Dokumen yang sudah di-approve oleh Reviewer atau diteruskan ke department lain
                    $query->where(function ($q) {
                        // Approved by IbuB
                        $q->whereHas('roleStatuses', function ($q2) {
                            $q2->where('role_code', 'ibub')
                                ->where('status', \App\Models\DokumenStatus::STATUS_APPROVED);
                        })
                            // OR has status record for other departments (sent to them)
                            ->orWhereHas('roleStatuses', function ($q3) {
                                $q3->whereIn('role_code', ['perpajakan', 'akutansi', 'pembayaran']);
                            });
                    });
                    break;

                case 'dikembalikan':
                    // Dokumen yang dikembalikan/rejected
                    $query->whereHas('roleStatuses', function ($q) {
                        $q->where('status', \App\Models\DokumenStatus::STATUS_REJECTED);
                    });
                    break;
            }
        }

        $perPage = $request->get('per_page', 10);
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
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "suggestions" => $suggestions ?? [],
            "availableColumns" => $availableColumns,
            "selectedColumns" => $selectedColumns,
        );

        return view('IbuA.dokumens.daftarDokumen', $data);
    }

    public function create()
    {
        // Ambil data dari database cash_bank_new
        $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
        $subKriteria = SubKriteria::all();
        $itemSubKriteria = ItemSubKriteria::all();
        
        $data = array(
            "title" => "Tambah Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "",
            "menuTambahDokumen" => "active",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "kategoriKriteria" => $kategoriKriteria,
            "subKriteria" => $subKriteria,
            "itemSubKriteria" => $itemSubKriteria,
        );
        return view('IbuA.dokumens.tambahDokumen', $data);
    }

    /**
     * Get document detail for AJAX request for IbuA
     */
    public function getDocumentDetailForIbuA(Dokumen $dokumen)
    {
        // Only allow if created by ibuA
        if ($dokumen->created_by !== 'ibuA') {
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return HTML partial for detail view
        $html = view('IbuA.dokumens.partials.document_detail', compact('dokumen'))->render();

        return response($html);
    }

    /**
     * Get document detail for modal popup (JSON format)
     */
    public function getDocumentDetail(Dokumen $dokumen)
    {
        // Only allow if created by ibuA
        if ($dokumen->created_by !== 'ibuA') {
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
                'nomor_mirror' => $dokumen->nomor_mirror,
                'nomor_miro' => $dokumen->nomor_miro,
                'no_berita_acara' => $dokumen->no_berita_acara,
                'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('Y-m-d') : null,
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
     * Get document progress for IbuA
     */
    public function getDocumentProgressForIbuA(Dokumen $dokumen)
    {
        // Only allow if created by ibuA
        if ($dokumen->created_by !== 'ibuA') {
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
            'step' => 'Dokumen Dibuat',
            'status' => 'completed',
            'time' => $dokumen->created_at ? $dokumen->created_at->format('d M Y H:i') : '',
            'description' => 'Dokumen berhasil dibuat oleh Ibu Tarapul',
            'percentage' => 20
        ];

        // Step 2: Document Sent to IbuB
        if ($dokumen->status === 'draft') {
            $timeline[] = [
                'step' => 'Menunggu Pengiriman',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang disiapkan untuk dikirim ke Ibu Yuni',
                'percentage' => 0
            ];
            $totalPercentage = 20;
        } elseif ($dokumen->status === 'sent_to_ibub') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
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
        } elseif ($dokumen->status === 'returned_to_ibua') {
            $timeline[] = [
                'step' => 'Terkirim ke Ibu Yuni',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            $timeline[] = [
                'step' => 'Dikembalikan ke Ibu Tarapul',
                'status' => 'completed',
                'time' => $dokumen->returned_to_ibua_at ? $dokumen->returned_to_ibua_at->format('d M Y H:i') : '',
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
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
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
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke Ibu Yuni untuk diproses',
                'percentage' => 30
            ];

            $timeline[] = [
                'step' => 'Sedang Diproses IbuB',
                'status' => 'completed',
                'time' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah selesai diproses oleh IbuB',
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

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text
            $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
            if (empty($nilaiRupiah) || $nilaiRupiah <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nilai rupiah harus lebih dari 0.');
            }
            $nilaiRupiah = (float) $nilaiRupiah;

            // Extract bulan dan tahun dari tanggal SPP
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

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;
            
            if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
            }
            
            if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                $subKriteria = SubKriteria::find($request->sub_kriteria);
            }
            
            if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                $itemSubKriteria = ItemSubKriteria::find($request->item_sub_kriteria);
            }

            // Create dokumen
            $dokumen = Dokumen::create([
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $bulanIndonesia[$tanggalSpp->month],
                'tahun' => $tanggalSpp->year,
                'tanggal_masuk' => now(), // Realtime timestamp
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
                'created_by' => 'ibuA',
                'current_handler' => 'ibuA',
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

            // Log activity: dokumen dibuat
            try {
                ActivityLogHelper::logCreated($dokumen);
            } catch (\Exception $logException) {
                \Log::error('Failed to log document creation: ' . $logException->getMessage());
            }

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil ditambahkan dengan nomor agenda: ' . $dokumen->nomor_agenda);

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Error creating dokumen: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan dokumen. Silakan coba lagi.');
        }
    }

    public function edit(Dokumen $dokumen)
    {
        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Ambil data dari database cash_bank_new untuk dropdown baru
        try {
            $kategoriKriteria = KategoriKriteria::where('tipe', 'Keluar')->get();
            $subKriteria = SubKriteria::all();
            $itemSubKriteria = ItemSubKriteria::all();
        } catch (\Exception $e) {
            \Log::error('Error fetching cash_bank data: ' . $e->getMessage());
            // Fallback: gunakan collection kosong jika error
            $kategoriKriteria = collect([]);
            $subKriteria = collect([]);
            $itemSubKriteria = collect([]);
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
            "module" => "IbuA",
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
        );

        return view('IbuA.dokumens.editDokumen', $data);
    }

    public function update(UpdateDokumenRequest $request, Dokumen $dokumen)
    {
        // Validate that user can edit this document
        // Allow editing if:
        // 1. Document is created by IbuA and currently with IbuA
        // 2. Document is rejected (can be edited to fix issues)
        // 3. Document is in draft or returned status
        
        $currentHandler = strtolower($dokumen->current_handler ?? '');
        $createdBy = strtolower($dokumen->created_by ?? '');
        $status = strtolower($dokumen->status ?? '');
        
        // Check if document is created by IbuA (case-insensitive)
        $createdByIbuA = in_array($createdBy, ['ibua', 'ibu a']);
        
        // Check if document is currently with IbuA (case-insensitive)
        $currentHandlerIbuA = in_array($currentHandler, ['ibua', 'ibu a']);
        
        // Check if document is rejected
        $isRejected = false;
        $ibuBStatus = $dokumen->getStatusForRole('ibub');
        if ($ibuBStatus && strtolower($ibuBStatus->status ?? '') === 'rejected') {
            $isRejected = true;
        } else {
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->whereIn('role_code', ['ibub', 'ibuB'])
                ->first();
            $isRejected = $rejectedStatus !== null;
        }
        
        // Check if status allows editing
        $allowedStatuses = ['draft', 'returned_to_ibua'];
        $isAllowedStatus = in_array($status, $allowedStatuses);
        
        // Allow editing if:
        // 1. Document is rejected AND with IbuA (can always be edited)
        // 2. OR document has allowed status AND with IbuA
        if (!$createdByIbuA || !$currentHandlerIbuA) {
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

            // Format nilai rupiah - remove dots, commas, spaces, and "Rp" text
            $nilaiRupiah = preg_replace('/[^0-9]/', '', $request->nilai_rupiah);
            if (empty($nilaiRupiah) || $nilaiRupiah <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nilai rupiah harus lebih dari 0.');
            }
            $nilaiRupiah = (float) $nilaiRupiah;

            // Extract bulan dan tahun dari tanggal SPP untuk update
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

            // Get nama from ID untuk field baru (kriteria_cf, sub_kriteria, item_sub_kriteria)
            $kategoriKriteria = null;
            $subKriteria = null;
            $itemSubKriteria = null;
            
            if ($request->has('kriteria_cf') && $request->kriteria_cf) {
                $kategoriKriteria = KategoriKriteria::find($request->kriteria_cf);
            }
            
            if ($request->has('sub_kriteria') && $request->sub_kriteria) {
                $subKriteria = SubKriteria::find($request->sub_kriteria);
            }
            
            if ($request->has('item_sub_kriteria') && $request->item_sub_kriteria) {
                $itemSubKriteria = ItemSubKriteria::find($request->item_sub_kriteria);
            }

            // Update dokumen
            // IMPORTANT: Status is NOT updated here - it only changes via workflow (send, return, etc)
            // BUT: For rejected documents, we need to ensure status remains 'returned_to_ibua' so they can be resent
            $updateData = [
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $newBulan,
                'tahun' => $newTahun,
                'tanggal_masuk' => $dokumen->tanggal_masuk, // Keep original creation timestamp
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                // Simpan nama dari ID untuk backward compatibility
                'kategori' => $kategoriKriteria ? $kategoriKriteria->nama_kriteria : ($request->kategori ?? $dokumen->kategori),
                'jenis_dokumen' => $subKriteria ? $subKriteria->nama_sub_kriteria : ($request->jenis_dokumen ?? $dokumen->jenis_dokumen),
                'jenis_sub_pekerjaan' => $itemSubKriteria ? $itemSubKriteria->nama_item_sub_kriteria : ($request->jenis_sub_pekerjaan ?? $dokumen->jenis_sub_pekerjaan),
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
                // 'status' => REMOVED - status should only change through workflow, not manual edit
                // 'keterangan' => REMOVED - not used anymore
            ];
            
            // For rejected documents, ensure status remains 'returned_to_ibua' so they can be resent
            // Don't change status if it's already 'returned_to_ibua' (for rejected documents)
            if ($isRejected && $dokumen->status !== 'returned_to_ibua') {
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
                'kategori' => 'Kategori',
                'jenis_dokumen' => 'Jenis Dokumen',
                'jenis_sub_pekerjaan' => 'Jenis Sub Pekerjaan',
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
                                'ibuA'
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
                            'ibuA'
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
     * Send document to IbuB (Reviewer)
     * Sets status to WAITING_REVIEWER_APPROVAL - Approval Gate Implementation
     */
    public function sendToIbuB(Dokumen $dokumen)
    {
        try {
            // Handle old data that might not have workflow fields
            $currentHandler = $dokumen->current_handler ?? 'ibuA';
            $createdBy = $dokumen->created_by ?? 'ibuA';

            // Check if document is created by IbuA (case-insensitive)
            $createdByIbuA = in_array(strtolower($createdBy), ['ibua', 'ibu a']);
            
            // Check if document is currently with IbuA (case-insensitive)
            $currentHandlerIbuA = in_array(strtolower($currentHandler), ['ibua', 'ibu a']);
            
            // Check if document is rejected (can be sent again)
            $isRejected = false;
            $ibuBStatus = $dokumen->getStatusForRole('ibub');
            if ($ibuBStatus && strtolower($ibuBStatus->status ?? '') === 'rejected') {
                $isRejected = true;
            } else {
                // Fallback: check from roleStatuses directly
                $rejectedStatus = $dokumen->roleStatuses()
                    ->where('status', 'rejected')
                    ->whereIn('role_code', ['ibub', 'ibuB'])
                    ->first();
                $isRejected = $rejectedStatus !== null;
            }
            
            // Check if document status is allowed (case-insensitive)
            $statusLower = strtolower($dokumen->status ?? '');
            $allowedStatuses = ['draft', 'returned_to_ibua', 'sedang diproses'];
            $isAllowedStatus = in_array($statusLower, $allowedStatuses);
            
            // Allow sending if:
            // 1. Document is rejected (can always be resent) AND with IbuA
            // 2. OR document has allowed status AND with IbuA
            if (!$isRejected && !$isAllowedStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak dapat dikirim. Status dokumen harus draft, returned, atau sedang diproses.'
                ], 400);
            }

            // Only allow if created by ibuA and current_handler is ibuA (case-insensitive)
            if (!$createdByIbuA || !$currentHandlerIbuA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
                ], 403);
            }

            DB::beginTransaction();

            // Kirim ke inbox Ibu Yuni untuk approval dengan status WAITING_REVIEWER_APPROVAL
            // Method sendToInbox() akan set:
            // - status ke 'waiting_reviewer_approval' untuk IbuB
            // - current_stage ke 'reviewer'
            // - last_action_status ke 'sent_to_ibub'
            $dokumen->sendToInbox('IbuB');

            // Also create record in new dokumen_statuses table
            $dokumen->sendToRoleInbox('ibub', 'ibuA');

            $dokumen->refresh();
            DB::commit();

            // Broadcast event untuk inbox (DocumentSentToInbox sudah di-broadcast di method sendToInbox)
            try {
                \Log::info('Document sent to inbox IbuB with WAITING_REVIEWER_APPROVAL status', [
                    'document_id' => $dokumen->id,
                    'status' => $dokumen->status,
                    'current_stage' => $dokumen->current_stage,
                    'inbox_approval_status' => $dokumen->getStatusForRole('ibub')->status ?? 'unknown',
                ]);
            } catch (\Exception $logException) {
                \Log::error('Failed to log document sent to inbox: ' . $logException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim ke inbox Ibu Yuni dan menunggu persetujuan.'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending document: ' . $e->getMessage(), [
                'document_id' => $dokumen->id ?? null,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen: ' . $e->getMessage()
            ], 500);
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

            // Only IbuB (Reviewer) can approve documents waiting for reviewer approval
            if ($userRole !== 'ibuB' && $userRole !== 'IbuB') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menyetujui dokumen ini.'
                ], 403);
            }

            // Check if document is waiting for reviewer approval
            if (
                $dokumen->status !== 'waiting_reviewer_approval' &&
                !($dokumen->isWaitingApprovalFor('IbuB'))
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
                'Ibu A' => 'ibuA',
                'IbuB' => 'ibuB',
                'Ibu B' => 'ibuB',
                'Ibu Yuni' => 'ibuB',
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
        $baseQuery = Dokumen::where('created_by', 'ibuA');

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
            'nomor_mirror',
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
            $q->where('created_by', 'ibuA');
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
