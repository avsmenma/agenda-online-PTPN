<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenRoleData extends Model
{
    use HasFactory;

    protected $table = 'dokumen_role_data';

    protected $fillable = [
        'dokumen_id',
        'role_code',
        'received_at',
        'processed_at',
        'deadline_at',
        'deadline_days',
        'deadline_note',
        'role_specific_data',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'deadline_at' => 'datetime',
        'role_specific_data' => 'array',
    ];

    /**
     * Get the dokumen that owns this data
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Get the role for this data
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
     * Get a specific field from role_specific_data
     */
    public function getRoleData(string $key, $default = null)
    {
        return data_get($this->role_specific_data, $key, $default);
    }

    /**
     * Set a specific field in role_specific_data
     */
    public function setRoleData(string $key, $value): self
    {
        $data = $this->role_specific_data ?? [];
        data_set($data, $key, $value);
        $this->role_specific_data = $data;
        return $this;
    }

    /**
     * Check if deadline is set
     */
    public function hasDeadline(): bool
    {
        return !is_null($this->deadline_at);
    }

    /**
     * Check if deadline is overdue
     */
    public function isOverdue(): bool
    {
        return $this->hasDeadline() && $this->deadline_at->isPast();
    }

    /**
     * Get days until deadline (negative if overdue)
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->hasDeadline()) {
            return null;
        }
        return now()->diffInDays($this->deadline_at, false);
    }

    /**
     * Set deadline
     */
    public function setDeadline(int $days, ?string $note = null): self
    {
        $this->deadline_days = $days;
        $this->deadline_at = now()->addDays($days);
        $this->deadline_note = $note;
        $this->save();

        return $this;
    }
}





