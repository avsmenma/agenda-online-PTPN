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
use App\Models\DokumenStatus;
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
     * List documents for current bagian
     */
    public function index(Request $request)
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // View-only monitoring: query dasar dokumen milik bagian ini
        $baseQuery = Dokumen::where('bagian', $bagianCode);

        // Search functionality
        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_spp', 'like', "%{$search}%")
                    ->orWhere('uraian_spp', 'like', "%{$search}%");
            });
        }

        // Month filter
        if ($request->filled('bulan')) {
            $baseQuery->where('bulan', $request->bulan);
        }
    
        // Year filter
        if ($request->filled('tahun')) {
            $baseQuery->where('tahun', $request->tahun);
        }

        // Vendor filter — kolom `dibayar_kepada` (nama penerima pembayaran).
        // Dicocokkan PERSIS, bukan LIKE: nilainya diambil dari daftar dropdown
        // yang dibangun dari kolom yang sama, jadi selalu cocok utuh. LIKE akan
        // membuat "PT Sumber" ikut menjaring "PT Sumber Makmur Jaya".
        if ($request->filled('vendor')) {
            $baseQuery->where('dibayar_kepada', $request->vendor);
        }

        // Item Sub Kriteria — disimpan di kolom `jenis_sub_pekerjaan`
        // (lihat DokumenController: ItemSubKriteria dipetakan ke kolom ini).
        if ($request->filled('sub_kriteria')) {
            $baseQuery->where('jenis_sub_pekerjaan', $request->sub_kriteria);
        }

        // Kartu informasi — dihitung dinamis mengikuti filter aktif (search, tahun, bulan, vendor, sub_kriteria).
        $totalDokumen = (clone $baseQuery)->count();
        $totalSudahDibayar = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereNotNull('tanggal_dibayar')
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            })
            ->count();
        $totalBelumDibayar = $totalDokumen - $totalSudahDibayar;

        // Query tabel: terapkan sorting, eager load, dan filter status bila dipilih
        $query = (clone $baseQuery)->with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas', 'roleData', 'roleStatuses'])
            // Urut TERBARU → TERLAMA berdasarkan angka nomor agenda (bagian sebelum "_",
            // mis. "3075_2026" → 3075). REGEXP lama gagal karena ada sufiks "_2026".
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_agenda, "_", 1) AS UNSIGNED) DESC')
            ->orderBy('created_at', 'desc');

        // Status filter with expanded options
        if ($request->filled('status')) {
            $statusFilter = $request->status;

            // Hanya 2 filter status (selaras dgn kartu info): sudah / belum dibayar.
            // "Sudah dibayar" = ada tanggal_dibayar ATAU status_pembayaran final.
            if ($statusFilter === 'sudah_dibayar') {
                $query->where(function ($q) {
                    $q->whereNotNull('tanggal_dibayar')
                        ->orWhere('status_pembayaran', 'sudah_dibayar');
                });
            } elseif ($statusFilter === 'belum_dibayar') {
                // Kebalikannya: belum ada tanggal bayar & belum berstatus final.
                $query->whereNull('tanggal_dibayar')
                    ->where(function ($q) {
                        $q->whereNull('status_pembayaran')
                            ->orWhere('status_pembayaran', '!=', 'sudah_dibayar');
                    });
            }
            // Nilai status lain (peninggalan lama) diabaikan.
        }

        // Hitung total nilai dari SEMUA hasil filter aktif saat ini (sebelum paginasi)
        $totalNilaiDokumen = (clone $query)->sum('nilai_rupiah');
        $totalNilaiFiltered = $totalNilaiDokumen;

        // Deteksi apakah ada filter aktif
        $isFiltered = $request->filled('search')
            || $request->filled('status')
            || $request->filled('tahun')
            || $request->filled('bulan')
            || $request->filled('vendor')
            || $request->filled('sub_kriteria');

        $perPage = $request->get('per_page', session('bagian_per_page', 10));
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;
        }
        session(['bagian_per_page' => $perPage]);
        $dokumens = $query->paginate($perPage)->appends($request->query());

        // Available columns for customization
        // Bagian view-only: HANYA 12 kolom yang boleh dilihat (keputusan pemilik).
        // Kolom tahap-lanjut (paraf, SPK, PO, pajak, status internal, dll) sengaja
        // tidak diekspos ke Bagian.
        $availableColumns = [
            'nomor_spp' => 'Nomor SPP',
            'nomor_agenda' => 'Nomor Agenda',
            'tanggal_spp' => 'Tanggal SPP',
            'tanggal_masuk' => 'Tanggal Masuk',
            'dibayar_kepada' => 'Dibayar Kepada',
            'uraian_spp' => 'Uraian SPP',
            'nilai_rupiah' => 'Nilai Rupiah',
            'umur_dokumen' => 'Waktu Pengerjaan',
            // Kolom sempit khusus pengembalian. SENGAJA bukan kolom 'status' penuh:
            // keputusan pemilik di atas melarang mengekspos status internal ke Bagian,
            // sementara Bagian tetap wajib tahu dokumennya dikembalikan (saran penguji
            // sidang 2026-08-05). Kolom ini hanya berisi info pengembalian, kosong
            // untuk dokumen lain.
            'pengembalian' => 'Pengembalian',
        ];
        // Catatan: "Status Pembayaran" TIDAK di sini — dirender sebagai kolom tetap
        // paling kanan (beku kanan) di view, bukan kolom yang bisa dikustom.

        // Get selected columns from request or session
        $selectedColumns = $request->get('columns', []);

        // Default: susunan kolom pemantauan Bagian (No & Nomor SPP beku kiri;
        // Status Pembayaran, Waktu Pengerjaan, Pengurus Dokumen beku kanan).
        $defaultColumns = [
            'nomor_spp',
            'nomor_agenda',
            'dibayar_kepada',
            'uraian_spp',
            'nilai_rupiah',
            'tanggal_masuk',
            'umur_dokumen',
            'pengembalian',
        ];

        // Guard: apa pun kolom yang diminta, batasi hanya ke daftar yang diizinkan
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($availableColumns)));

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['bagian_dokumens_table_columns_v6' => $selectedColumns]);
        } else {
            // Load from session or use default
            $selectedColumns = session('bagian_dokumens_table_columns_v6', $defaultColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['bagian_dokumens_table_columns_v6' => $selectedColumns]);
        }

        // Filter akhir: buang kolom terlarang dari session lama.
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($availableColumns)));
        if (empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }

        // Pilihan dropdown Vendor & Item Sub Kriteria.
        //
        // Dibatasi ke dokumen milik BAGIAN INI (bukan seluruh database): daftar
        // vendor global memuat ratusan nama dari bagian lain yang tak pernah
        // muncul di halaman ini, sehingga memilihnya hanya menghasilkan tabel
        // kosong. Sengaja TIDAK terpengaruh filter lain yang sedang aktif —
        // kalau ikut menyempit, user tak bisa berpindah vendor setelah memilih
        // satu (opsi lain lenyap dari dropdown-nya sendiri).
        $vendorList = Dokumen::where('bagian', $bagianCode)
            ->whereNotNull('dibayar_kepada')
            ->where('dibayar_kepada', '!=', '')
            ->distinct()
            ->orderBy('dibayar_kepada')
            ->pluck('dibayar_kepada');

        $subKriteriaList = Dokumen::where('bagian', $bagianCode)
            ->whereNotNull('jenis_sub_pekerjaan')
            ->where('jenis_sub_pekerjaan', '!=', '')
            ->distinct()
            ->orderBy('jenis_sub_pekerjaan')
            ->pluck('jenis_sub_pekerjaan');

        // Notifikasi pengembalian yang belum dibaca (in-app). Ditulis oleh
        // App\Services\DocumentReturnNotifier saat dokumen dikembalikan ke bagian ini.
        // Bagian hanya punya SATU halaman, jadi panel di sini sudah setara "lonceng
        // global" bagi mereka — tanpa perlu menyentuh layouts/app.blade.php.
        $notifPengembalian = auth()->user()
            ->unreadNotifications()
            ->where('type', \App\Notifications\DokumenDikembalikanNotification::class)
            ->latest()
            ->take(20)
            ->get();

        // Perjalanan dokumen dalam alur keuangan (kolom Pengurus Dokumen).
        // roleData & roleStatuses sudah ter-eager-load di query atas, jadi tidak ada
        // query per-baris.
        $perjalanan = [];
        foreach ($dokumens as $dokumen) {
            $roleCodeTerlacak = $dokumen->roleData
                ->filter(fn ($rd) => $rd->received_at !== null)
                ->pluck('role_code')
                ->all();

            // Baris dokumen_statuses berstatus pending = "sudah dikirim, belum diterima"
            // tahap tujuan (sendToRoleInbox menulis ini TANPA memajukan current_handler).
            // strtolower() di kedua sisi: data lama bisa beragam kapitalisasi.
            $roleCodeMenunggu = $dokumen->roleStatuses
                ->filter(fn ($rs) => strtolower((string) $rs->status) === strtolower(DokumenStatus::STATUS_PENDING))
                ->pluck('role_code')
                ->all();

            // Definisi kanonik "lunas" — SAMA dengan kartu "Sudah Dibayar" di atas
            // ($totalSudahDibayar). Jangan buat definisi kedua. Kolom sudah ter-eager-load
            // pada $dokumen sendiri (bukan relasi), jadi tidak menambah query per-baris.
            $lunas = $dokumen->tanggal_dibayar !== null || $dokumen->status_pembayaran === 'sudah_dibayar';

            $perjalanan[$dokumen->id] = \App\Support\DocumentJourney::forDokumen(
                $dokumen,
                $roleCodeTerlacak,
                $roleCodeMenunggu,
                $lunas
            );
        }

        return view('bagian.dokumens.daftarDokumen', compact(
            'dokumens',
            'bagianCode',
            'bagianName',
            'availableColumns',
            'selectedColumns',
            'totalDokumen',
            'totalBelumDibayar',
            'totalSudahDibayar',
            'totalNilaiDokumen',
            'notifPengembalian',
            'perjalanan',
            'vendorList',
            'subKriteriaList',
            'totalNilaiFiltered',
            'isFiltered'
        ));
    }

    /**
     * Tandai semua notifikasi pengembalian milik user ini sebagai sudah dibaca.
     * Dipanggil tombol "Tandai sudah dibaca" di panel notifikasi Bagian.
     */
    public function tandaiNotifikasiDibaca()
    {
        auth()->user()
            ->unreadNotifications()
            ->where('type', \App\Notifications\DokumenDikembalikanNotification::class)
            ->update(['read_at' => now()]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
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

    /**
     * Get return/rejection detail for the rejection modal (bagian view)
     * Reads directly from dokumens.return_reason and returned_at
     * because returnToBidang stores data there, not in DokumenStatus
     */
    public function getReturnDetail(Dokumen $dokumen)
    {
        $bagianCode = $this->getBagianCode();

        if (!$bagianCode || $dokumen->bagian !== $bagianCode) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        if ($dokumen->status !== 'returned_to_bidang') {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak dalam status dikembalikan'], 404);
        }

        $returnedBy = 'Team Verifikasi';
        $returnReason = $dokumen->return_reason ?? 'Tidak ada alasan yang diberikan';
        $returnedAt = $dokumen->returned_at
            ? \Carbon\Carbon::parse($dokumen->returned_at)->format('d/m/Y H:i')
            : '-';

        return response()->json([
            'success' => true,
            'dokumen' => [
                'nomor_agenda' => $dokumen->nomor_agenda ?? '-',
                'nomor_spp'    => $dokumen->nomor_spp ?? '-',
                'uraian_spp'   => $dokumen->uraian_spp ?? '-',
                'nilai_rupiah' => 'Rp ' . number_format((float) ($dokumen->nilai_rupiah ?? 0), 0, ',', '.'),
            ],
            'rejected_by'      => $returnedBy,
            'rejection_reason' => $returnReason,
            'rejected_at'      => $returnedAt,
        ]);
    }
}






