<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'action',
        'actor',
        'metadata',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the document that owns the tracking entry.
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'document_id');
    }

    /**
     * Get all possible actions for tracking
     */
    public static function getAvailableActions(): array
    {
        return [
            'created' => 'Dokumen DOperatort',
            'sent_to_Team Verifikasi' => 'Dikirim ke Ibu B',
            'deadline_set' => 'Deadline Ditentukan',
            'processed_by_Team Verifikasi' => 'Diproses Ibu B',
            'returned_to_Operator' => 'Dikembalikan ke Ibu A',
            'sent_to_perpajakan' => 'Dikirim ke Perpajakan',
            'processed_perpajakan' => 'Diproses Perpajakan',
            'returned_from_perpajakan' => 'Dikembalikan dari Perpajakan',
            'sent_to_akutansi' => 'Dikirim ke Akutansi',
            'processed_akutansi' => 'Diproses Akutansi',
            'sent_to_pembayaran' => 'Dikirim ke Pembayaran',
            'processed_pembayaran' => 'Diproses Pembayaran',
            'universal_approval_sent' => 'Diajukan Approve Universal',
            'universal_approved' => 'Di-approve Universal',
            'universal_rejected' => 'Di-reject Universal',
            'deadline_completed' => 'Deadline Selesai',
            'deadline_overdue' => 'Deadline Terlewat',
        ];
    }

    /**
     * Get all possible actors
     */
    public static function getAvailableActors(): array
    {
        return [
            'operator' => 'Operator',
            'team_verifikasi' => 'Ibu B',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran',
            'system' => 'System',
        ];
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        $actions = self::getAvailableActions();
        return $actions[$this->action] ?? $this->action;
    }

    /**
     * Get actor display name
     */
    public function getActorDisplayAttribute(): string
    {
        $actors = self::getAvailableActors();
        return $actors[$this->actor] ?? $this->actor;
    }

    /**
     * Get formatted action time
     */
    public function getFormattedActionAtAttribute(): string
    {
        return $this->action_at->format('d M Y H:i');
    }

    /**
     * Get relative time for action
     */
    public function getRelativeTimeAttribute(): string
    {
        return $this->action_at->diffForHumans();
    }

    /**
     * Get action color class for UI
     */
    public function getActionColorClassAttribute(): string
    {
        $colorMap = [
            'created' => 'text-green-600 bg-green-50 border-green-200',
            'sent_to_Team Verifikasi' => 'text-blue-600 bg-blue-50 border-blue-200',
            'deadline_set' => 'text-yellow-600 bg-yellow-50 border-yellow-200',
            'processed_by_Team Verifikasi' => 'text-purple-600 bg-purple-50 border-purple-200',
            'returned_to_Operator' => 'text-red-600 bg-red-50 border-red-200',
            'sent_to_perpajakan' => 'text-indigo-600 bg-indigo-50 border-indigo-200',
            'processed_perpajakan' => 'text-indigo-600 bg-indigo-50 border-indigo-200',
            'returned_from_perpajakan' => 'text-red-600 bg-red-50 border-red-200',
            'sent_to_akutansi' => 'text-orange-600 bg-orange-50 border-orange-200',
            'processed_akutansi' => 'text-orange-600 bg-orange-50 border-orange-200',
            'sent_to_pembayaran' => 'text-teal-600 bg-teal-50 border-teal-200',
            'processed_pembayaran' => 'text-teal-600 bg-teal-50 border-teal-200',
            'universal_approval_sent' => 'text-yellow-600 bg-yellow-50 border-yellow-200',
            'universal_approved' => 'text-green-600 bg-green-50 border-green-200',
            'universal_rejected' => 'text-red-600 bg-red-50 border-red-200',
            'deadline_completed' => 'text-green-600 bg-green-50 border-green-200',
            'deadline_overdue' => 'text-red-600 bg-red-50 border-red-200',
        ];

        return $colorMap[$this->action] ?? 'text-gray-600 bg-gray-50 border-gray-200';
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        $iconMap = [
            'created' => 'fa-plus-circle',
            'sent_to_Team Verifikasi' => 'fa-paper-plane',
            'deadline_set' => 'fa-clock',
            'processed_by_Team Verifikasi' => 'fa-user-check',
            'returned_to_Operator' => 'fa-undo',
            'sent_to_perpajakan' => 'fa-file-invoice',
            'processed_perpajakan' => 'fa-stamp',
            'returned_from_perpajakan' => 'fa-exclamation-triangle',
            'sent_to_akutansi' => 'fa-calculator',
            'processed_akutansi' => 'fa-check-double',
            'sent_to_pembayaran' => 'fa-money-bill-wave',
            'processed_pembayaran' => 'fa-check-circle',
            'universal_approval_sent' => 'fa-paper-plane',
            'universal_approved' => 'fa-shield-check',
            'universal_rejected' => 'fa-times-circle',
            'deadline_completed' => 'fa-flag-checkered',
            'deadline_overdue' => 'fa-exclamation-circle',
        ];

        return $iconMap[$this->action] ?? 'fa-circle';
    }

    /**
     * Scope for recent actions (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('action_at', '>=', now()->subHours(24));
    }

    /**
     * Scope for actions by actor
     */
    public function scopeByActor($query, string $actor)
    {
        return $query->where('actor', $actor);
    }

    /**
     * Scope for actions by type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Create a tracking entry
     */
    public static function logAction(int $documentId, string $action, string $actor, array $metadata = null): self
    {
        return self::create([
            'document_id' => $documentId,
            'action' => $action,
            'actor' => $actor,
            'metadata' => $metadata,
            'action_at' => now(),
        ]);
    }
}



