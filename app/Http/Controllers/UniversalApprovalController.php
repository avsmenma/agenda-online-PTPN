<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Support\Facades\Log;

class UniversalApprovalController extends Controller
{
    

    

    

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
                    'dikirim_pada' => $dokumen->inbox_approval_sent_at ? $dokumen->inbox_approval_sent_at->format('d M Y H:i') : '-',
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
                'Operator' => 'operator',
                'team_verifikasi' => 'team_verifikasi',
                'Ibu B' => 'team_verifikasi',
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

    
}






