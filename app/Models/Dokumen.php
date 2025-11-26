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
        // Pembayaran fields
        'sent_to_pembayaran_at',
        'status_pembayaran',
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
        // Pembayaran casts
        'sent_to_pembayaran_at' => 'datetime',
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
        $this->inbox_approval_for = $recipientRole;
        $this->inbox_approval_status = 'pending';
        $this->inbox_approval_sent_at = now();
        $this->inbox_original_status = $this->status;
        $this->status = 'menunggu_di_approve';
        
        // Clear rejection fields jika dokumen dikirim kembali ke inbox
        // (dokumen yang sebelumnya di-reject sekarang dikirim ulang)
        $this->inbox_approval_reason = null;
        $this->inbox_approval_responded_at = null;
        
        $this->save();

        // Log activity
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $recipientRole,
            'action' => 'inbox_sent',
            'action_description' => "Dokumen dikirim ke inbox {$recipientRole} menunggu persetujuan",
            'performed_by' => auth()->user()->name ?? auth()->user()->role ?? 'System',
            'action_at' => now(),
            'details' => [
                'recipient_role' => $recipientRole,
                'original_status' => $this->inbox_original_status,
            ]
        ]);

        // Fire event
        event(new \App\Events\DocumentSentToInbox($this, $recipientRole));
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
            $statusMap = [
                'IbuB' => 'sent_to_ibub',
                'Perpajakan' => 'sent_to_perpajakan',
                'Akutansi' => 'sent_to_akutansi',
            ];
            
            $this->status = $statusMap[$this->inbox_approval_for] ?? $this->inbox_original_status ?? 'diterima';
            
            // Set timestamp sesuai recipient
            if ($this->inbox_approval_for === 'IbuB') {
                $this->sent_to_ibub_at = now();
                $this->processed_at = now();
            } elseif ($this->inbox_approval_for === 'Perpajakan') {
                $this->sent_to_perpajakan_at = now();
            } elseif ($this->inbox_approval_for === 'Akutansi') {
                // Note: sent_to_akutansi_at field might not exist, but we set processed_at
                $this->processed_at = now();
            }
        } else {
            // Fallback ke status original jika role tidak dikenali
            $this->status = $this->inbox_original_status ?? 'diterima';
        }

        $this->save();

        // Log approval
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $this->inbox_approval_for,
            'action' => 'inbox_approved',
            'action_description' => "Dokumen disetujui di inbox {$this->inbox_approval_for}",
            'performed_by' => auth()->user()->name ?? auth()->user()->role ?? 'System',
            'action_at' => now(),
            'details' => [
                'approved_by' => auth()->user()->name ?? auth()->user()->role ?? 'System',
            ]
        ]);

        // Fire event
        event(new \App\Events\DocumentApprovedInbox($this));
    }

    /**
     * Reject document from inbox
     */
    public function rejectInbox($reason)
    {
        // Simpan recipient role sebelum di-clear
        $inboxRecipient = $this->inbox_approval_for;
        
        $this->inbox_approval_status = 'rejected';
        $this->inbox_approval_reason = $reason;
        $this->inbox_approval_responded_at = now();

        // Tentukan pengirim asli berdasarkan inbox_approval_for
        // Jika ditolak dari IbuB, kembali ke IbuA
        // Jika ditolak dari Perpajakan, kembali ke IbuB
        // Jika ditolak dari Akutansi, kembali ke Perpajakan
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
            // Ditolak dari Akutansi, kembali ke Perpajakan
            $originalSender = 'perpajakan';
            $returnStatus = 'returned_from_akutansi';
            $this->returned_from_akutansi_at = now();
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
        $this->current_handler = $originalSender;
        $this->status = $returnStatus;

        // JANGAN clear inbox_approval_for dan inbox_approval_sent_at
        // Biarkan tetap ada agar bisa ditampilkan sebagai "dokumen ditolak, alasan"
        // Hanya clear jika dokumen dikirim kembali ke inbox (di method sendToInbox)

        $this->save();

        // Log rejection
        DokumenActivityLog::create([
            'dokumen_id' => $this->id,
            'stage' => $inboxRecipient ?? 'inbox',
            'action' => 'inbox_rejected',
            'action_description' => "Dokumen ditolak di inbox {$inboxRecipient} dan dikembalikan ke {$originalSender}. Alasan: {$reason}",
            'performed_by' => auth()->user()->name ?? auth()->user()->role ?? 'System',
            'action_at' => now(),
            'details' => [
                'rejection_reason' => $reason,
                'rejected_by' => auth()->user()->name ?? auth()->user()->role ?? 'System',
                'returned_to' => $originalSender,
                'original_inbox_recipient' => $inboxRecipient,
            ]
        ]);

        // Fire event
        event(new \App\Events\DocumentRejectedInbox($this, $reason));
    }
}