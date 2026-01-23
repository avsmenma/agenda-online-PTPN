<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'sequence',
    ];

    /**
     * Get all dokumen statuses for this role
     */
    public function dokumenStatuses(): HasMany
    {
        return $this->hasMany(DokumenStatus::class, 'role_code', 'code');
    }

    /**
     * Get all dokumen role data for this role
     */
    public function dokumenRoleData(): HasMany
    {
        return $this->hasMany(DokumenRoleData::class, 'role_code', 'code');
    }

    /**
     * Get all users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'code');
    }

    /**
     * Get role by code (case-insensitive)
     */
    public static function findByCode(string $code): ?self
    {
        return self::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
    }

    /**
     * Get display name for role
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }
}



