<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class DashboardController extends Controller
{
    public function index(){
        // Redirect Admin/Owner to owner dashboard
        $user = auth()->user();
        if ($user && (strtolower($user->role) === 'admin' || strtolower($user->role) === 'owner')) {
            return redirect('/owner/dashboard');
        }

        // Get statistics for IbuA (only documents created by ibuA)
        $totalDokumen = Dokumen::where('created_by', 'ibuA')->count();

        // Total dokumen belum dikirim = dokumen yang masih draft atau belum dikirim ke ibuB
        $totalBelumDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereNull('sent_to_ibub_at')
            ->where('status', '!=', 'returned_to_ibua')
            ->count();

        // Total dokumen sudah dikirim = dokumen yang sudah dikirim ke ibuB (sent_to_ibub_at tidak null)
        $totalSudahDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('sent_to_ibub_at')
            ->where('status', '!=', 'returned_to_ibua')
            ->count();

        // Total dokumen yang di-reject dari inbox dan dikembalikan ke IbuA
        $totalDitolakInbox = Dokumen::where('created_by', 'ibuA')
            ->where('current_handler', 'ibuA')
            ->where('status', 'returned_to_ibua')
            ->where('inbox_approval_status', 'rejected')
            ->count();

        // Get latest documents (5 most recent) created by ibuA
        $dokumenTerbaru = Dokumen::where('created_by', 'ibuA')
            ->with(['dibayarKepadas'])
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard",
            "module" => "IbuA",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'menuRekapan' => '',
            'totalDokumen' => $totalDokumen,
            'totalBelumDikirim' => $totalBelumDikirim,
            'totalSudahDikirim' => $totalSudahDikirim,
            'totalDitolakInbox' => $totalDitolakInbox,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('IbuA.dashboard',$data);
    }

    /**
     * API endpoint untuk check dokumen yang di-reject dari inbox untuk IbuA
     */
    public function checkRejectedDocuments(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Hanya allow IbuA
            if (!$user || strtolower($user->role) !== 'ibua') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get last check time from request (dari localStorage client)
            $lastCheckTime = $request->input('last_check_time');
            
            // Cari dokumen yang di-reject dalam 24 jam terakhir (untuk memastikan notifikasi selalu muncul)
            // Jika ada lastCheckTime, gunakan yang lebih lama antara lastCheckTime atau 24 jam yang lalu
            // Ini memastikan bahwa dokumen yang di-reject dalam 24 jam terakhir selalu ditampilkan
            $checkFrom24Hours = now()->subHours(24);
            $checkFrom = $lastCheckTime ? \Carbon\Carbon::parse($lastCheckTime) : $checkFrom24Hours;
            
            // Gunakan waktu yang lebih lama untuk memastikan tidak ada yang terlewat
            if ($checkFrom->gt($checkFrom24Hours)) {
                $checkFrom = $checkFrom24Hours;
            }

            \Log::info('IbuA checkRejectedDocuments called', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'last_check_time' => $lastCheckTime,
                'check_from' => $checkFrom->toIso8601String(),
                'check_from_24h' => $checkFrom24Hours->toIso8601String(),
            ]);

            // Cari dokumen yang di-reject dari inbox dalam 24 jam terakhir
            // FIX: Gunakan AND condition yang ketat untuk mencegah cross-interference
            // Hanya dokumen yang BENAR-BENAR dibuat oleh IbuA yang akan ditampilkan
            $rejectedDocuments = Dokumen::where(function($query) {
                    // Hanya dokumen yang dibuat oleh IbuA
                    $query->whereRaw('LOWER(created_by) IN (?, ?)', ['ibua', 'ibu a'])
                          ->orWhere('created_by', 'ibuA')
                          ->orWhere('created_by', 'IbuA');
                })
                ->where(function($query) {
                    // DAN status returned ke IbuA
                    $query->where('status', 'returned_to_ibua')
                          ->where('inbox_approval_status', 'rejected');
                })
                ->where('inbox_approval_responded_at', '>=', $checkFrom)
                ->whereNotNull('inbox_approval_responded_at')
                ->with('activityLogs')
                ->orderBy('inbox_approval_responded_at', 'desc')
                ->select(['id', 'nomor_agenda', 'nomor_spp', 'uraian_spp', 'nilai_rupiah', 'inbox_approval_responded_at', 'inbox_approval_reason', 'inbox_approval_for'])
                ->get();

            \Log::info('IbuA rejected documents found', [
                'count' => $rejectedDocuments->count(),
                'document_ids' => $rejectedDocuments->pluck('id')->toArray(),
            ]);

            // Hitung total rejected (case-insensitive)
            $totalRejected = Dokumen::where(function($query) {
                    $query->whereRaw('LOWER(created_by) = ?', ['ibua'])
                          ->orWhere('created_by', 'ibuA')
                          ->orWhere('created_by', 'IbuA');
                })
                ->where(function($query) {
                    $query->whereRaw('LOWER(current_handler) = ?', ['ibua'])
                          ->orWhere('current_handler', 'ibuA')
                          ->orWhere('current_handler', 'IbuA');
                })
                ->where('status', 'returned_to_ibua')
                ->where('inbox_approval_status', 'rejected')
                ->count();

            return response()->json([
                'success' => true,
                'rejected_documents_count' => $rejectedDocuments->count(),
                'total_rejected' => $totalRejected,
                'rejected_documents' => $rejectedDocuments->map(function($doc) {
                    // Get rejected by name from activity log
                    $rejectLog = $doc->activityLogs()
                        ->where('action', 'inbox_rejected')
                        ->latest('action_at')
                        ->first();
                    
                    $rejectedBy = 'Unknown';
                    if ($rejectLog) {
                        $rejectedBy = $rejectLog->performed_by ?? $rejectLog->details['rejected_by'] ?? 'Unknown';
                        // Map role to display name
                        $nameMap = [
                            'IbuB' => 'Ibu Yuni',
                            'ibuB' => 'Ibu Yuni',
                            'Perpajakan' => 'Team Perpajakan',
                            'perpajakan' => 'Team Perpajakan',
                            'Akutansi' => 'Team Akutansi',
                            'akutansi' => 'Team Akutansi',
                        ];
                        $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;
                    } else if ($doc->inbox_approval_for) {
                        $nameMap = [
                            'IbuB' => 'Ibu Yuni',
                            'Perpajakan' => 'Team Perpajakan',
                            'Akutansi' => 'Team Akutansi',
                        ];
                        $rejectedBy = $nameMap[$doc->inbox_approval_for] ?? $doc->inbox_approval_for;
                    }

                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => \Illuminate\Support\Str::limit($doc->uraian_spp ?? '-', 50),
                        'nilai_rupiah' => $doc->formatted_nilai_rupiah ?? 'Rp 0',
                        'rejected_at' => $doc->inbox_approval_responded_at->format('d/m/Y H:i'),
                        'rejected_by' => $rejectedBy,
                        'rejection_reason' => \Illuminate\Support\Str::limit($doc->inbox_approval_reason ?? '-', 100),
                        'url' => route('ibua.rejected.show', $doc->id),
                    ];
                }),
                'current_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking rejected documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa dokumen yang ditolak'
            ], 500);
        }
    }

    /**
     * Menampilkan detail dokumen yang di-reject dari inbox untuk IbuA
     */
    public function showRejectedDocument(Dokumen $dokumen)
    {
        try {
            $user = auth()->user();
            
            // Hanya allow IbuA
            if (!$user || strtolower($user->role) !== 'ibua') {
                abort(403, 'Unauthorized access');
            }

            // Validasi: dokumen harus di-reject dan dikembalikan ke IbuA
            if ($dokumen->status !== 'returned_to_ibua' || 
                $dokumen->inbox_approval_status !== 'rejected' ||
                strtolower($dokumen->created_by) !== 'ibua' ||
                strtolower($dokumen->current_handler) !== 'ibua') {
                abort(404, 'Dokumen tidak ditemukan atau tidak valid');
            }

            // Get rejected by name from activity log
            $rejectLog = $dokumen->activityLogs()
                ->where('action', 'inbox_rejected')
                ->latest('action_at')
                ->first();
            
            $rejectedBy = 'Unknown';
            if ($rejectLog) {
                $rejectedBy = $rejectLog->performed_by ?? $rejectLog->details['rejected_by'] ?? 'Unknown';
                // Map role to display name
                $nameMap = [
                    'IbuB' => 'Ibu Yuni',
                    'ibuB' => 'Ibu Yuni',
                    'Perpajakan' => 'Team Perpajakan',
                    'perpajakan' => 'Team Perpajakan',
                    'Akutansi' => 'Team Akutansi',
                    'akutansi' => 'Team Akutansi',
                ];
                $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;
            } else if ($dokumen->inbox_approval_for) {
                $nameMap = [
                    'IbuB' => 'Ibu Yuni',
                    'Perpajakan' => 'Team Perpajakan',
                    'Akutansi' => 'Team Akutansi',
                ];
                $rejectedBy = $nameMap[$dokumen->inbox_approval_for] ?? $dokumen->inbox_approval_for;
            }

            $data = [
                "title" => "Detail Dokumen Ditolak",
                "module" => "IbuA",
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "dokumen" => $dokumen,
                "rejectedBy" => $rejectedBy,
                "rejectionReason" => $dokumen->inbox_approval_reason,
                "rejectedAt" => $dokumen->inbox_approval_responded_at,
            ];

            return view('IbuA.rejected-detail', $data);

        } catch (\Exception $e) {
            \Log::error('Error showing rejected document: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail dokumen yang ditolak');
        }
    }
}
