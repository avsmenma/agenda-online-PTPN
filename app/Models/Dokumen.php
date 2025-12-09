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
        'target_department',
        'department_returned_at',
        'department_return_reason',
        'target_bidang',
        'bidang_returned_at',
        'bidang_return_reason',
        'created_by',
        'current_handler',
        'current_stage',
        'last_action_status',
        'sent_to_ibub_at',
        'processed_at',
        'returned_to_ibua_at',
        'deadline_at',
        'deadline_days',
        'deadline_note',
        'deadline_completed_at',
        // Approval System fields
        'pending_approval_for',
        'pending_approval_at',
        'approval_responded_at',
        'approval_responded_by',
        'approval_rejection_reason',
        // Perpajakan fields
        'npwp',
        'status_perpajakan',
        'no_faktur',
        'tanggal_faktur',
        'tanggal_selesai_verifikasi_pajak',
        'jenis_pph',
        'dpp_pph',
        'ppn_terhutang',
        'link_dokumen_pajak',
        'deadline_perpajakan_at',
        'deadline_perpajakan_days',
        'deadline_perpajakan_note',
        'sent_to_perpajakan_at',
        'processed_perpajakan_at',
        'returned_from_perpajakan_at',
        'returned_from_akutansi_at',
        // Pembayaran fields
        'sent_to_pembayaran_at',
        'status_pembayaran',
        'tanggal_dibayar',
        'link_bukti_pembayaran',
        // Universal Approval System fields
        'universal_approval_for',
        'universal_approval_sent_at',
        'universal_approval_responded_at',
        'universal_approval_responded_by',
        'universal_approval_rejection_reason',
        // Inbox Approval System fields
        'inbox_approval_for',
        'inbox_approval_status',
        'inbox_approval_sent_at',
        'inbox_approval_responded_at',
        'inbox_approval_reason',
        'inbox_original_status',
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
        'tanggal_masuk' => 'datetime',
        'tanggal_spp' => 'datetime',
        'tanggal_berita_acara' => 'date',
        'tanggal_spk' => 'date',
        'tanggal_berakhir_spk' => 'date',
        'nilai_rupiah' => 'decimal:2',
        'sent_to_ibub_at' => 'datetime',
        'processed_at' => 'datetime',
        'returned_to_ibua_at' => 'datetime',
        'department_returned_at' => 'datetime',
        'bidang_returned_at' => 'datetime',
        'deadline_at' => 'datetime',
        'deadline_completed_at' => 'datetime',
        // Approval System casts
        'pending_approval_at' => 'datetime',
        'approval_responded_at' => 'datetime',
        // Perpajakan casts
        'tanggal_faktur' => 'date',
        'tanggal_selesai_verifikasi_pajak' => 'date',
        'dpp_pph' => 'decimal:2',
        'ppn_terhutang' => 'decimal:2',
        'deadline_perpajakan_at' => 'datetime',
        'sent_to_perpajakan_at' => 'datetime',
        'processed_perpajakan_at' => 'datetime',
        'returned_from_perpajakan_at' => 'datetime',
        'returned_from_akutansi_at' => 'datetime',
        // Pembayaran casts
        'sent_to_pembayaran_at' => 'datetime',
        'tanggal_dibayar' => 'date',
        // Universal Approval System casts
        'universal_approval_sent_at' => 'datetime',
        'universal_approval_responded_at' => 'datetime',
        // Inbox Approval System casts
        'inbox_approval_sent_at' => 'datetime',
        'inbox_approval_responded_at' => 'datetime',
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
        return 'Rp. ' . number_format($this->nilai_rupiah, 0, ',', '.');
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
     * Helper methods untuk Universal Approval System
     */

    /**
     * Cek apakah dokumen sedang menunggu universal approval
     */
    public function isWaitingUniversalApproval(): bool
    {
        return $this->status === 'menunggu_approved_pengiriman' && !is_null($this->universal_approval_for);
    }

    /**
     * Cek apakah dokumen menunggu approval untuk role tertentu
     */
    public function isWaitingApprovalFor(string $role): bool
    {
        return $this->isWaitingUniversalApproval() && $this->universal_approval_for === $role;
    }

    /**
     * Cek apakah dokumen sudah di-approve universal
     */
    public function isUniversalApproved(): bool
    {
        return $this->status === 'approved_data_sudah_terkirim';
    }

    /**
     * Cek apakah dokumen di-reject universal
     */
    public function isUniversalRejected(): bool
    {
        return $this->status === 'rejected_data_tidak_lengkap';
    }

    /**
     * Get semua role yang bisa menerima universal approval
     */
    public static function getUniversalApprovalRoles(): array
    {
        return [
            'ibuB' => 'Ibu B',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
        ];
    }

    /**
     * Get status display untuk universal approval
     */
    public function getUniversalApprovalStatusDisplay(): string
    {
        $statusMap = [
            'menunggu_approved_pengiriman' => 'Menunggu Approve Pengiriman',
            'approved_data_sudah_terkirim' => 'Approve, Data Sudah Terkirim',
            'rejected_data_tidak_lengkap' => 'Reject, Data Tidak Lengkap',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Get nama role yang lebih user-friendly
     */
    public function getUniversalApprovalForDisplay(): string
    {
        $roles = self::getUniversalApprovalRoles();
        return $roles[$this->universal_approval_for] ?? $this->universal_approval_for;
    }

    /**
     * Get user yang mengirim dokumen (creator display name)
     */
    public function getSenderDisplayName(): string
    {
        // Jika dokumen masuk via inbox, cari pengirim dari activity log
        if ($this->inbox_approval_status == 'pending' || $this->inbox_approval_sent_at) {
            // Cari activity log untuk inbox_sent
            $sentLog = $this->activityLogs()
                ->where('action', 'inbox_sent')
                ->where('stage', $this->inbox_approval_for)
                ->latest('action_at')
                ->first();
            
            if ($sentLog) {
                $performedBy = $sentLog->performed_by ?? $sentLog->details['performed_by'] ?? null;
                
                // Map role/name ke display name
                $nameMap = [
                    'ibuA' => 'Ibu Tarapul',
                    'IbuA' => 'Ibu Tarapul',
                    'Ibu A' => 'Ibu Tarapul',
                    'ibuB' => 'Ibu Yuni',
                    'IbuB' => 'Ibu Yuni',
                    'Ibu B' => 'Ibu Yuni',
                    'Ibu Yuni' => 'Ibu Yuni',
                    'perpajakan' => 'Team Perpajakan',
                    'Perpajakan' => 'Team Perpajakan',
                    'akutansi' => 'Team Akutansi',
                    'Akutansi' => 'Team Akutansi',
                ];
                
                if ($performedBy && isset($nameMap[$performedBy])) {
                    return $nameMap[$performedBy];
                }
                
                // Jika tidak ada di map, coba dari details
                $recipientRole = $sentLog->details['recipient_role'] ?? null;
                if ($recipientRole) {
                    // Jika recipient adalah Perpajakan, sender kemungkinan IbuB
                    if ($recipientRole === 'Perpajakan') {
                        return 'Ibu Yuni';
                    }
                    // Jika recipient adalah Akutansi, sender kemungkinan Perpajakan
                    if ($recipientRole === 'Akutansi') {
                        return 'Team Perpajakan';
                    }
                }
            }
            
            // Fallback: jika inbox_approval_for adalah Perpajakan, sender adalah IbuB
            if ($this->inbox_approval_for === 'Perpajakan') {
                return 'Ibu Yuni';
            }
            // Jika inbox_approval_for adalah Akutansi, sender adalah Perpajakan
            if ($this->inbox_approval_for === 'Akutansi') {
                return 'Team Perpajakan';
            }
        }
        
        // Default: gunakan created_by
        $senderMap = [
            'ibuA' => 'Ibu Tarapul',
            'ibuB' => 'Ibu Yuni',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Team Pembayaran',
        ];

        return $senderMap[$this->created_by] ?? $this->created_by;
    }

    /**
     * Inbox Approval System Methods
     */

    /**
     * Send document to inbox for approval
     */
    public function sendToInbox($recipientRole)
    {
        // Normalize recipient role to match enum values (IbuB, Perpajakan, Akutansi)
        $roleMap = [
            'IbuB' => 'IbuB',
            'ibuB' => 'IbuB',
            'Ibu B' => 'IbuB',
            'Ibu Yuni' => 'IbuB',
            'Perpajakan' => 'Perpajakan',
            'perpajakan' => 'Perpajakan',
            'Akutansi' => 'Akutansi',
            'akutansi' => 'Akutansi',
        ];
        $normalizedRole = $roleMap[$recipientRole] ?? $recipientRole;
        
        \Log::info('sendToInbox called', [
            'document_id' => $this->id,
            'original_recipient_role' => $recipientRole,
            'normalized_role' => $normalizedRole,
            'current_status' => $this->status,
        ]);
        
        $this->inbox_approval_for = $normalizedRole;
        $this->inbox_approval_status = 'pending';
        $this->inbox_approval_sent_at = now();
        $this->inbox_original_status = $this->status;

        // Set current_stage based on recipient role
        $stageMap = [
            'IbuB' => 'reviewer',
            'Perpajakan' => 'tax',
            'Akutansi' => 'accounting',
        ];
        $this->current_stage = $stageMap[$normalizedRole] ?? $this->current_stage;
        // Set last_action_status based on normalized role
        $actionStatusMap = [
            'IbuB' => 'sent_to_reviewer',
            'Perpajakan' => 'sent_to_tax',
            'Akutansi' => 'sent_to_accounting',
        ];
        $this->last_action_status = $actionStatusMap[$normalizedRole] ?? 'sent_to_' . strtolower($normalizedRole);

        // Set status based on recipient role (Approval Gate)
        // For Reviewer (IbuB), status should be WAITING_REVIEWER_APPROVAL
        // This will be set by sendToIbuB() method, but we set it here as fallback
        if ($normalizedRole === 'IbuB' && $this->status !== 'waiting_reviewer_approval') {
            // Only set if not already set by sendToIbuB()
            $this->status = 'waiting_reviewer_approval';
        } elseif ($normalizedRole === 'Perpajakan' || $normalizedRole === 'Akutansi') {
            // For other roles, use menunggu_di_approve
            // Don't overwrite milestone statuses
            if (!in_array($this->status, ['approved_ibub', 'approved_data_sudah_terkirim', 'approved_perpajakan', 'approved_akutansi'])) {
                $this->status = 'menunggu_di_approve';
            }
        }
        
        // Clear rejection fields jika dokumen dikirim kembali ke inbox
        // (dokumen yang sebelumnya di-reject sekarang dikirim ulang)
        $this->inbox_approval_reason = null;
        $this->inbox_approval_responded_at = null;
        
        $this->save();
        
        \Log::info('sendToInbox completed', [
            'document_id' => $this->id,
            'inbox_approval_for' => $this->inbox_approval_for,
            'inbox_approval_status' => $this->inbox_approval_status,
            'inbox_approval_sent_at' => $this->inbox_approval_sent_at,
            'status' => $this->status,
        ]);

        // Log activity
        $performedBy = 'System';
        if (auth()->check() && auth()->user()) {
            $user = auth()->user();
            $performedBy = $user->name ?? $user->role ?? 'System';
        }
        
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $recipientRole,
            'action' => 'inbox_sent',
            'action_description' => "Dokumen dikirim ke inbox {$recipientRole} menunggu persetujuan",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'recipient_role' => $recipientRole,
                'original_status' => $this->inbox_original_status,
            ]
        ]);

        // Fire event
        try {
            \Log::info('Firing DocumentSentToInbox event', [
                'document_id' => $this->id,
                'recipient_role' => $recipientRole,
                'inbox_approval_status' => $this->inbox_approval_status,
                'inbox_approval_sent_at' => $this->inbox_approval_sent_at,
            ]);

            event(new \App\Events\DocumentSentToInbox($this, $recipientRole));

            \Log::info('DocumentSentToInbox event fired successfully', [
                'document_id' => $this->id,
                'recipient_role' => $recipientRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fire DocumentSentToInbox event: ' . $e->getMessage(), [
                'document_id' => $this->id,
                'recipient_role' => $recipientRole,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Approve document from inbox
     */
    public function approveInbox()
    {
        $this->inbox_approval_status = 'approved';
        $this->inbox_approval_responded_at = now();

        // Update current_handler berdasarkan recipient role
        $handlerMap = [
            'IbuB' => 'ibuB',
            'Perpajakan' => 'perpajakan',
            'Akutansi' => 'akutansi',
        ];
        
        if (isset($handlerMap[$this->inbox_approval_for])) {
            $this->current_handler = $handlerMap[$this->inbox_approval_for];
            
            // Update status sesuai dengan recipient role
            // Untuk IbuB, setelah approve dari inbox, status menjadi 'sedang diproses' (bukan 'sent_to_ibub')
            // Untuk Akutansi, gunakan alur yang sama dengan Perpajakan (fix: field sent_to_akutansi tidak ada)
            $statusMap = [
                'IbuB' => 'sedang diproses',  // Ubah dari 'sent_to_ibub' ke 'sedang diproses'
                'Perpajakan' => 'sent_to_perpajakan',
                'Akutansi' => 'sent_to_perpajakan',  // FIX: Gunakan alur Perpajakan yang sudah valid
            ];
            
            $this->status = $statusMap[$this->inbox_approval_for] ?? $this->inbox_original_status ?? 'diterima';
            
            // Set timestamp sesuai recipient
            if ($this->inbox_approval_for === 'IbuB') {
                // Only set sent_to_ibub_at if it's null (first time entering IbuB)
                // This preserves the original entry time for consistent ordering
                if (is_null($this->sent_to_ibub_at)) {
                    $this->sent_to_ibub_at = now();
                }
                // Clear deadline untuk memastikan dokumen terlock sampai deadline di-set
                $this->deadline_at = null;
                $this->deadline_days = null;
                $this->deadline_note = null;
                // Jangan set processed_at karena dokumen masih perlu diproses
                // $this->processed_at = now();
            } elseif ($this->inbox_approval_for === 'Perpajakan') {
                $this->sent_to_perpajakan_at = now();
                // Clear deadline untuk memastikan dokumen terlock sampai deadline di-set
                $this->deadline_at = null;
                $this->deadline_days = null;
                $this->deadline_note = null;
                $this->deadline_perpajakan_at = null;
                $this->deadline_perpajakan_days = null;
                $this->deadline_perpajakan_note = null;
            } elseif ($this->inbox_approval_for === 'Akutansi') {
                // FIX: Gunakan alur yang sama dengan Perpajakan (field valid)
                $this->sent_to_perpajakan_at = now();
                // Clear deadline untuk memastikan dokumen terlock sampai deadline di-set
                $this->deadline_at = null;
                $this->deadline_days = null;
                $this->deadline_note = null;
                $this->deadline_perpajakan_at = null;
                $this->deadline_perpajakan_days = null;
                $this->deadline_perpajakan_note = null;
            }
        } else {
            // Fallback ke status original jika role tidak dikenali
            $this->status = $this->inbox_original_status ?? 'diterima';
        }

        $this->save();

        // Log approval
        $performedBy = 'System';
        $approvedBy = 'System';
        if (auth()->check() && auth()->user()) {
            $user = auth()->user();
            $performedBy = $user->name ?? $user->role ?? 'System';
            $approvedBy = $user->name ?? $user->role ?? 'System';
        }
        
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $this->inbox_approval_for,
            'action' => 'inbox_approved',
            'action_description' => "Dokumen disetujui di inbox {$this->inbox_approval_for}",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'approved_by' => $approvedBy,
            ]
        ]);

        // Fire event
        event(new \App\Events\DocumentApprovedInbox($this));
    }

    /**
     * Reject document from inbox
     * Updated to use last_action_status instead of overwriting global status
     */
    public function rejectInbox($reason)
    {
        // Simpan recipient role sebelum di-clear
        $inboxRecipient = $this->inbox_approval_for;
        
        $this->inbox_approval_status = 'rejected';
        $this->inbox_approval_reason = $reason;
        $this->inbox_approval_responded_at = now();

        // Set last_action_status based on who rejected (for role-based visibility)
        $rejectionStatusMap = [
            'IbuB' => 'rejected_by_reviewer',
            'Perpajakan' => 'rejected_by_tax',
            'Akutansi' => 'rejected_by_accounting',
        ];
        $this->last_action_status = $rejectionStatusMap[$inboxRecipient] ?? 'rejected';
        
        // Update current_stage to reflect rejection
        // Keep current_stage to show where rejection happened
        $stageMap = [
            'IbuB' => 'reviewer',
            'Perpajakan' => 'tax',
            'Akutansi' => 'accounting',
        ];
        $this->current_stage = $stageMap[$inboxRecipient] ?? $this->current_stage;

        // Tentukan pengirim asli berdasarkan inbox_approval_for
        // Jika ditolak dari IbuB, kembali ke IbuA
        // Jika ditolak dari Perpajakan, kembali ke IbuB
        // Jika ditolak dari Akutansi, kembali ke IbuB
        $originalSender = null;
        $returnStatus = null;
        
        if ($inboxRecipient === 'IbuB') {
            // Ditolak dari IbuB, kembali ke IbuA
            $originalSender = 'ibuA';
            $returnStatus = 'returned_to_ibua';
            $this->returned_to_ibua_at = now();
        } elseif ($inboxRecipient === 'Perpajakan') {
            // Ditolak dari Perpajakan, kembali ke IbuB
            $originalSender = 'ibuB';
            $returnStatus = 'returned_to_department';
            $this->department_returned_at = now();
            $this->target_department = 'perpajakan';
            $this->department_return_reason = $reason;
        } elseif ($inboxRecipient === 'Akutansi') {
            // Ditolak dari Akutansi, kembali ke IbuB (pengirim asli)
            $originalSender = 'ibuB';
            $returnStatus = 'returned_to_department';
            $this->department_returned_at = now();
            $this->target_department = 'akutansi';
            $this->department_return_reason = $reason;
        } else {
            // Fallback: gunakan created_by
            $originalSender = $this->created_by ?? 'ibuA';
            if ($originalSender === 'ibuA') {
                $returnStatus = 'returned_to_ibua';
                $this->returned_to_ibua_at = now();
            } else {
                $returnStatus = $this->inbox_original_status ?? 'draft';
            }
        }
        
        // Kembalikan ke pengirim dengan status yang sesuai
        // IMPORTANT: Only update status if rejected by Reviewer (IbuB)
        // For other rejections, keep the status but use last_action_status for visibility
        if ($inboxRecipient === 'IbuB') {
            // If Reviewer rejects, update status to returned_to_ibua
            $this->current_handler = $originalSender;
            $this->status = $returnStatus;
        } else {
            // If Tax/Accounting rejects, DON'T overwrite global status
            // Use last_action_status for role-based visibility instead
            // Only update current_handler to return to sender
            $this->current_handler = $originalSender;
            // Keep current status, but last_action_status will show rejection
            // This way, Sender sees "Sedang Proses" while Reviewer sees "Ditolak Tax"
        }

        // JANGAN clear inbox_approval_for dan inbox_approval_sent_at
        // Biarkan tetap ada agar bisa ditampilkan sebagai "dokumen ditolak, alasan"
        // Hanya clear jika dokumen dikirim kembali ke inbox (di method sendToInbox)

        $this->save();

        // Log rejection
        $performedBy = 'System';
        $rejectedBy = 'System';
        if (auth()->check() && auth()->user()) {
            $user = auth()->user();
            $performedBy = $user->name ?? $user->role ?? 'System';
            $rejectedBy = $user->name ?? $user->role ?? 'System';
        }
        
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $inboxRecipient ?? 'inbox',
            'action' => 'inbox_rejected',
            'action_description' => "Dokumen ditolak di inbox {$inboxRecipient} dan dikembalikan ke {$originalSender}. Alasan: {$reason}",
            'performed_by' => $performedBy,
            'action_at' => now(),
            'details' => [
                'rejection_reason' => $reason,
                'rejected_by' => $rejectedBy,
                'returned_to' => $originalSender,
                'original_inbox_recipient' => $inboxRecipient,
            ]
        ]);

        // Fire event
        event(new \App\Events\DocumentRejectedInbox($this, $reason));
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
                
                // If approved by reviewer (Ibu Yuni) - status becomes "Terkirim" for sender
                if ($this->inbox_approval_for === 'IbuB' && $this->inbox_approval_status === 'approved') {
                    return 'Terkirim';
                }
                
                // If moved to next stages (Tax/Accounting) after reviewer approval
                if ($this->status === 'sent_to_perpajakan' || $this->status === 'sent_to_akutansi') {
                    return 'Sedang Proses';
                }
                
                // If status is 'sedang diproses' but not yet approved by reviewer, check inbox status
                if ($this->status === 'sedang diproses') {
                    // If inbox was approved, it means reviewer already approved
                    if ($this->inbox_approval_status === 'approved' && $this->inbox_approval_for === 'IbuB') {
                        return 'Terkirim';
                    }
                    return 'Sedang Proses';
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
                if ($this->inbox_approval_status === 'approved' && $this->inbox_approval_for === 'IbuB') {
                    return 'Terkirim';
                }
                return 'Sedang Proses';
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