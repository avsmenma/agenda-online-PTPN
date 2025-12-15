<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Dokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        // Core document fields
        'nomor_agenda',
        'bulan',
        'tahun',
        'tanggal_masuk',
        'nomor_spp',
        'tanggal_spp',
        'uraian_spp',
        'nilai_rupiah',
        'kategori',
        'jenis_dokumen',
        'jenis_sub_pekerjaan',
        'jenis_pembayaran',
        'kebun',
        'bagian',
        'nama_pengirim',
        'dibayar_kepada',
        'no_berita_acara',
        'tanggal_berita_acara',
        'no_spk',
        'tanggal_spk',
        'tanggal_berakhir_spk',
        'nomor_mirror',
        'nomor_miro',
        'status',
        'keterangan',
        'alasan_pengembalian',
        // Department/Bidang return fields
        'target_department',
        'department_returned_at',
        'department_return_reason',
        'target_bidang',
        'bidang_returned_at',
        'bidang_return_reason',
        // Workflow tracking (kept for compatibility)
        'created_by',
        'current_handler',
        'current_stage',
        'last_action_status',
        // Perpajakan fields (core data, kept in dokumens)
        'npwp',
        'status_perpajakan',
        'no_faktur',
        'tanggal_faktur',
        'tanggal_selesai_verifikasi_pajak',
        'jenis_pph',
        'dpp_pph',
        'ppn_terhutang',
        'link_dokumen_pajak',
        // Perpajakan Extended Fields
        'komoditi_perpajakan',
        'alamat_pembeli',
        'no_kontrak',
        'no_invoice',
        'tanggal_invoice',
        'dpp_invoice',
        'ppn_invoice',
        'dpp_ppn_invoice',
        'tanggal_pengajuan_pajak',
        'dpp_faktur',
        'ppn_faktur',
        'selisih_pajak',
        'keterangan_pajak',
        'penggantian_pajak',
        'dpp_penggantian',
        'ppn_penggantian',
        'selisih_ppn',
        // Pembayaran fields (core data)
        'status_pembayaran',
        'tanggal_dibayar',
        'link_bukti_pembayaran',
        // CSV Import additional fields
        'nama_kebuns',
        'no_ba',
        'NO_PO',
        'NO_MIRO_SES',
        'DIBAYAR',
        'BELUM_DIBAYAR',
        'KATEGORI',
    ];

    protected $casts = [
        // Core document casts
        'tanggal_masuk' => 'datetime',
        'tanggal_spp' => 'datetime',
        'tanggal_berita_acara' => 'date',
        'tanggal_spk' => 'date',
        'tanggal_berakhir_spk' => 'date',
        'nilai_rupiah' => 'decimal:2',
        // Department return casts
        'department_returned_at' => 'datetime',
        'bidang_returned_at' => 'datetime',
        // Perpajakan casts
        'tanggal_faktur' => 'date',
        'tanggal_selesai_verifikasi_pajak' => 'date',
        'dpp_pph' => 'decimal:2',
        'ppn_terhutang' => 'decimal:2',
        // Perpajakan Extended casts
        'tanggal_invoice' => 'date',
        'tanggal_pengajuan_pajak' => 'date',
        'dpp_invoice' => 'decimal:2',
        'ppn_invoice' => 'decimal:2',
        'dpp_ppn_invoice' => 'decimal:2',
        'dpp_faktur' => 'decimal:2',
        'ppn_faktur' => 'decimal:2',
        'selisih_pajak' => 'decimal:2',
        'penggantian_pajak' => 'decimal:2',
        'dpp_penggantian' => 'decimal:2',
        'ppn_penggantian' => 'decimal:2',
        'selisih_ppn' => 'decimal:2',
        // Pembayaran casts
        'tanggal_dibayar' => 'date',
    ];

    public function dokumenPos(): HasMany
    {
        return $this->hasMany(DokumenPO::class);
    }

    public function dokumenPrs(): HasMany
    {
        return $this->hasMany(DokumenPR::class);
    }

    public function getFormattedNilaiRupiahAttribute()
    {
        return 'Rp. ' . number_format((float) $this->nilai_rupiah, 0, ',', '.');
    }

    public function getFormattedNomorAgendaAttribute()
    {
        return $this->nomor_agenda;
    }

    public function dibayarKepadas(): HasMany
    {
        return $this->hasMany(DibayarKepada::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(DokumenActivityLog::class)->orderBy('action_at', 'desc');
    }

    public function documentTrackings(): HasMany
    {
        return $this->hasMany(DocumentTracking::class, 'document_id');
    }

    /**
     * Get all role statuses for this document
     */
    public function roleStatuses(): HasMany
    {
        return $this->hasMany(DokumenStatus::class);
    }

    /**
     * Get all role data for this document
     */
    public function roleData(): HasMany
    {
        return $this->hasMany(DokumenRoleData::class);
    }

    /**
     * Get status for a specific role
     */
    public function getStatusForRole(string $roleCode): ?DokumenStatus
    {
        return $this->roleStatuses()->where('role_code', strtolower($roleCode))->first();
    }

    /**
     * Get data for a specific role
     */
    public function getDataForRole(string $roleCode): ?DokumenRoleData
    {
        return $this->roleData()->where('role_code', strtolower($roleCode))->first();
    }

    /**
     * Create or update status for a role
     */
    public function setStatusForRole(string $roleCode, string $status, ?string $changedBy = null, ?string $notes = null): DokumenStatus
    {
        $roleCode = strtolower($roleCode);
        $changedBy = $changedBy ?? auth()->user()?->name ?? 'System';

        return DokumenStatus::updateOrCreate(
            ['dokumen_id' => $this->id, 'role_code' => $roleCode],
            [
                'status' => $status,
                'status_changed_at' => now(),
                'changed_by' => $changedBy,
                'notes' => $notes,
            ]
        );
    }

    /**
     * Create or update data for a role
     */
    public function setDataForRole(string $roleCode, array $data): DokumenRoleData
    {
        $roleCode = strtolower($roleCode);

        return DokumenRoleData::updateOrCreate(
            ['dokumen_id' => $this->id, 'role_code' => $roleCode],
            $data
        );
    }

    /**
     * Send document to a role's inbox
     */
    public function sendToRoleInbox(string $targetRoleCode, ?string $senderRoleCode = null): DokumenStatus
    {
        $targetRoleCode = strtolower($targetRoleCode);
        $senderRoleCode = $senderRoleCode ?? auth()->user()?->role ?? 'system';

        // Set target role status to pending
        $targetStatus = $this->setStatusForRole($targetRoleCode, DokumenStatus::STATUS_PENDING, $senderRoleCode);

        // Update data for target role
        $this->setDataForRole($targetRoleCode, [
            'received_at' => now(),
        ]);

        // Log activity
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $targetRoleCode,
            'action' => 'sent_to_inbox',
            'action_description' => "Dokumen dikirim ke inbox {$targetRoleCode}",
            'performed_by' => $senderRoleCode,
            'action_at' => now(),
            'details' => [
                'sender_role' => $senderRoleCode,
                'target_role' => $targetRoleCode,
            ]
        ]);

        return $targetStatus;
    }

    /**
     * Approve document from role's inbox
     */
    public function approveFromRoleInbox(string $roleCode): DokumenStatus
    {
        $roleCode = strtolower($roleCode);
        $approvedBy = auth()->user()?->name ?? 'System';

        // Update role status to approved
        $status = $this->setStatusForRole($roleCode, DokumenStatus::STATUS_APPROVED, $approvedBy);

        // Update processed time
        $roleData = $this->getDataForRole($roleCode);
        if ($roleData) {
            $roleData->processed_at = now();
            $roleData->save();
        }

        // === SYNC LEGACY COLUMNS ===
        // Update legacy columns for backward compatibility with existing dashboards
        // Map role code to proper handler format (capital B for ibuB to match query expectations)
        $handlerMap = [
            'ibub' => 'ibuB',
            'perpajakan' => 'perpajakan',
            'akutansi' => 'akutansi',
            'pembayaran' => 'pembayaran',
        ];
        $this->current_handler = $handlerMap[$roleCode] ?? $roleCode;

        switch ($roleCode) {
            case 'ibub':
                $this->status = 'sedang diproses'; // Status expected by DashboardB (with space, not underscore)
                $this->processed_at = now(); // Legacy timestamp for IbuB
                break;

            case 'perpajakan':
                $this->status = 'proses_perpajakan';
                $this->processed_perpajakan_at = now(); // Legacy timestamp
                break;

            case 'akutansi':
                $this->status = 'proses_akutansi';
                // sent_to_akutansi_at is removed, rely on generic status
                break;

            case 'pembayaran':
                $this->status = 'proses_pembayaran';
                if (!$this->status_pembayaran) {
                    $this->status_pembayaran = 'pending';
                }
                break;
        }

        $this->save();
        // === END SYNC LEGACY COLUMNS ===

        // Log activity
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $roleCode,
            'action' => 'approved',
            'action_description' => "Dokumen disetujui oleh {$approvedBy}",
            'performed_by' => $approvedBy,
            'action_at' => now(),
        ]);

        // Fire event
        event(new \App\Events\DocumentApprovedInbox($this, $roleCode));

        return $status;
    }

    /**
     * Reject document from role's inbox
     */
    public function rejectFromRoleInbox(string $roleCode, string $reason): DokumenStatus
    {
        $roleCode = strtolower($roleCode);
        $rejectedBy = auth()->user()?->name ?? 'System';

        // Update role status to rejected
        $status = $this->setStatusForRole($roleCode, DokumenStatus::STATUS_REJECTED, $rejectedBy, $reason);

        // Log activity
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $roleCode,
            'action' => 'rejected',
            'action_description' => "Dokumen ditolak: {$reason}",
            'performed_by' => $rejectedBy,
            'action_at' => now(),
            'details' => ['rejection_reason' => $reason],
        ]);

        // Fire event
        event(new \App\Events\DocumentRejectedInbox($this, $reason, $roleCode));

        return $status;
    }

    /**
     * Check if document is pending for a specific role
     */
    public function isPendingForRole(string $roleCode): bool
    {
        $status = $this->getStatusForRole($roleCode);
        return $status && $status->status === DokumenStatus::STATUS_PENDING;
    }

    /**
     * Get the current role that has the document (pending status)
     */
    public function getCurrentRoleHandler(): ?string
    {
        $pendingStatus = $this->roleStatuses()
            ->where('status', DokumenStatus::STATUS_PENDING)
            ->first();

        return $pendingStatus?->role_code;
    }

    /**
     * Helper method untuk cek apakah dokumen sedang pending approval
     */
    public function isPendingApproval(): bool
    {
        return in_array($this->status, [
            'pending_approval_ibub',
            'pending_approval_perpajakan',
            'pending_approval_akutansi'
        ]);
    }

    /**
     * Helper method untuk cek pending approval untuk role tertentu
     */
    public function isPendingApprovalFor(string $role): bool
    {
        return $this->pending_approval_for === $role && $this->isPendingApproval();
    }

    /**
     * Get all available approval roles
     */
    public static function getApprovalRoles(): array
    {
        return [
            'ibuB' => 'Ibu B',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
        ];
    }

    /**
     * Get status display name for pending approval
     */
    public function getPendingApprovalStatusDisplay(): string
    {
        $statusMap = [
            'pending_approval_ibub' => 'Menunggu Persetujuan Ibu B',
            'pending_approval_perpajakan' => 'Menunggu Persetujuan Perpajakan',
            'pending_approval_akutansi' => 'Menunggu Persetujuan Akutansi',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
    /**
     * Cek apakah dokumen menunggu approval untuk role tertentu (Inbox System)
     */
    public function isWaitingApprovalFor(string $role): bool
    {
        return $this->isPendingForRole($role);
    }




    /**
     * Get user yang mengirim dokumen (creator display name)
     */
    public function getSenderDisplayName(): string
    {
        // Default: gunakan created_by
        $senderMap = [
            'ibuA' => 'Ibu Tarapul',
            'ibuB' => 'Ibu Yuni',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Team Pembayaran',
        ];

        return $senderMap[$this->created_by] ?? $this->created_by ?? 'Unknown';
    }

    /**
     * Inbox Approval System Methods
     */

    /**
     * Send document to inbox for approval
     */
    /**
     * Send document to inbox for approval
     * Refactored to use new DokumenStatus system
     */
    public function sendToInbox($recipientRole)
    {
        // Normalize recipient role to match enum values
        $roleMap = [
            'IbuB' => 'ibuB', // Lowercase for internal code
            'ibuB' => 'ibuB',
            'Ibu B' => 'ibuB',
            'Ibu Yuni' => 'ibuB',
            'Perpajakan' => 'perpajakan',
            'perpajakan' => 'perpajakan',
            'Akutansi' => 'akutansi',
            'akutansi' => 'akutansi',
            'Pembayaran' => 'pembayaran',
            'pembayaran' => 'pembayaran',
        ];
        $normalizedRole = $roleMap[$recipientRole] ?? strtolower($recipientRole);

        // Use new method to create status record
        // current_handler will be updated here if needed, or by the caller
        $this->sendToRoleInbox($normalizedRole);

        // Update legacy tracking columns if they still exist (for backward compat in views/logic)
        // current_stage and last_action_status were KEPT in the cleanup
        $stageMap = [
            'ibuB' => 'reviewer',
            'perpajakan' => 'tax',
            'akutansi' => 'accounting',
            'pembayaran' => 'payment',
        ];

        // Only update these if implicit stage transition is desired
        if (isset($stageMap[$normalizedRole])) {
            $this->current_stage = $stageMap[$normalizedRole];
        }

        $actionStatusMap = [
            'ibuB' => 'sent_to_reviewer',
            'perpajakan' => 'sent_to_tax',
            'akutansi' => 'sent_to_accounting',
            'pembayaran' => 'sent_to_payment',
        ];

        if (isset($actionStatusMap[$normalizedRole])) {
            $this->last_action_status = $actionStatusMap[$normalizedRole];
        } else {
            $this->last_action_status = 'sent_to_' . $normalizedRole;
        }

        // Set legacy status for IbuB/Reviewer flow
        if ($normalizedRole === 'ibuB' && $this->status !== 'waiting_reviewer_approval') {
            $this->status = 'waiting_reviewer_approval';
        }

        $this->save();

        // Event firing is likely handled by controller or redundant now, 
        // but keeping it safe if listeners depend on it.
        // CHECK if event uses deleted fields inside it.
        try {
            event(new \App\Events\DocumentSentToInbox($this, $recipientRole));
        } catch (\Exception $e) {
            \Log::error('Failed to fire DocumentSentToInbox event: ' . $e->getMessage());
        }
    }

    /**
     * Approve document from inbox
     */
    /**
     * Approve document from inbox
     * Refactored to use new DokumenStatus system
     */
    public function approveInbox()
    {
        // Find pending status to determine which role is approving
        $pendingStatus = $this->roleStatuses()
            ->where('status', DokumenStatus::STATUS_PENDING)
            ->first();

        if (!$pendingStatus) {
            \Log::warning('approveInbox called but no pending status found', ['dokumen_id' => $this->id]);
            return;
        }

        $roleCode = $pendingStatus->role_code;

        // Delegate to new method
        $this->approveFromRoleInbox($roleCode);

        // Update legacy tracking columns for backward compatibility if needed
        // (This logic matches what was previously in sendToInbox/approveInbox)
        $handlerMap = [
            'ibub' => 'ibuB',
            'perpajakan' => 'perpajakan',
            'akutansi' => 'akutansi',
            'pembayaran' => 'pembayaran',
        ];

        if (isset($handlerMap[$roleCode])) {
            $this->current_handler = $handlerMap[$roleCode];

            // Map role code to status string expected by legacy views
            $statusMap = [
                'ibub' => 'sedang diproses',
                'perpajakan' => 'sent_to_perpajakan',
                'akutansi' => 'sent_to_accounting',
                'pembayaran' => 'sent_to_payment',
            ];

            if (isset($statusMap[$roleCode])) {
                // Update status only if it makes sense (don't overwrite 'completed' etc)
                // This is a loose fallback
                $this->status = $statusMap[$roleCode];
            }
        }

        $this->save();

        // Fire event (might be redundant but safe)
        // Removed here as it is fired in approveFromRoleInbox
        // event(new \App\Events\DocumentApprovedInbox($this, $roleCode));
    }

    /**
     * Reject document from inbox
     * Updated to use last_action_status instead of overwriting global status
     */
    /**
     * Reject document from inbox
     * Refactored to use new DokumenStatus system
     */
    public function rejectInbox($reason)
    {
        // Find pending status to determine which role is rejecting
        $pendingStatus = $this->roleStatuses()
            ->where('status', DokumenStatus::STATUS_PENDING)
            ->first();

        // If no pending status, check who is current_handler to guess who is rejecting
        // This is a fallback
        $roleCode = $pendingStatus ? $pendingStatus->role_code : strtolower($this->current_handler ?? 'unknown');

        // Delegate to new method
        // Note: rejectFromRoleInbox returns legacy struct, we ignore it here
        // Corrected args: roleCode first, then reason
        $this->rejectFromRoleInbox($roleCode, $reason);

        // Update legacy columns for backward compatibility / visibility
        $rejectionStatusMap = [
            'ibub' => 'rejected_by_reviewer',
            'perpajakan' => 'rejected_by_tax',
            'akutansi' => 'rejected_by_accounting',
            'pembayaran' => 'rejected',
        ];

        $this->last_action_status = $rejectionStatusMap[$roleCode] ?? 'rejected';

        // Update current_stage
        $stageMap = [
            'ibub' => 'reviewer',
            'perpajakan' => 'tax',
            'akutansi' => 'accounting',
            'pembayaran' => 'payment',
        ];
        if (isset($stageMap[$roleCode])) {
            $this->current_stage = $stageMap[$roleCode];
        }

        // Return logic (Legacy)
        // IbuB -> IbuA
        // Perpajakan -> IbuB
        // Akutansi -> IbuB
        $originalSender = 'ibuA';
        $returnStatus = 'returned_to_ibua';

        if ($roleCode === 'perpajakan' || $roleCode === 'akutansi') {
            $originalSender = 'ibuB';
            $returnStatus = 'returned_to_department';
            $this->department_returned_at = now();
            $this->target_department = $roleCode;
            $this->department_return_reason = $reason;
        }

        $this->current_handler = $originalSender;
        $this->status = $returnStatus;

        $this->save();

        // Event firing
        // Removed here as it is fired in rejectFromRoleInbox
        // event(new \App\Events\DocumentRejectedInbox($this, $reason, $roleCode));
    }

    /**
     * Helper untuk menampilkan status yang benar ke Ibu Tarapul
     * Memeriksa milestone historical sebelum menampilkan current status
     */
    public function getIbuTarapulStatusDisplay()
    {
        // Prioritaskan milestone historical PERMANENT
        if ($this->approved_by_ibub_at) {
            return 'Document Approved'; // ✅ PERMANENT MILESTONE - TIDAK AKAN TERGANGGU REJECT
        }

        if ($this->approved_by_perpajakan_at) {
            return 'Approved by Perpajakan';
        }

        if ($this->approved_by_akutansi_at) {
            return 'Approved by Akutansi';
        }

        // Jika ada milestone, gunakan itu - jangan overwrite dengan current status!
        if ($this->approved_by_ibub_at || $this->approved_by_perpajakan_at || $this->approved_by_akutansi_at) {
            // Cari status milestone yang sesuai
            $milestoneStatuses = [
                'approved_data_sudah_terkirim' => 'Document Approved',
                'approved_ibub' => 'Approved by Ibu Yuni',
                'approved_perpajakan' => 'Approved by Perpajakan',
                'approved_akutansi' => 'Approved by Akutansi',
                'selesai' => 'Document Selesai'
            ];

            return $milestoneStatuses[$this->status] ?? 'Status Unknown';
        }

        // Fallback ke current status dengan logic yang bersih
        return $this->getStatusDisplay();
    }

    /**
     * Get status display name in Indonesian
     * Helper method untuk menampilkan status dalam format yang user-friendly
     */
    public function getStatusDisplay(): string
    {
        $statusMap = [
            'draft' => 'Draft',
            'sedang diproses' => 'Sedang Diproses',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'pending_approval_ibub' => 'Menunggu Persetujuan Ibu Yuni',
            'sent_to_ibub' => 'Terkirim ke Ibu Yuni',
            'proses_ibub' => 'Diproses Ibu Yuni',
            'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
            'proses_perpajakan' => 'Diproses Team Perpajakan',
            'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
            'proses_akutansi' => 'Diproses Team Akutansi',
            'menunggu_approved_pengiriman' => 'Menunggu Persetujuan Pengiriman',
            'proses_pembayaran' => 'Diproses Team Pembayaran',
            'sent_to_pembayaran' => 'Terkirim ke Team Pembayaran',
            'approved_data_sudah_terkirim' => 'Data Sudah Terkirim',
            'rejected_data_tidak_lengkap' => 'Ditolak - Data Tidak Lengkap',
            'selesai' => 'Selesai',
            'returned_to_ibua' => 'Dikembalikan ke Ibu Tarapul',
            'returned_to_department' => 'Dikembalikan ke Department',
            'returned_to_bidang' => 'Dikembalikan ke Bidang',
            'returned_from_ibub' => 'Dikembalikan dari Ibu Yuni',
            'returned_from_perpajakan' => 'Dikembalikan dari Perpajakan',
            'returned_from_akutansi' => 'Dikembalikan dari Akutansi',
        ];

        return $statusMap[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Helper untuk menampilkan status yang benar ke Ibu Tarapul
     * Milestone-aware status display untuk mencegah kesalahan architcktural
     */
    public function getCorrectStatusDisplay()
    {
        // Jika ada milestone historical, gunakan itu
        if ($this->approved_by_ibub_at) {
            return 'Document Approved'; // ✅ MILESTONE SELALU BENAR
        }

        if ($this->approved_by_perpajakan_at) {
            return 'Approved by Perpajakan';
        }

        if ($this->approved_by_akutansi_at) {
            return 'Approved by Akutansi';
        }

        // Fallback ke status logic yang bersih tanpa overwrite
        $statusMapping = [
            'draft' => 'Draft',
            'sedang diproses' => 'Sedang Diproses',
            'approved_data_sudah_terkirim' => 'Document Approved',
            'approved_perpajakan' => 'Approved by Perpajakan',
            'approved_akutansi' => 'Approved by Akutansi',
            'selesai' => 'Selesai',
            'returned_to_ibua' => 'Dikembalikan ke Ibu Tarapul',
            'returned_to_department' => 'Dikembalikan ke Bagian',
            'rejected_ibub' => 'Ditolak oleh Ibu Yuni',
            'rejected_data_tidik_lengkap' => 'Ditolak (Data Tidak Lengkap)',
            'returned_from_perpajakan' => 'Dikembalikan dari Perpajakan',
            'returned_from_akutansi' => 'Dikembalikan dari Akutansi',
            'menunggu_di_approve' => 'Menunggu Approve Ibu Yuni',
            'menunggu_di_approve_perpajakan' => 'Menunggu Approve Perpajakan',
            'menunggu_di_approve_akutansi' => 'Menunggu Approve Akutansi',
        ];

        return $statusMapping[$this->status] ?? 'Status Unknown';
    }

    /**
     * Helper untuk mendapatkan informasi progress
     */

    /**
     * Get status display based on user role (Role-Based Status Visibility)
     * 
     * @param string|null $userRole User role (ibuA, ibuB, perpajakan, akutansi, pembayaran)
     * @return string Status label dalam bahasa Indonesia
     */
    public function getStatusForUserAttribute(?string $userRole = null): string
    {
        // Get user role from auth if not provided
        if (!$userRole && auth()->check()) {
            $user = auth()->user();
            $userRole = $user->role ?? null;

            // Normalize role
            if ($userRole) {
                $roleMap = [
                    'IbuA' => 'ibuA',
                    'Ibu A' => 'ibuA',
                    'ibuA' => 'ibuA',
                    'IbuB' => 'ibuB',
                    'Ibu B' => 'ibuB',
                    'ibuB' => 'ibuB',
                    'Ibu Yuni' => 'ibuB',
                    'Perpajakan' => 'perpajakan',
                    'perpajakan' => 'perpajakan',
                    'Akutansi' => 'akutansi',
                    'akutansi' => 'akutansi',
                    'Pembayaran' => 'pembayaran',
                    'pembayaran' => 'pembayaran',
                ];
                $userRole = $roleMap[$userRole] ?? strtolower($userRole);
            }
        }

        // Default to sender view if no role
        if (!$userRole) {
            $userRole = 'ibuA';
        }

        // SENDER VIEW (Ibu Tarapul / ibuA)
        if ($userRole === 'ibuA') {
            // PRIORITY CHECK: If approved by reviewer (Ibu Yuni) - status becomes "Terkirim" for sender
            // This check must be done FIRST before any other status checks
            // to ensure documents approved by Ibu Yuni always show as "Terkirim" for Ibu Tarapul
            if ($this->inbox_approval_for === 'IbuB' && $this->inbox_approval_status === 'approved') {
                return 'Terkirim';
            }

            // Check if document has passed sender stage (milestone check)
            if ($this->sent_to_ibub_at) {
                // Document has been sent to reviewer
                // Check if there's a rejection from later stages
                if ($this->last_action_status && strpos($this->last_action_status, 'rejected') !== false) {
                    // Check which stage rejected
                    if ($this->current_stage === 'tax' && $this->last_action_status === 'rejected_by_tax') {
                        // Tax rejected, but sender doesn't need to know details
                        return 'Sedang Proses (Reviewer/Tax)';
                    } elseif ($this->current_stage === 'accounting' && $this->last_action_status === 'rejected_by_accounting') {
                        return 'Sedang Proses (Reviewer/Accounting)';
                    }
                }

                // If document is at reviewer stage waiting approval
                if ($this->status === 'waiting_reviewer_approval' || ($this->inbox_approval_for === 'IbuB' && $this->inbox_approval_status === 'pending')) {
                    return 'Menunggu Approval Reviewer';
                }

                // If moved to next stages (Tax/Accounting) after reviewer approval
                if ($this->status === 'sent_to_perpajakan' || $this->status === 'sent_to_akutansi') {
                    return 'Sedang Proses';
                }

                // If status is 'sedang diproses', check inbox status
                // Note: This should not return 'Sedang Proses' if already approved (handled above)
                if ($this->status === 'sedang diproses') {
                    // Double check: if inbox was approved, it means reviewer already approved
                    if ($this->inbox_approval_status === 'approved' && $this->inbox_approval_for === 'IbuB') {
                        return 'Terkirim';
                    }
                    // Only show 'Sedang Proses' if NOT yet approved
                    return 'Menunggu Approval Reviewer';
                }

                // If returned to sender
                if ($this->status === 'returned_to_ibua') {
                    return 'Dikembalikan untuk Revisi';
                }

                // If completed
                if ($this->status === 'selesai' || $this->status === 'completed') {
                    return 'Selesai';
                }
            }

            // Default status mapping for sender
            $senderStatusMap = [
                'draft' => 'Draft',
                'waiting_reviewer_approval' => 'Menunggu Approval Reviewer',
                'menunggu_di_approve' => 'Menunggu Approval',
                'sent_to_ibub' => 'Terkirim',
                'returned_to_ibua' => 'Dikembalikan untuk Revisi',
                'selesai' => 'Selesai',
                'completed' => 'Selesai',
            ];

            // Check if status exists in map
            if (isset($senderStatusMap[$this->status])) {
                return $senderStatusMap[$this->status];
            }

            // For 'sedang diproses', check if it's after reviewer approval
            if ($this->status === 'sedang diproses') {
                // If inbox was approved by Ibu Yuni, show as "Terkirim"
                $ibuBStatus = $this->getStatusForRole('ibub');
                if ($ibuBStatus && $ibuBStatus->status === 'approved') {
                    return 'Terkirim';
                }
                // Otherwise, it's still waiting for approval
                return 'Menunggu Approval Reviewer';
            }

            // Final fallback: if document was sent to IbuB but status is unclear, check approval
            if ($this->inbox_approval_for === 'IbuB') {
                if ($this->inbox_approval_status === 'approved') {
                    return 'Terkirim';
                } elseif ($this->inbox_approval_status === 'pending') {
                    return 'Menunggu Approval Reviewer';
                }
            }

            return 'Sedang Proses';
        }

        // REVIEWER VIEW (Ibu Yuni / ibuB)
        if ($userRole === 'ibuB') {
            // Check if document is waiting for reviewer approval
            if ($this->status === 'waiting_reviewer_approval' || ($this->inbox_approval_for === 'IbuB' && $this->inbox_approval_status === 'pending')) {
                return 'Menunggu Approval';
            }

            // Check if there's rejection from later stages
            if ($this->last_action_status === 'rejected_by_tax') {
                return 'Ditolak Tax (Perlu Revisi)';
            }

            if ($this->last_action_status === 'rejected_by_accounting') {
                return 'Ditolak Accounting (Perlu Revisi)';
            }

            // If approved and moved forward
            if ($this->status === 'sedang diproses' || $this->status === 'sent_to_perpajakan' || $this->status === 'sent_to_akutansi') {
                return 'Terkirim/Approved';
            }

            // Reviewer-specific status mapping
            $reviewerStatusMap = [
                'waiting_reviewer_approval' => 'Menunggu Approval',
                'menunggu_di_approve' => 'Menunggu Approval',
                'sedang diproses' => 'Terkirim/Approved',
                'sent_to_perpajakan' => 'Terkirim ke Tax',
                'sent_to_akutansi' => 'Terkirim ke Accounting',
                'returned_to_ibub' => 'Dikembalikan',
            ];

            return $reviewerStatusMap[$this->status] ?? $this->getStatusDisplay();
        }

        // TAX VIEW (Perpajakan)
        if ($userRole === 'perpajakan') {
            // Check if waiting approval
            if ($this->inbox_approval_for === 'Perpajakan' && $this->inbox_approval_status === 'pending') {
                return 'Menunggu Approval';
            }

            // Tax-specific status mapping
            $taxStatusMap = [
                'sent_to_perpajakan' => 'Sedang Diproses',
                'sedang diproses' => 'Sedang Diproses',
                'returned_to_department' => 'Dikembalikan',
            ];

            return $taxStatusMap[$this->status] ?? $this->getStatusDisplay();
        }

        // ACCOUNTING VIEW (Akutansi)
        if ($userRole === 'akutansi') {
            // Check if waiting approval
            if ($this->inbox_approval_for === 'Akutansi' && $this->inbox_approval_status === 'pending') {
                return 'Menunggu Approval';
            }

            // Accounting-specific status mapping
            $accountingStatusMap = [
                'sent_to_akutansi' => 'Sedang Diproses',
                'sedang diproses' => 'Sedang Diproses',
                'returned_to_department' => 'Dikembalikan',
            ];

            return $accountingStatusMap[$this->status] ?? $this->getStatusDisplay();
        }

        // Default: use general status display
        return $this->getStatusDisplay();
    }

    /**
     * Helper method to get user role from authenticated user
     */
    private function getCurrentUserRole(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();

        if (isset($user->role)) {
            return strtolower($user->role);
        }

        return null;
    }

    /**
     * Get status for current authenticated user (convenience method)
     * Can be called as: $dokumen->getStatusForUser() or $dokumen->status_for_user
     * 
     * @param string|null $userRole Optional user role, if not provided will use authenticated user
     * @return string Status label dalam bahasa Indonesia
     */
    public function getStatusForUser(?string $userRole = null): string
    {
        return $this->getStatusForUserAttribute($userRole);
    }
}