<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentPreviewController extends Controller
{
    /**
     * Get document preview data
     * 
     * @param int $dokumenId
     * @return JsonResponse
     */
    public function getPreviewData(int $dokumenId): JsonResponse
    {
        try {
            // Load document with relationships (optimized with eager loading)
            $dokumen = Dokumen::with([
                'roleData',
                'roleStatuses',
                'activityLogs' => function ($query) {
                    $query->latest('action_at')->limit(10);
                },
                'dibayarKepadas'
            ])->findOrFail($dokumenId);

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $dokumen->id,
                    'nomor_agenda' => $dokumen->nomor_agenda,
                    'nomor_spp' => $dokumen->nomor_spp,
                    'tanggal_spp' => $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d M Y') : null,
                    'uraian' => $dokumen->uraian,
                    'nilai_rupiah' => $dokumen->nilai_rupiah,
                    'nilai_formatted' => 'Rp ' . number_format($dokumen->nilai_rupiah, 0, ',', '.'),
                    'dibayar_kepada' => $dokumen->dibayar_kepada,
                    'status' => $dokumen->status,
                    'status_display' => $this->getStatusDisplay($dokumen->status),
                    'current_handler' => $dokumen->current_handler,
                    'handler_display' => $this->getHandlerDisplay($dokumen->current_handler),
                    'bagian' => $dokumen->bagian,
                    'kebun' => $dokumen->kebun,
                    'created_at' => $dokumen->created_at->format('d M Y H:i'),
                    'created_by' => $dokumen->created_by,
                ],
                'timeline' => $this->getTimeline($dokumen),
                'attachments' => $this->getAttachments($dokumen),
                'comments' => $this->getComments($dokumen),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found or error loading data',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Quick approve document (Team Verifikasi)
     * 
     * @param Request $request
     * @param int $dokumenId
     * @return JsonResponse
     */
    public function quickApprove(Request $request, int $dokumenId): JsonResponse
    {
        DB::beginTransaction();

        try {
            $dokumen = Dokumen::findOrFail($dokumenId);

            // Verify user has permission (Team Verifikasi)
            if (auth()->user()->role !== 'team_verifikasi' && auth()->user()->role !== 'ibu_b') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            // Update document status
            $dokumen->status = 'sent_to_perpajakan';
            $dokumen->current_handler = 'perpajakan';
            $dokumen->save();

            // Update role data
            $roleData = DokumenRoleData::where('dokumen_id', $dokumenId)
                ->where('role_code', 'team_verifikasi')
                ->first();

            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->display_status = 'approved';
                $roleData->save();
            }

            // Create role data for Perpajakan
            DokumenRoleData::updateOrCreate(
                [
                    'dokumen_id' => $dokumenId,
                    'role_code' => 'perpajakan'
                ],
                [
                    'received_at' => now(),
                    'display_status' => 'received'
                ]
            );

            // Log activity
            \App\Models\DokumenActivityLog::create([
                'dokumen_id' => $dokumenId,
                'stage' => 'team_verifikasi',
                'action' => 'approved_quick',
                'username' => auth()->user()->username,
                'notes' => $request->input('notes', 'Approved via quick action'),
                'action_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document approved and sent to Perpajakan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick reject document (Return to Bagian)
     * 
     * @param Request $request
     * @param int $dokumenId
     * @return JsonResponse
     */
    public function quickReject(Request $request, int $dokumenId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $dokumen = Dokumen::findOrFail($dokumenId);

            // Verify permission
            if (auth()->user()->role !== 'team_verifikasi' && auth()->user()->role !== 'ibu_b') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            // Update document status
            $dokumen->status = 'returned_to_department';
            $dokumen->current_handler = 'bagian';
            $dokumen->alasan_pengembalian = $request->input('reason');
            $dokumen->save();

            // Update role data
            $roleData = DokumenRoleData::where('dokumen_id', $dokumenId)
                ->where('role_code', 'team_verifikasi')
                ->first();

            if ($roleData) {
                $roleData->processed_at = now();
                $roleData->display_status = 'returned';
                $roleData->save();
            }

            // Log activity
            \App\Models\DokumenActivityLog::create([
                'dokumen_id' => $dokumenId,
                'stage' => 'team_verifikasi',
                'action' => 'returned_to_department',
                'username' => auth()->user()->username,
                'notes' => $request->input('reason'),
                'action_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document returned to Bagian'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to return document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document timeline
     * 
     * @param Dokumen $dokumen
     * @return array
     */
    private function getTimeline(Dokumen $dokumen): array
    {
        $timeline = [];

        // Get all role data ordered by received_at
        $roleData = $dokumen->roleData()
            ->whereNotNull('received_at')
            ->orderBy('received_at', 'asc')
            ->get();

        foreach ($roleData as $data) {
            $duration = null;
            $durationHours = null;

            if ($data->received_at && $data->processed_at) {
                $durationHours = $data->received_at->diffInHours($data->processed_at);
                $days = floor($durationHours / 24);
                $hours = $durationHours % 24;

                if ($days > 0) {
                    $duration = "{$days}d {$hours}h";
                } else {
                    $duration = "{$hours}h";
                }
            }

            $timeline[] = [
                'stage' => $data->role_code,
                'stage_display' => $this->getRoleDisplayName($data->role_code),
                'received_at' => $data->received_at ? $data->received_at->format('d M Y H:i') : null,
                'processed_at' => $data->processed_at ? $data->processed_at->format('d M Y H:i') : null,
                'status' => $data->display_status,
                'duration' => $duration,
                'duration_hours' => $durationHours,
                'is_completed' => $data->processed_at !== null,
                'is_current' => $dokumen->current_handler === $data->role_code,
            ];
        }

        return $timeline;
    }

    /**
     * Get document attachments
     * 
     * @param Dokumen $dokumen
     * @return array
     */
    private function getAttachments(Dokumen $dokumen): array
    {
        $attachments = [];

        // Check for file columns in dokumen table
        $fileFields = [
            'file_spp' => 'SPP Document',
            'file_invoice' => 'Invoice',
            'file_kwitansi' => 'Kwitansi',
            'file_faktur_pajak' => 'Faktur Pajak',
            'file_kontrak' => 'Kontrak',
        ];

        foreach ($fileFields as $field => $label) {
            if (!empty($dokumen->$field)) {
                $attachments[] = [
                    'name' => $label,
                    'filename' => basename($dokumen->$field),
                    'path' => $dokumen->$field,
                    'url' => asset('storage/' . $dokumen->$field),
                    'size' => file_exists(storage_path('app/public/' . $dokumen->$field))
                        ? filesize(storage_path('app/public/' . $dokumen->$field))
                        : 0,
                ];
            }
        }

        return $attachments;
    }

    /**
     * Get document comments/activities
     * 
     * @param Dokumen $dokumen
     * @return array
     */
    private function getComments(Dokumen $dokumen): array
    {
        $comments = [];

        if ($dokumen->activityLogs) {
            foreach ($dokumen->activityLogs as $log) {
                $comments[] = [
                    'user' => $log->username ?? 'System',
                    'stage' => $this->getRoleDisplayName($log->stage),
                    'action' => $this->getActionDisplay($log->action),
                    'notes' => $log->notes,
                    'created_at' => $log->action_at ? $log->action_at->format('d M Y H:i') : null,
                ];
            }
        }

        return $comments;
    }

    /**
     * Get status display name
     * 
     * @param string $status
     * @return string
     */
    private function getStatusDisplay(?string $status): string
    {
        $statuses = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'sent_to_verifikasi' => 'Sent to Verifikasi',
            'sent_to_perpajakan' => 'Sent to Perpajakan',
            'sent_to_akutansi' => 'Sent to Akutansi',
            'sent_to_pembayaran' => 'Sent to Pembayaran',
            'completed' => 'Completed',
            'returned_to_department' => 'Returned to Department',
        ];

        return $statuses[$status] ?? ucfirst(str_replace('_', ' ', $status ?? 'unknown'));
    }

    /**
     * Get handler display name
     * 
     * @param string $handler
     * @return string
     */
    private function getHandlerDisplay(?string $handler): string
    {
        return $this->getRoleDisplayName($handler);
    }

    /**
     * Get role display name
     * 
     * @param string $role
     * @return string
     */
    private function getRoleDisplayName(?string $role): string
    {
        $roles = [
            'bagian' => 'Bagian',
            'team_verifikasi' => 'Team Verifikasi',
            'ibu_b' => 'Team Verifikasi',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
            'owner' => 'Owner',
        ];

        return $roles[$role] ?? ucfirst($role ?? 'unknown');
    }

    /**
     * Get action display name
     * 
     * @param string $action
     * @return string
     */
    private function getActionDisplay(?string $action): string
    {
        $actions = [
            'created' => 'Created document',
            'approved' => 'Approved',
            'approved_quick' => 'Approved (Quick Action)',
            'rejected' => 'Rejected',
            'returned_to_department' => 'Returned to Department',
            'forwarded' => 'Forwarded',
            'updated' => 'Updated document',
        ];

        return $actions[$action] ?? ucfirst(str_replace('_', ' ', $action ?? 'unknown'));
    }
}
