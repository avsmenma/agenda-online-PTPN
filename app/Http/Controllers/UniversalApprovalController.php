<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UniversalApprovalController extends Controller
{
    /**
     * Menampilkan daftar dokumen yang menunggu approval untuk user yang sedang login
     */
    public function index()
    {
        try {
            $currentUser = auth()->user();
            $userRole = $this->getUserRole($currentUser);

            if (!$userRole || $userRole === 'ibuA') {
                abort(403, 'Unauthorized access');
            }

            // Ambil dokumen yang menunggu approval untuk role user ini
            $waitingDocuments = Dokumen::where('universal_approval_for', $userRole)
                ->where('status', 'menunggu_approved_pengiriman')
                ->orderBy('universal_approval_sent_at', 'desc')
                ->get();

            $data = array(
                "title" => "Daftar Masuk Dokumen",
                "module" => $userRole,
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "waitingDocuments" => $waitingDocuments,
                "userRole" => $userRole,
            );
            return view('universal-approval.index', $data);

        } catch (\Exception $e) {
            Log::error('Error loading universal approval index: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat daftar dokumen yang menunggu persetujuan');
        }
    }

    /**
     * Approve dokumen yang dikirim
     */
    public function approve(Request $request, Dokumen $dokumen)
    {
        try {
            $currentUser = auth()->user();
            $userRole = $this->getUserRole($currentUser);

            if (!$userRole || !$dokumen->isWaitingApprovalFor($userRole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            // Update dokumen status - langsung masuk ke daftar dokumen
            $dokumen->update([
                'status' => 'sedang_diproses', // Status untuk dokumen yang sudah masuk ke daftar
                'universal_approval_for' => $userRole, // Universal approval untuk user role ini
                'current_handler' => $userRole, // Pindah ke handler yang approve
                'updated_at' => now(), // Update timestamp untuk tracking
            ]);

            // Log activity
            Log::info("Document approved and added to daftar dokumen", [
                'document_id' => $dokumen->id,
                'approved_by' => $userRole,
                'nomor_agenda' => $dokumen->nomor_agenda,
                'module' => $userRole
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Dokumen {$dokumen->nomor_agenda} berhasil disetujui dan telah langsung masuk ke daftar dokumen {$userRole}",
                'redirect_url' => $this->getRedirectUrl($userRole)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject dokumen dengan alasan
     */
    public function reject(Request $request, Dokumen $dokumen)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500'
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter'
        ]);

        try {
            $currentUser = auth()->user();
            $userRole = $this->getUserRole($currentUser);

            if (!$userRole || !$dokumen->isWaitingApprovalFor($userRole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            // Update dokumen status
            $dokumen->update([
                'status' => 'rejected_data_tidak_lengkap',
                'universal_approval_responded_at' => now(),
                'universal_approval_responded_by' => $userRole,
                'universal_approval_rejection_reason' => $request->rejection_reason,
                'current_handler' => $dokumen->created_by, // Kembali ke pengirim
            ]);

            // Log activity
            Log::info("Document rejected", [
                'document_id' => $dokumen->id,
                'rejected_by' => $userRole,
                'rejection_reason' => $request->rejection_reason,
                'nomor_agenda' => $dokumen->nomor_agenda
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Dokumen {$dokumen->nomor_agenda} ditolak dan dikembalikan ke pengirim"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dokumen detail untuk AJAX
     */
    public function getDetail(Dokumen $dokumen)
    {
        try {
            $currentUser = auth()->user();
            $userRole = $this->getUserRole($currentUser);

            if (!$userRole || !$dokumen->isWaitingApprovalFor($userRole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'uraian_spp' => $dokumen->uraian_spp,
                    'nilai_rupiah' => $dokumen->formatted_nilai_rupiah,
                    'pengirim' => $dokumen->getSenderDisplayName(),
                    'dikirim_pada' => $dokumen->universal_approval_sent_at ? $dokumen->universal_approval_sent_at->format('d M Y H:i') : '-',
                    'bagian' => $dokumen->bagian,
                    'kategori' => $dokumen->kategori,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'tanggal_masuk' => $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d M Y') : '-',
                    'status' => $dokumen->getUniversalApprovalStatusDisplay(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting document detail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail dokumen'
            ], 500);
        }
    }

    /**
     * Helper untuk mendapatkan role user
     */
    private function getUserRole($user)
    {
        // Handle case when user is null
        if (!$user) {
            return null;
        }

        // Coba dengan Spatie/Laravel-permission jika ada
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            return $roles->first() ?? null;
        }

        // Coba dengan field role langsung
        if (isset($user->role)) {
            return $user->role;
        }

        // Coba dengan field name (some systems use name field for role)
        if (isset($user->name)) {
            // Map common names to roles
            $nameToRole = [
                'Ibu A' => 'ibuA',
                'IbuB' => 'ibuB',
                'Ibu B' => 'ibuB',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
                'Pembayaran' => 'pembayaran'
            ];

            return $nameToRole[$user->name] ?? null;
        }

        // Default fallback
        return null;
    }

    /**
     * Check untuk notification badge (AJAX endpoint)
     * Note: deprecated since documents are sent directly without approval
     */
    public function checkNotifications()
    {
        // No more waiting approvals since documents are sent directly
        return response()->json([
            'count' => 0,
            'documents' => []
        ]);
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($userRole)
    {
        $redirectUrls = [
            'ibuB' => '/dokumensb',
            'perpajakan' => '/dokumensPerpajakan',
            'akutansi' => '/akutansi',
            'pembayaran' => '/daftarPembayaran',
        ];

        return $redirectUrls[$userRole] ?? '/dashboard';
    }
}
