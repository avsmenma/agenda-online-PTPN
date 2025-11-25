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
        $senderMap = [
            'ibuA' => 'Ibu A',
            'ibuB' => 'Ibu B',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
        ];

        return $senderMap[$this->created_by] ?? $this->created_by;
    }
}