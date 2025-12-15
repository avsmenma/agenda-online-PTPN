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
     * Updated to use new dokumen_statuses table
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);

            // Hanya allow IbuB, Perpajakan, Akutansi, Pembayaran
            $allowedRoles = ['IbuB', 'Perpajakan', 'Akutansi', 'Pembayaran'];
            if (!$userRole || !in_array($userRole, $allowedRoles)) {
                abort(403, 'Unauthorized access - Halaman ini hanya untuk IbuB, Perpajakan, Akutansi, dan Pembayaran');
            }

            // Normalize role code for database query (lowercase)
            $roleCode = strtolower($userRole);

            // Query documents using new dokumen_statuses table
            $documents = Dokumen::with(['activityLogs', 'roleStatuses'])
                ->whereHas('roleStatuses', function ($query) use ($roleCode) {
                    $query->where('role_code', $roleCode)
                        ->where('status', \App\Models\DokumenStatus::STATUS_PENDING);
                })
                ->latest('created_at')
                ->paginate(10);

            // Count statistics using new table
            $pendingCount = \App\Models\DokumenStatus::where('role_code', $roleCode)
                ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
                ->count();

            $approvedToday = \App\Models\DokumenStatus::where('role_code', $roleCode)
                ->where('status', \App\Models\DokumenStatus::STATUS_APPROVED)
                ->whereDate('status_changed_at', today())
                ->count();

            $totalProcessed = \App\Models\DokumenStatus::where('role_code', $roleCode)
                ->whereIn('status', [
                    \App\Models\DokumenStatus::STATUS_APPROVED,
                    \App\Models\DokumenStatus::STATUS_REJECTED
                ])
                ->count();

            // Normalize module untuk layout
            $moduleMap = [
                'IbuB' => 'ibub',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
                'Pembayaran' => 'pembayaran',
            ];
            $normalizedModule = $moduleMap[$userRole] ?? strtolower($userRole);

            // Hitung dokumen baru (masuk dalam 24 jam terakhir)
            $newDocumentsCount = \App\Models\DokumenStatus::where('role_code', $roleCode)
                ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
                ->where('status_changed_at', '>=', now()->subHours(24))
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
            // Note: This check logic is a bit complex due to unified model methods
            // We check if there is a pending status for this role
            if (!$dokumen->isPendingForRole(strtolower($userRole))) {
                // Double check: maybe it WAS pending for this user but handled?
                // If so, redirect to index with message
                $status = $dokumen->getStatusForRole(strtolower($userRole));
                if ($status && $status->status !== 'pending') {
                    return redirect()->route('inbox.index')
                        ->with('error', 'Dokumen ini sudah diproses');
                }

                // If not pending and not handled by this user ever (or cleared), then unauthorized
                abort(403, 'Unauthorized access');
            }

            // Normalize module untuk layout (harus lowercase untuk match statement)
            $moduleMap = [
                'IbuB' => 'ibub',
                'Perpajakan' => 'perpajakan',
                'Akutansi' => 'akutansi',
                'Pembayaran' => 'pembayaran',
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
     * Updated to use new dokumen_statuses table
     */
    public function approve(Request $request, Dokumen $dokumen)
    {
        try {
            $user = auth()->user();
            $userRole = $this->getUserRole($user);
            $roleCode = strtolower($userRole);

            // Validate user has access using new status table
            if (!$dokumen->isPendingForRole($roleCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access atau dokumen sudah diproses'
                ], 403);
            }

            // Use new approval method
            $dokumen->approveFromRoleInbox($roleCode);

            return redirect()->route('inbox.index')
                ->with('success', 'Dokumen berhasil disetujui dan masuk ke daftar dokumen resmi.');

        } catch (\Exception $e) {
            Log::error('Error approving document from inbox: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Reject dokumen dari inbox
     * Updated to use new dokumen_statuses table
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
            $roleCode = strtolower($userRole);

            // Validate user has access using new status table
            if (!$dokumen->isPendingForRole($roleCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access atau dokumen sudah diproses'
                ], 403);
            }

            // Use new rejection method
            $dokumen->rejectFromRoleInbox($roleCode, $request->reason);

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
                'pembayaran' => 'Pembayaran',
                'Pembayaran' => 'Pembayaran',
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
                'Pembayaran' => 'Pembayaran'
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

            if (!$userRole || !in_array($userRole, ['IbuB', 'Perpajakan', 'Akutansi', 'Pembayaran'])) {
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
            // Use DokumenStatus to find new pending documents
            $newDocuments = Dokumen::whereHas('roleStatuses', function ($query) use ($userRole, $checkFrom) {
                $query->where('role_code', strtolower($userRole))
                    ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
                    ->where('status_changed_at', '>', $checkFrom);
            })
                ->with(['activityLogs', 'roleStatuses']) // Eager load for access
                ->get()
                ->map(function ($doc) use ($userRole) {
                    // Get the status record for date info
                    $status = $doc->getStatusForRole($userRole);
                    $doc->inbox_approval_sent_at = $status->status_changed_at;
                    return $doc;
                })
                ->sortByDesc('inbox_approval_sent_at')
                ->values();

            // Hitung total pending
            $pendingCount = \App\Models\DokumenStatus::where('role_code', strtolower($userRole))
                ->where('status', \App\Models\DokumenStatus::STATUS_PENDING)
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
                'new_documents' => $newDocuments->map(function ($doc) {
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
