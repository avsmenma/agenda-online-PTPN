<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect Admin/Owner to owner dashboard
        $user = auth()->user();

        if ($user) {
            $role = strtolower($user->role);

            if ($role === 'admin' || $role === 'owner') {
                return redirect('/owner/dashboard');
            }

            if ($role === 'ibub' || $role === 'ibu b') {
                return redirect()->route('dashboard.ibub');
            }

            if ($role === 'perpajakan') {
                return redirect()->route('dashboard.perpajakan');
            }

            if ($role === 'akutansi') {
                return redirect()->route('dashboard.akutansi');
            }

            if ($role === 'pembayaran') {
                return redirect()->route('dashboard.pembayaran');
            }
        }

        // Get statistics for IbuA (only documents created by ibuA)
        $totalDokumen = Dokumen::where('created_by', 'ibuA')->count();

        // Total dokumen belum dikirim = dokumen yang masih draft atau belum dikirim ke ibuB
        $totalBelumDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereDoesntHave('roleData', function ($query) {
                // If roleData for 'ibub' does NOT exist (or received_at is null) it hasn't been sent
                $query->where('role_code', 'ibub');
            })
            ->where('status', '!=', 'returned_to_ibua')
            ->count();

        // Total dokumen sudah dikirim = dokumen yang sudah dikirim ke ibuB
        $totalSudahDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereHas('roleData', function ($query) {
                // If roleData for 'ibub' exists, it has been sent
                $query->where('role_code', 'ibub');
            })
            ->where('status', '!=', 'returned_to_ibua')
            ->count();

        // Total dokumen yang di-reject dari inbox dan dikembalikan ke IbuA
        $totalDitolakInbox = Dokumen::where('created_by', 'ibuA')
            ->where('current_handler', 'ibuA')
            ->where('status', 'returned_to_ibua')
            ->whereHas('roleStatuses', function ($query) {
                $query->where('status', 'rejected');
            })
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
        return view('IbuA.dashboard', $data);
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
            
            // Initialize $checkFrom dengan default value
            $checkFrom = $checkFrom24Hours;
            
            try {
                if ($lastCheckTime) {
                    $parsedTime = \Carbon\Carbon::parse($lastCheckTime);
                    // Gunakan waktu yang lebih lama untuk memastikan tidak ada yang terlewat
                    $checkFrom = $parsedTime->gt($checkFrom24Hours) ? $checkFrom24Hours : $parsedTime;
                }
            } catch (\Exception $e) {
                \Log::warning('Invalid last_check_time format, using 24 hours ago', [
                    'last_check_time' => $lastCheckTime,
                    'error' => $e->getMessage()
                ]);
                // $checkFrom already set to $checkFrom24Hours as default
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
            // FIX: inbox_approval_responded_at sudah dihapus, gunakan status_changed_at dari dokumen_statuses
            $rejectedDocuments = Dokumen::where(function ($query) {
                // Hanya dokumen yang dibuat oleh IbuA
                $query->whereRaw('LOWER(created_by) IN (?, ?)', ['ibua', 'ibu a'])
                    ->orWhere('created_by', 'ibuA')
                    ->orWhere('created_by', 'IbuA');
            })
                ->where(function ($query) {
                    // DAN status returned ke IbuA
                    $query->where('status', 'returned_to_ibua')
                        ->whereHas('roleStatuses', function ($q) use ($checkFrom) {
                            // Filter by rejected status and check time from status_changed_at
                            $q->where('status', 'rejected')
                                ->where('status_changed_at', '>=', $checkFrom);
                        });
                })
                ->with([
                    'activityLogs' => function ($q) {
                        $q->whereIn('action', ['rejected', 'inbox_rejected'])
                            ->latest('action_at');
                    },
                    'roleStatuses' => function ($q) {
                        $q->where('status', 'rejected')
                            ->orderBy('status_changed_at', 'desc');
                    }
                ])
                ->get()
                ->filter(function ($doc) use ($checkFrom) {
                    try {
                        // Filter by the most recent rejection status change time
                        $rejectedStatus = $doc->roleStatuses->first();
                        if (!$rejectedStatus) {
                            return false;
                        }
                        return $rejectedStatus->status_changed_at && $rejectedStatus->status_changed_at >= $checkFrom;
                    } catch (\Exception $e) {
                        \Log::warning('Error filtering rejected document', [
                            'doc_id' => $doc->id,
                            'error' => $e->getMessage()
                        ]);
                        return false;
                    }
                })
                ->sortByDesc(function ($doc) {
                    try {
                        $rejectedStatus = $doc->roleStatuses->first();
                        return $rejectedStatus && $rejectedStatus->status_changed_at 
                            ? $rejectedStatus->status_changed_at->timestamp 
                            : 0;
                    } catch (\Exception $e) {
                        return 0;
                    }
                })
                ->values()
                ->map(function ($doc) {
                    try {
                        // Populate reason from log if needed, or other source
                        // Get reason from status notes or activity log details
                        $rejectLog = null;
                        try {
                            $rejectLog = $doc->activityLogs()
                                ->whereIn('action', ['rejected', 'inbox_rejected'])
                                ->latest('action_at')
                                ->first();
                        } catch (\Exception $e) {
                            \Log::warning('Error getting reject log', ['doc_id' => $doc->id, 'error' => $e->getMessage()]);
                        }
                        
                        $rejectedStatus = $doc->roleStatuses->first();
                        
                        // Get rejection reason from multiple sources
                        $reason = '';
                        if ($rejectedStatus && $rejectedStatus->notes) {
                            $reason = $rejectedStatus->notes;
                        } elseif ($rejectLog) {
                            $details = $rejectLog->details ?? [];
                            $reason = $details['rejection_reason'] ?? $details['reason'] ?? '';
                        }
                        
                        $doc->inbox_approval_reason = $reason;
                        
                        // Add status_changed_at for compatibility
                        $doc->inbox_approval_responded_at = $rejectedStatus && $rejectedStatus->status_changed_at 
                            ? $rejectedStatus->status_changed_at 
                            : null;
                        
                        return $doc;
                    } catch (\Exception $e) {
                        \Log::error('Error mapping rejected document', [
                            'doc_id' => $doc->id ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Return doc with default values if mapping fails
                        $doc->inbox_approval_reason = '';
                        $doc->inbox_approval_responded_at = null;
                        return $doc;
                    }
                });

            \Log::info('IbuA rejected documents found', [
                'count' => $rejectedDocuments->count(),
                'document_ids' => $rejectedDocuments->pluck('id')->toArray(),
            ]);

            // Hitung total rejected (case-insensitive)
            // FIX: inbox_approval_status sudah dihapus, gunakan dokumen_statuses
            $totalRejected = Dokumen::where(function ($query) {
                $query->whereRaw('LOWER(created_by) = ?', ['ibua'])
                    ->orWhere('created_by', 'ibuA')
                    ->orWhere('created_by', 'IbuA');
            })
                ->where(function ($query) {
                    $query->whereRaw('LOWER(current_handler) = ?', ['ibua'])
                        ->orWhere('current_handler', 'ibuA')
                        ->orWhere('current_handler', 'IbuA');
                })
                ->where('status', 'returned_to_ibua')
                ->whereHas('roleStatuses', function ($q) {
                    $q->where('status', 'rejected');
                })
                ->count();

            return response()->json([
                'success' => true,
                'rejected_documents_count' => $rejectedDocuments->count(),
                'total_rejected' => $totalRejected,
                'rejected_documents' => $rejectedDocuments->map(function ($doc) {
                    try {
                        // Get rejected by name from status or activity log
                        $rejectedStatus = $doc->roleStatuses->first();
                        $rejectLog = null;
                        try {
                            $rejectLog = $doc->activityLogs()
                                ->whereIn('action', ['rejected', 'inbox_rejected'])
                                ->latest('action_at')
                                ->first();
                        } catch (\Exception $e) {
                            \Log::warning('Error getting reject log in response mapping', [
                                'doc_id' => $doc->id ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                        }

                        $rejectedBy = 'Unknown';
                        if ($rejectedStatus && $rejectedStatus->changed_by) {
                            $rejectedBy = $rejectedStatus->changed_by;
                        } elseif ($rejectLog) {
                            $rejectedBy = $rejectLog->performed_by ?? (is_array($rejectLog->details) && isset($rejectLog->details['rejected_by']) ? $rejectLog->details['rejected_by'] : 'Unknown');
                        }
                        
                        // Map role to display name
                        $nameMap = [
                            'IbuB' => 'Team Verifikasi',
                            'ibuB' => 'Team Verifikasi',
                            'ibub' => 'Team Verifikasi',
                            'Perpajakan' => 'Team Perpajakan',
                            'perpajakan' => 'Team Perpajakan',
                            'Akutansi' => 'Team Akutansi',
                            'akutansi' => 'Team Akutansi',
                        ];
                        $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;

                        // Format nilai rupiah safely
                        $nilaiRupiah = 'Rp 0';
                        try {
                            if (isset($doc->nilai_rupiah) && $doc->nilai_rupiah) {
                                $nilaiRupiah = 'Rp ' . number_format((float)$doc->nilai_rupiah, 0, ',', '.');
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Error formatting nilai rupiah', ['doc_id' => $doc->id ?? 'unknown']);
                        }

                        // Format rejected_at safely - get from dokumen_statuses
                        $rejectedAt = '-';
                        $rejectionReason = 'Tidak ada alasan yang diberikan';
                        try {
                            // Get from rejectedStatus (already loaded)
                            if ($rejectedStatus && $rejectedStatus->status_changed_at) {
                                $rejectedAt = $rejectedStatus->status_changed_at->format('d/m/Y H:i');
                            }
                            
                            // Get rejection reason from status notes or activity log
                            if ($rejectedStatus && $rejectedStatus->notes) {
                                $rejectionReason = $rejectedStatus->notes;
                            } elseif ($rejectLog) {
                                $details = $rejectLog->details ?? [];
                                if (isset($details['rejection_reason'])) {
                                    $rejectionReason = $details['rejection_reason'];
                                } elseif (isset($details['reason'])) {
                                    $rejectionReason = $details['reason'];
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Error formatting rejected_at', [
                                'doc_id' => $doc->id ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                        }

                        return [
                            'id' => $doc->id ?? 0,
                            'nomor_agenda' => $doc->nomor_agenda ?? '-',
                            'nomor_spp' => $doc->nomor_spp ?? '-',
                            'uraian_spp' => \Illuminate\Support\Str::limit($doc->uraian_spp ?? '-', 50),
                            'nilai_rupiah' => $nilaiRupiah,
                            'rejected_at' => $rejectedAt,
                            'rejected_by' => $rejectedBy,
                            'rejection_reason' => \Illuminate\Support\Str::limit($rejectionReason, 100),
                            'url' => route('api.documents.rejected.show', $doc->id ?? 0),
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Error formatting rejected document response', [
                            'doc_id' => $doc->id ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return [
                            'id' => $doc->id ?? 0,
                            'nomor_agenda' => $doc->nomor_agenda ?? '-',
                            'nomor_spp' => $doc->nomor_spp ?? '-',
                            'uraian_spp' => '-',
                            'nilai_rupiah' => 'Rp 0',
                            'rejected_at' => '-',
                            'rejected_by' => 'Unknown',
                            'rejection_reason' => '-',
                            'url' => '#',
                        ];
                    }
                }),
                'current_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking rejected documents: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_role' => auth()->user()?->role ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Return empty result instead of 500 to prevent console errors
            return response()->json([
                'success' => false,
                'rejected_documents_count' => 0,
                'total_rejected' => 0,
                'rejected_documents' => [],
                'current_time' => now()->toIso8601String(),
                'message' => 'Gagal memeriksa dokumen yang ditolak: ' . $e->getMessage()
            ], 200); // Return 200 instead of 500 to prevent console spam
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
            // Check if rejected using new status system by checking if ANY role rejected it
            $hasRejection = \App\Models\DokumenStatus::where('dokumen_id', $dokumen->id)
                ->where('status', 'rejected')
                ->exists();

            if (
                $dokumen->status !== 'returned_to_ibua' ||
                !$hasRejection ||
                strtolower($dokumen->created_by) !== 'ibua' ||
                strtolower($dokumen->current_handler) !== 'ibua'
            ) {
                abort(404, 'Dokumen tidak ditemukan atau tidak valid');
            }

            // Get rejected status from dokumen_statuses
            $rejectedStatus = $dokumen->roleStatuses()
                ->where('status', 'rejected')
                ->latest('status_changed_at')
                ->first();

            // Get rejected by name from status or activity log
            $rejectLog = $dokumen->activityLogs()
                ->whereIn('action', ['rejected', 'inbox_rejected'])
                ->latest('action_at')
                ->first();

            $rejectedBy = 'Unknown';
            $rejectionReason = 'Tidak ada alasan yang diberikan';
            $rejectedAt = null;

            if ($rejectedStatus) {
                $rejectedBy = $rejectedStatus->changed_by ?? 'Unknown';
                $rejectionReason = $rejectedStatus->notes ?? 'Tidak ada alasan yang diberikan';
                $rejectedAt = $rejectedStatus->status_changed_at;
            } elseif ($rejectLog) {
                $rejectedBy = $rejectLog->performed_by ?? 'Unknown';
                if (isset($rejectLog->details['rejection_reason'])) {
                    $rejectionReason = $rejectLog->details['rejection_reason'];
                }
                $rejectedAt = $rejectLog->action_at;
            }

            // Map role to display name
            $nameMap = [
                'IbuB' => 'Team Verifikasi',
                'ibuB' => 'Team Verifikasi',
                'ibub' => 'Team Verifikasi',
                'Perpajakan' => 'Team Perpajakan',
                'perpajakan' => 'Team Perpajakan',
                'Akutansi' => 'Team Akutansi',
                'akutansi' => 'Team Akutansi',
            ];
            $rejectedBy = $nameMap[$rejectedBy] ?? $rejectedBy;

            // Return JSON response for AJAX modal
            return response()->json([
                'success' => true,
                'dokumen' => [
                    'id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'uraian_spp' => $dokumen->uraian_spp,
                    'nilai_rupiah' => 'Rp ' . number_format((float)$dokumen->nilai_rupiah, 0, ',', '.'),
                ],
                'rejected_by' => $rejectedBy,
                'rejection_reason' => $rejectionReason,
                'rejected_at' => $rejectedAt ? $rejectedAt->format('d/m/Y H:i') : '-',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error showing rejected document: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail dokumen yang ditolak');
        }
    }
}
