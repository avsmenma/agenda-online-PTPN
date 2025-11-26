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
            $checkFrom = $lastCheckTime ? \Carbon\Carbon::parse($lastCheckTime) : now()->subHours(24);

            // Cari dokumen yang di-reject dari inbox setelah last check
            $rejectedDocuments = Dokumen::where('created_by', 'ibuA')
                ->where('current_handler', 'ibuA')
                ->where('status', 'returned_to_ibua')
                ->where('inbox_approval_status', 'rejected')
                ->where('inbox_approval_responded_at', '>', $checkFrom)
                ->with('activityLogs')
                ->orderBy('inbox_approval_responded_at', 'desc')
                ->select(['id', 'nomor_agenda', 'nomor_spp', 'uraian_spp', 'nilai_rupiah', 'inbox_approval_responded_at', 'inbox_approval_reason', 'inbox_approval_for'])
                ->get();

            // Hitung total rejected
            $totalRejected = Dokumen::where('created_by', 'ibuA')
                ->where('current_handler', 'ibuA')
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
                        'url' => route('dokumens.index') . '#doc-' . $doc->id,
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
}
