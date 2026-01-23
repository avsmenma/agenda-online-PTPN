<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenStatus extends Model
{
    use HasFactory;

    protected $table = 'dokumen_statuses';

    protected $fillable = [
        'dokumen_id',
        'role_code',
        'status',
        'status_changed_at',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RECEIVED = 'received';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_RETURNED = 'returned';

    /**
     * Get the dokumen that owns this status
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Get the role for this status
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_code', 'code');
    }

    /**
     * Scope: filter by role
     */
    public function scopeForRole($query, string $roleCode)
    {
        return $query->where('role_code', $roleCode);
    }

    /**
     * Scope: filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: pending documents for a role
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get status display name in Indonesian
     */
    public function getStatusDisplayAttribute(): string
    {
        $statusMap = [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_RECEIVED => 'Diterima',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_RETURNED => 'Dikembalikan',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Update status for this record
     */
    public function updateStatus(string $newStatus, ?string $changedBy = null, ?string $notes = null): self
    {
        $this->status = $newStatus;
        $this->status_changed_at = now();
        $this->changed_by = $changedBy ?? auth()->user()?->name ?? 'System';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }
}



