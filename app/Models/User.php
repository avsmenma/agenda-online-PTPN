<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'Admin' => 'Admin',
        'IbuA' => 'Ibu A',
        'IbuB' => 'Ibu B',
        'Pembayaran' => 'Pembayaran',
        'Akutansi' => 'Akutansi',
        'Perpajakan' => 'Perpajakan',
        'Verifikasi' => 'Verifikasi',
    ];

    public const DASHBOARD_ROUTES = [
        'Admin' => '/owner/dashboard',
        'owner' => '/owner/dashboard',
        'Owner' => '/owner/dashboard',
        'IbuA' => '/dashboard',
        'IbuB' => '/dashboard/verifikasi',
        'Pembayaran' => '/dashboard/pembayaran',
        'Akutansi' => '/dashboard/akutansi',
        'Perpajakan' => '/dashboard/perpajakan',
        'Verifikasi' => '/dashboard/verifikasi-role',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'table_columns_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'table_columns_preferences' => 'array',
        ];
    }

    /**
     * Get the dashboard route for the user's role.
     */
    public function getDashboardRoute(): string
    {
        return self::DASHBOARD_ROUTES[$this->role] ?? self::DASHBOARD_ROUTES['IbuA'];
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Get the display name for the user's role.
     */
    public function getRoleDisplayName(): string
    {
        return self::ROLES[$this->role] ?? 'Unknown';
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, string $role): void
    {
        $query->where('role', $role);
    }

    /**
     * Get all available roles as array for select options.
     */
    public static function getRoleOptions(): array
    {
        return collect(self::ROLES)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
