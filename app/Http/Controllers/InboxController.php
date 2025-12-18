<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\DokumenStatus;
use App\Models\DocumentActivity;
use App\Events\DocumentActivityChanged;
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
            Log::error('Error loading inbox index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show detailed error in development, generic message in production
            $errorMessage = config('app.debug') 
                ? 'Gagal memuat daftar dokumen inbox: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
                : 'Gagal memuat daftar dokumen inbox. Silakan cek log untuk detail.';
            
            return back()->with('error', $errorMessage);
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
            $roleCode = strtolower($userRole);

            // Refresh dokumen untuk memastikan data terbaru
            $dokumen->refresh();
            $dokumen->load('roleStatuses');

            // Validate user has access to this inbox
            // Note: This check logic is a bit complex due to unified model methods
            // We check if there is a pending status for this role
            if (!$dokumen->isPendingForRole($roleCode)) {
                // Double check: maybe it WAS pending for this user but handled?
                // If so, redirect to index with appropriate message
                $status = $dokumen->getStatusForRole($roleCode);
                if ($status) {
                    if ($status->status === DokumenStatus::STATUS_APPROVED) {
                        // Dokumen sudah di-approve - redirect dengan info message
                        return redirect()->route('inbox.index')
                            ->with('info', 'Dokumen ini sudah disetujui dan telah masuk ke daftar dokumen resmi.');
                    } elseif ($status->status === DokumenStatus::STATUS_REJECTED) {
                        // Dokumen sudah di-reject
                        return redirect()->route('inbox.index')
                            ->with('info', 'Dokumen ini sudah ditolak sebelumnya.');
                    }
                }

                // If not pending and not handled by this user ever (or cleared), then unauthorized
                return redirect()->route('inbox.index')
                    ->with('error', 'Dokumen ini tidak tersedia untuk approval atau sudah diproses.');
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
            Log::error('Error loading inbox show: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show detailed error in development, generic message in production
            $errorMessage = config('app.debug') 
                ? 'Gagal memuat detail dokumen: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
                : 'Gagal memuat detail dokumen. Silakan cek log untuk detail.';
            
            return back()->with('error', $errorMessage);
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

            // Refresh dokumen untuk memastikan data terbaru (mencegah race condition)
            $dokumen->refresh();
            
            // Reload relationship untuk mendapatkan status terbaru
            $dokumen->load('roleStatuses');

            // Validate user has access using new status table
            if (!$dokumen->isPendingForRole($roleCode)) {
                // Cek apakah dokumen sudah di-approve oleh user ini
                $status = $dokumen->getStatusForRole($roleCode);
                if ($status && $status->status === DokumenStatus::STATUS_APPROVED) {
                    // Dokumen sudah di-approve, redirect dengan success message
                    return redirect()->route('inbox.index')
                        ->with('info', 'Dokumen ini sudah disetujui sebelumnya dan telah masuk ke daftar dokumen resmi.');
                }
                
                // Dokumen tidak pending dan tidak approved - mungkin sudah di-reject atau tidak ada akses
                return redirect()->route('inbox.index')
                    ->with('error', 'Dokumen ini sudah diproses atau tidak tersedia untuk approval.');
            }

            // Use new approval method
            $dokumen->approveFromRoleInbox($roleCode);

            return redirect()->route('inbox.index')
                ->with('success', 'Dokumen berhasil disetujui dan masuk ke daftar dokumen resmi.');

        } catch (\Exception $e) {
            Log::error('Error approving document from inbox: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id,
                'user_role' => $userRole ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('inbox.index')
                ->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
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

            // Refresh dokumen untuk memastikan data terbaru (mencegah race condition)
            $dokumen->refresh();
            $dokumen->load('roleStatuses');

            // Validate user has access using new status table
            if (!$dokumen->isPendingForRole($roleCode)) {
                // Cek apakah dokumen sudah di-process
                $status = $dokumen->getStatusForRole($roleCode);
                if ($status && $status->status === DokumenStatus::STATUS_APPROVED) {
                    return redirect()->route('inbox.index')
                        ->with('info', 'Dokumen ini sudah disetujui sebelumnya dan tidak dapat ditolak.');
                } elseif ($status && $status->status === DokumenStatus::STATUS_REJECTED) {
                    return redirect()->route('inbox.index')
                        ->with('info', 'Dokumen ini sudah ditolak sebelumnya.');
                }
                
                return redirect()->route('inbox.index')
                    ->with('error', 'Dokumen ini sudah diproses atau tidak tersedia untuk penolakan.');
            }

            // Use new rejection method
            $dokumen->rejectFromRoleInbox($roleCode, $request->reason);

            return redirect()->route('inbox.index')
                ->with('success', 'Dokumen ditolak dan dikembalikan ke pengirim dengan alasan: ' . $request->reason);

        } catch (\Exception $e) {
            Log::error('Error rejecting document from inbox: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id,
                'user_role' => $userRole ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show detailed error in development, generic message in production
            $errorMessage = config('app.debug') 
                ? 'Gagal menolak dokumen: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')'
                : 'Gagal menolak dokumen. Silakan cek log untuk detail.';
            
            return redirect()->route('inbox.index')
                ->with('error', $errorMessage);
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

    /**
     * Track user activity on a document (viewing/editing)
     */
    public function trackActivity(Request $request, $dokumenId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                Log::warning('Activity tracking: Unauthorized user');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $activityType = $request->input('activity_type', DocumentActivity::TYPE_VIEWING);
            
            Log::info('Activity tracking request', [
                'dokumen_id' => $dokumenId,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'activity_type' => $activityType
            ]);
            
            // Validate activity type
            if (!in_array($activityType, [DocumentActivity::TYPE_VIEWING, DocumentActivity::TYPE_EDITING])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid activity type'
                ], 400);
            }

            // Check if document exists
            $dokumen = Dokumen::findOrFail($dokumenId);

            // Update or create activity record
            $activity = DocumentActivity::updateOrCreate(
                [
                    'dokumen_id' => $dokumenId,
                    'user_id' => $user->id,
                    'activity_type' => $activityType,
                ],
                [
                    'last_activity_at' => now(),
                ]
            );

            Log::info('Activity saved', [
                'activity_id' => $activity->id,
                'dokumen_id' => $dokumenId,
                'user_id' => $user->id
            ]);

            // Broadcast activity change
            broadcast(new DocumentActivityChanged(
                $dokumenId,
                $user->id,
                $user->name,
                $user->role,
                $activityType,
                now()->toIso8601String()
            ));

            Log::info('Activity broadcasted', [
                'dokumen_id' => $dokumenId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity tracked successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error tracking activity: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal melacak aktivitas'
            ], 500);
        }
    }

    /**
     * Get current activities for a document
     */
    public function getActivities($dokumenId)
    {
        try {
            Log::info('Getting activities', ['dokumen_id' => $dokumenId]);
            
            $activities = DocumentActivity::with('user')
                ->where('dokumen_id', $dokumenId)
                ->active()
                ->get();

            Log::info('Activities found', [
                'dokumen_id' => $dokumenId,
                'count' => $activities->count(),
                'activities' => $activities->map(fn($a) => [
                    'user_id' => $a->user_id,
                    'user_name' => $a->user->name ?? 'Unknown',
                    'activity_type' => $a->activity_type
                ])->toArray()
            ]);

            $grouped = $activities->groupBy('activity_type')
                ->map(function ($group) {
                    return $group->map(function ($activity) {
                        return [
                            'user_id' => $activity->user_id,
                            'user_name' => $activity->user->name ?? 'Unknown',
                            'user_role' => $activity->user->role ?? null,
                            'last_activity_at' => $activity->last_activity_at->toIso8601String(),
                        ];
                    })->values();
                });

            $result = [
                'success' => true,
                'activities' => [
                    'viewing' => $grouped->get(DocumentActivity::TYPE_VIEWING, []),
                    'editing' => $grouped->get(DocumentActivity::TYPE_EDITING, []),
                ]
            ];

            Log::info('Activities response', [
                'dokumen_id' => $dokumenId,
                'viewing_count' => count($result['activities']['viewing']),
                'editing_count' => count($result['activities']['editing'])
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error getting activities: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aktivitas'
            ], 500);
        }
    }

    /**
     * Stop tracking activity (when user leaves page)
     */
    public function stopActivity(Request $request, $dokumenId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false], 401);
            }

            // Delete activity records for this user and document
            DocumentActivity::where('dokumen_id', $dokumenId)
                ->where('user_id', $user->id)
                ->delete();

            // Broadcast that user left
            broadcast(new DocumentActivityChanged(
                $dokumenId,
                $user->id,
                $user->name,
                $user->role,
                'left',
                now()->toIso8601String()
            ));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error stopping activity: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
