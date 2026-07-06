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
     * List documents for current bagian
     */
    public function index(Request $request)
    {
        $bagianCode = $this->getBagianCode();
        $bagianName = $this->getBagianName();

        if (!$bagianCode) {
            abort(403, 'Bagian code not configured for this user');
        }

        // View-only monitoring: tampilkan semua dokumen milik bagian ini (kolom `bagian`)
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
            ->where('bagian', $bagianCode)
            // Urut TERBARU → TERLAMA berdasarkan angka nomor agenda (bagian sebelum "_",
            // mis. "3075_2026" → 3075). REGEXP lama gagal karena ada sufiks "_2026".
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_agenda, "_", 1) AS UNSIGNED) DESC')
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
        ];

        // Guard: apa pun kolom yang diminta, batasi hanya ke daftar yang diizinkan
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($availableColumns)));

        // If columns are provided in request, save to session
        if ($request->has('columns') && !empty($selectedColumns)) {
            session(['bagian_dokumens_table_columns_v5' => $selectedColumns]);
        } else {
            // Load from session or use default
            $selectedColumns = session('bagian_dokumens_table_columns_v5', $defaultColumns);

            // If empty after filtering, use default
            if (empty($selectedColumns)) {
                $selectedColumns = $defaultColumns;
            }

            // Update session to keep it in sync
            session(['bagian_dokumens_table_columns_v5' => $selectedColumns]);
        }

        // Filter akhir: buang kolom terlarang dari session lama.
        $selectedColumns = array_values(array_intersect($selectedColumns, array_keys($availableColumns)));
        if (empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }

        // Kartu informasi — total keseluruhan milik bagian ini (tidak terpengaruh filter).
        $totalDokumen = Dokumen::where('bagian', $bagianCode)->count();
        $totalSudahDibayar = Dokumen::where('bagian', $bagianCode)
            ->where(function ($q) {
                $q->whereNotNull('tanggal_dibayar')
                    ->orWhere('status_pembayaran', 'sudah_dibayar');
            })
            ->count();
        $totalBelumDibayar = $totalDokumen - $totalSudahDibayar;

        return view('bagian.dokumens.daftarDokumen', compact(
            'dokumens',
            'bagianCode',
            'bagianName',
            'availableColumns',
            'selectedColumns',
            'totalDokumen',
            'totalBelumDibayar',
            'totalSudahDibayar'
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






