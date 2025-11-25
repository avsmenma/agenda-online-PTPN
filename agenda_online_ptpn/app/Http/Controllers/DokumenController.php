<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDokumenRequest;
use App\Http\Requests\UpdateDokumenRequest;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class DokumenController extends Controller
{
    public function index(Request $request)
    {
        // IbuA only sees documents created by ibua
        $query = Dokumen::with(['dokumenPos', 'dokumenPrs', 'dibayarKepadas'])
            ->where('created_by', 'ibua')
            ->latest('tanggal_masuk');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_agenda', 'like', '%' . $search . '%')
                  ->orWhere('nomor_spp', 'like', '%' . $search . '%')
                  ->orWhere('uraian_spp', 'like', '%' . $search . '%');
            });
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('tahun', $request->year);
        }

        $dokumens = $query->paginate(10);

        $data = array(
            "title" => "Daftar Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
        );

        return view('IbuA.dokumens.daftarDokumen', $data);
    }

    public function create()
    {
        $data = array(
            "title" => "Tambah Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "",
            "menuTambahDokumen" => "active",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
        );
        return view('IbuA.dokumens.tambahDokumen', $data);
    }

    /**
     * Get document detail for AJAX request for IbuA
     */
    public function getDocumentDetailForIbuA(Dokumen $dokumen)
    {
        // Only allow if created by ibua
        if ($dokumen->created_by !== 'ibua') {
            return response('<div class="text-center p-4 text-danger">Access denied</div>', 403);
        }

        // Load relationships
        $dokumen->load(['dokumenPos', 'dokumenPrs', 'dibayarKepadas']);

        // Return HTML partial for detail view
        $html = view('IbuA.dokumens.partials.document_detail', compact('dokumen'))->render();

        return response($html);
    }

    /**
     * Get document progress for IbuA
     */
    public function getDocumentProgressForIbuA(Dokumen $dokumen)
    {
        // Only allow if created by ibua
        if ($dokumen->created_by !== 'ibua') {
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
            'description' => 'Dokumen berhasil dibuat oleh IbuA',
            'percentage' => 20
        ];

        // Step 2: Document Sent to IbuB
        if ($dokumen->status === 'draft') {
            $timeline[] = [
                'step' => 'Menunggu Pengiriman',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang disiapkan untuk dikirim ke IbuB',
                'percentage' => 0
            ];
            $totalPercentage = 20;
        } elseif ($dokumen->status === 'sent_to_ibub') {
            $timeline[] = [
                'step' => 'Terkirim ke IbuB',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke IbuB untuk diproses',
                'percentage' => 30
            ];

            // Step 3: Processing by IbuB
            $timeline[] = [
                'step' => 'Sedang Diproses IbuB',
                'status' => 'current',
                'time' => '',
                'description' => 'Dokumen sedang ditinjau dan diproses oleh IbuB',
                'percentage' => 0
            ];
            $totalPercentage = 50;
        } elseif ($dokumen->status === 'returned_to_ibua') {
            $timeline[] = [
                'step' => 'Terkirim ke IbuB',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke IbuB untuk diproses',
                'percentage' => 30
            ];

            $timeline[] = [
                'step' => 'Dikembalikan ke IbuA',
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
                'description' => 'Dokumen perlu diperbaiki sesuai masukan dari IbuB',
                'percentage' => 0
            ];
            $totalPercentage = 60;
        } elseif ($dokumen->status === 'sedang diproses') {
            $timeline[] = [
                'step' => 'Terkirim ke IbuB',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke IbuB untuk diproses',
                'percentage' => 30
            ];

            // Step 3: Processing by IbuB
            $timeline[] = [
                'step' => 'Sedang Diproses IbuB',
                'status' => 'completed',
                'time' => $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah selesai diproses oleh IbuB',
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
                'step' => 'Terkirim ke IbuB',
                'status' => 'completed',
                'time' => $dokumen->sent_to_ibub_at ? $dokumen->sent_to_ibub_at->format('d M Y H:i') : '',
                'description' => 'Dokumen telah dikirim ke IbuB untuk diproses',
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
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'May', 6 => 'Juni', 7 => 'July', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

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
                'kategori' => $request->kategori,
                'jenis_dokumen' => $request->jenis_dokumen,
                'jenis_sub_pekerjaan' => $request->jenis_sub_pekerjaan,
                'jenis_pembayaran' => $request->jenis_pembayaran,
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
                'created_by' => 'ibua',
                'current_handler' => 'ibua',
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

            return redirect()->route('dokumens.index')
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

        $data = array(
            "title" => "Edit Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumen" => $dokumen,
        );

        return view('IbuA.dokumens.editDokumen', $data);
    }

    public function update(UpdateDokumenRequest $request, Dokumen $dokumen)
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

            // Extract bulan dan tahun dari tanggal SPP untuk update
            $tanggalSpp = Carbon::parse($request->tanggal_spp);
            $bulanIndonesia = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'May', 6 => 'Juni', 7 => 'July', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            // Update dokumen
            // IMPORTANT: Status is NOT updated here - it only changes via workflow (send, return, etc)
            $dokumen->update([
                'nomor_agenda' => $request->nomor_agenda,
                'bulan' => $bulanIndonesia[$tanggalSpp->month],
                'tahun' => $tanggalSpp->year,
                'tanggal_masuk' => $dokumen->tanggal_masuk, // Keep original creation timestamp
                'nomor_spp' => $request->nomor_spp,
                'tanggal_spp' => $request->tanggal_spp,
                'uraian_spp' => $request->uraian_spp,
                'nilai_rupiah' => $nilaiRupiah,
                'kategori' => $request->kategori,
                'jenis_dokumen' => $request->jenis_dokumen,
                'jenis_sub_pekerjaan' => $request->jenis_sub_pekerjaan,
                'jenis_pembayaran' => $request->jenis_pembayaran,
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

            return redirect()->route('dokumens.index')
                ->with('success', 'Dokumen berhasil diperbarui.');

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Error updating dokumen: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui dokumen. Silakan coba lagi.');
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

            return redirect()->route('dokumens.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (Exception $e) {
            DB::rollback();

            \Log::error('Error deleting dokumen: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus dokumen. Silakan coba lagi.');
        }
    }

    /**
     * Show confirmation page before sending to IbuB
     */
    public function confirmSendToIbuB(Dokumen $dokumen)
    {
        $data = array(
            "title" => "Konfirmasi Pengiriman Dokumen",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumen" => "active",
            "menuTambahDokumen" => "",
            "menuDaftarDokumenDikembalikan" => "",
            "menuDashboard" => "",
            "dokumen" => $dokumen,
        );

        return view('IbuA.dokumens.confirm-send', $data);
    }

    /**
     * Send document to IbuB
     */
    public function sendToIbuB(Dokumen $dokumen)
    {
        try {
            // Handle old data that might not have workflow fields
            $currentHandler = $dokumen->current_handler ?? 'ibua';
            $createdBy = $dokumen->created_by ?? 'ibua';

            // Only allow sending if document is in draft, returned, or sedang diproses status
            // and current_handler is ibua
            if (!in_array($dokumen->status, ['draft', 'returned_to_ibua', 'sedang diproses'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak dapat dikirim. Status dokumen harus draft, returned, atau sedang diproses.'
                ], 400);
            }

            // Only allow if created by ibua and current_handler is ibua
            if ($createdBy !== 'ibua' || $currentHandler !== 'ibua') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengirim dokumen ini.'
                ], 403);
            }

            DB::beginTransaction();

            // Gunakan Universal Approval System
            $dokumen->update([
                'status' => 'pending_approval_ibub',           // Status pending approval
                'universal_approval_for' => 'ibub',            // Target penerima (lowercase for consistency)
                'universal_approval_sent_at' => now(),          // Timestamp pengiriman universal
                'current_handler' => 'ibua',                   // Tetap di pengirim sampai di-approve (lowercase)
            ]);

            $dokumen->refresh();
            DB::commit();

            // Broadcast event to IbuB (after commit to ensure data is saved)
            try {
                broadcast(new \App\Events\DocumentSent($dokumen, 'ibuA', 'ibuB'));
                \Log::info('DocumentSent event broadcasted', [
                    'document_id' => $dokumen->id,
                    'sent_to' => 'ibuB',
                    'status' => $dokumen->status
                ]);
            } catch (\Exception $broadcastException) {
                \Log::error('Failed to broadcast DocumentSent event: ' . $broadcastException->getMessage(), [
                    'document_id' => $dokumen->id,
                    'error' => $broadcastException->getTraceAsString()
                ]);
                // Don't fail the request if broadcast fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dikirim ke IbuB. Menunggu persetujuan pengiriman.'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error sending document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim dokumen.'
            ], 500);
        }
    }
}
