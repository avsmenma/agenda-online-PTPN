<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboxController extends Controller
{
    /**
     * Menampilkan daftar dokumen yang menunggu approval di inbox
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Hanya allow IbuB, Perpajakan, Akutansi
            if (!$userRole || !in_array($userRole, ['IbuB', 'Perpajakan', 'Akutansi'])) {
                abort(403, 'Unauthorized access - Halaman ini hanya untuk IbuB, Perpajakan, dan Akutansi');
            }

            // Ambil dokumen yang menunggu approval di inbox untuk role user ini
            // Debug logging
            \Log::info('InboxController::index - Fetching documents', [
                'user_id' => $user->id ?? null,
                'user_role_raw' => $user->role ?? null,
                'user_role_mapped' => $userRole,
                'query_conditions' => [
                    'inbox_approval_for' => $userRole,
                    'inbox_approval_status' => 'pending',
                ],
            ]);
            
            $documents = Dokumen::with('activityLogs')
                ->where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'pending')
                ->latest('inbox_approval_sent_at')
                ->paginate(10);
            
            \Log::info('InboxController::index - Documents found', [
                'user_role' => $userRole,
                'documents_count' => $documents->count(),
                'document_ids' => $documents->pluck('id')->toArray(),
            ]);

            // Hitung statistik
            $pendingCount = Dokumen::where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'pending')
                ->count();

            $approvedToday = Dokumen::where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'approved')
                ->whereDate('inbox_approval_responded_at', today())
                ->count();

            $totalProcessed = Dokumen::where('inbox_approval_for', $userRole)
                ->whereIn('inbox_approval_status', ['approved', 'rejected'])
                ->count();

            // Normalize module untuk layout (harus lowercase untuk match statement)
            $moduleMap = [
                'IbuB' => 'ibub',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
            ];
            $normalizedModule = $moduleMap[$userRole] ?? strtolower($userRole);

            // Hitung dokumen baru (masuk dalam 24 jam terakhir)
            $newDocumentsCount = Dokumen::where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'pending')
                ->where('inbox_approval_sent_at', '>=', now()->subHours(24))
                ->count();

            $data = [
                "title" => "Inbox Dokumen",
                "module" => $normalizedModule,
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "documents" => $documents,
                "userRole" => $userRole,
                "pendingCount" => $pendingCount,
                "approvedToday" => $approvedToday,
                "totalProcessed" => $totalProcessed,
                "newDocumentsCount" => $newDocumentsCount,
            ];

            return view('inbox.index', $data);

        } catch (\Exception $e) {
            Log::error('Error loading inbox index: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat daftar dokumen inbox');
        }
    }

    /**
     * Menampilkan detail dokumen di inbox
     */
    public function show(Dokumen $dokumen)
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Validate user has access to this inbox
            if ($dokumen->inbox_approval_for !== $userRole) {
                abort(403, 'Unauthorized access');
            }

            if ($dokumen->inbox_approval_status !== 'pending') {
                return redirect()->route('inbox.index')
                    ->with('error', 'Dokumen ini sudah diproses');
            }

            // Normalize module untuk layout (harus lowercase untuk match statement)
            $moduleMap = [
                'IbuB' => 'ibub',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
            ];
            $normalizedModule = $moduleMap[$userRole] ?? strtolower($userRole);

            // Load activity logs untuk getSenderDisplayName
            $dokumen->load('activityLogs');
            
            $data = [
                "title" => "Detail Dokumen - Inbox",
                "module" => $normalizedModule,
                "menuDokumen" => "",
                "menuDaftarDokumen" => "",
                "menuDashboard" => "",
                "dokumen" => $dokumen,
                "userRole" => $userRole,
            ];

            return view('inbox.show', $data);

        } catch (\Exception $e) {
            Log::error('Error loading inbox show: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail dokumen');
        }
    }

    /**
     * Approve dokumen dari inbox
     */
    public function approve(Request $request, Dokumen $dokumen)
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Validate user has access
            if ($dokumen->inbox_approval_for !== $userRole || $dokumen->inbox_approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access atau dokumen sudah diproses'
                ], 403);
            }

            $dokumen->approveInbox();

            return redirect()->route('inbox.index')
                ->with('success', 'Dokumen berhasil disetujui dan masuk ke daftar dokumen resmi.');

        } catch (\Exception $e) {
            Log::error('Error approving document from inbox: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Reject dokumen dari inbox
     */
    public function reject(Request $request, Dokumen $dokumen)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ], [
            'reason.required' => 'Alasan penolakan harus diisi',
            'reason.max' => 'Alasan penolakan maksimal 500 karakter'
        ]);

        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Validate user has access
            if ($dokumen->inbox_approval_for !== $userRole || $dokumen->inbox_approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access atau dokumen sudah diproses'
                ], 403);
            }

            $dokumen->rejectInbox($request->reason);

            return redirect()->route('inbox.index')
                ->with('success', 'Dokumen ditolak dan dikembalikan ke pengirim dengan alasan: ' . $request->reason);

        } catch (\Exception $e) {
            Log::error('Error rejecting document from inbox: ' . $e->getMessage());
            return back()->with('error', 'Gagal menolak dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk mendapatkan role user
     */
    private function getUserRole($user)
    {
        if (!$user) {
            \Log::warning('getUserRole: User is null');
            return null;
        }

        // Prioritize role field over name field
        if (isset($user->role)) {
            $role = $user->role;
            // Map role ke format yang sesuai untuk inbox (must match enum: IbuB, Perpajakan, Akutansi)
            $roleMap = [
                'ibuB' => 'IbuB',
                'IbuB' => 'IbuB',
                'Ibu B' => 'IbuB',
                'ibu B' => 'IbuB',
                'Ibu Yuni' => 'IbuB',
                'ibu yuni' => 'IbuB',
                'perpajakan' => 'Perpajakan',
                'Perpajakan' => 'Perpajakan',
                'akutansi' => 'Akutansi',
                'Akutansi' => 'Akutansi',
            ];
            $mappedRole = $roleMap[$role] ?? $role;
            
            \Log::info('getUserRole: Mapped from role field', [
                'user_id' => $user->id,
                'original_role' => $role,
                'mapped_role' => $mappedRole,
            ]);
            
            return $mappedRole;
        }

        // Fallback ke field name
        if (isset($user->name)) {
            $name = $user->name;
            $nameToRole = [
                'Ibu A' => 'ibuA',
                'IbuA' => 'ibuA',
                'ibuA' => 'ibuA',
                'Ibu Tarapul' => 'ibuA',
                'IbuB' => 'IbuB',
                'Ibu B' => 'IbuB',
                'ibuB' => 'IbuB',
                'ibu B' => 'IbuB',
                'Ibu Yuni' => 'IbuB',
                'ibu yuni' => 'IbuB',
                'Perpajakan' => 'Perpajakan',
                'perpajakan' => 'Perpajakan',
                'Akutansi' => 'Akutansi',
                'akutansi' => 'Akutansi',
                'Pembayaran' => 'pembayaran'
            ];
            $mappedRole = $nameToRole[$name] ?? null;
            
            \Log::info('getUserRole: Mapped from name field', [
                'user_id' => $user->id,
                'user_name' => $name,
                'mapped_role' => $mappedRole,
            ]);
            
            return $mappedRole;
        }

        \Log::warning('getUserRole: No role or name field found', [
            'user_id' => $user->id ?? null,
        ]);
        
        return null;
    }

    /**
     * API endpoint untuk check dokumen baru di inbox
     */
    public function checkNewDocuments(Request $request)
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Debug logging
            Log::info('checkNewDocuments called', [
                'user_id' => $user->id ?? null,
                'user_role_raw' => $user->role ?? null,
                'user_role_mapped' => $userRole,
                'allowed_roles' => ['IbuB', 'Perpajakan', 'Akutansi']
            ]);

            if (!$userRole || !in_array($userRole, ['IbuB', 'Perpajakan', 'Akutansi'])) {
                Log::warning('Unauthorized access to checkNewDocuments', [
                    'user_role' => $userRole,
                    'user_role_raw' => $user->role ?? null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get last check time from request (dari localStorage client)
            $lastCheckTime = $request->input('last_check_time');
            $checkFrom = $lastCheckTime ? \Carbon\Carbon::parse($lastCheckTime) : now()->subHours(24);

            // Cari dokumen baru yang masuk setelah last check
            $newDocuments = Dokumen::where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'pending')
                ->where('inbox_approval_sent_at', '>', $checkFrom)
                ->orderBy('inbox_approval_sent_at', 'desc')
                ->select(['id', 'nomor_agenda', 'nomor_spp', 'uraian_spp', 'nilai_rupiah', 'inbox_approval_sent_at'])
                ->get();

            // Hitung total pending
            $pendingCount = Dokumen::where('inbox_approval_for', $userRole)
                ->where('inbox_approval_status', 'pending')
                ->count();

            Log::info('checkNewDocuments result', [
                'user_role' => $userRole,
                'new_documents_count' => $newDocuments->count(),
                'pending_count' => $pendingCount,
                'check_from' => $checkFrom->toIso8601String()
            ]);

            return response()->json([
                'success' => true,
                'new_documents_count' => $newDocuments->count(),
                'pending_count' => $pendingCount,
                'new_documents' => $newDocuments->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'nomor_agenda' => $doc->nomor_agenda,
                        'nomor_spp' => $doc->nomor_spp,
                        'uraian_spp' => \Illuminate\Support\Str::limit($doc->uraian_spp ?? '-', 50),
                        'nilai_rupiah' => $doc->formatted_nilai_rupiah ?? 'Rp 0',
                        'sent_at' => $doc->inbox_approval_sent_at->format('d/m/Y H:i'),
                        'url' => route('inbox.show', $doc->id),
                    ];
                }),
                'current_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking new documents: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa dokumen baru'
            ], 500);
        }
    }
}
