<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleDeadlineConfig extends Model
{
    protected $table = 'role_deadline_configs';

    protected $fillable = [
        'role_code',
        'default_deadline_days',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_deadline_days' => 'integer',
    ];

    /**
     * Get deadline config for a specific role
     */
    public static function getDeadlineDaysForRole(string $roleCode): int
    {
        $config = self::where('role_code', $roleCode)
            ->where('is_active', true)
            ->first();

        return $config ? $config->default_deadline_days : 3; // Default to 3 days if not found
    }
}







