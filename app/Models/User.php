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
        // Bagian roles
        'bagian_akn' => 'Bagian AKN',
        'bagian_dpm' => 'Bagian DPM',
        'bagian_kpl' => 'Bagian KPL',
        'bagian_pmo' => 'Bagian PMO',
        'bagian_sdm' => 'Bagian SDM',
        'bagian_skh' => 'Bagian SKH',
        'bagian_tan' => 'Bagian TAN',
        'bagian_tep' => 'Bagian TEP',
    ];

    public const DASHBOARD_ROUTES = [
        'Admin' => '/owner/home',
        'owner' => '/owner/home',
        'Owner' => '/owner/home',
        'IbuA' => '/dashboard',
        'ibutarapul' => '/dashboard',  // New role name from migration
        'IbuB' => '/dashboard/verifikasi',
        'verifikasi' => '/dashboard/verifikasi',  // New role name from migration
        'Pembayaran' => '/dashboard/pembayaran',
        'pembayaran' => '/dashboard/pembayaran',
        'Akutansi' => '/dashboard/akutansi',
        'akutansi' => '/dashboard/akutansi',
        'Perpajakan' => '/dashboard/perpajakan',
        'perpajakan' => '/dashboard/perpajakan',
        'Verifikasi' => '/dashboard/verifikasi-role',
        // Bagian dashboard routes
        'bagian_akn' => '/bagian/dashboard',
        'bagian_dpm' => '/bagian/dashboard',
        'bagian_kpl' => '/bagian/dashboard',
        'bagian_pmo' => '/bagian/dashboard',
        'bagian_sdm' => '/bagian/dashboard',
        'bagian_skh' => '/bagian/dashboard',
        'bagian_tan' => '/bagian/dashboard',
        'bagian_tep' => '/bagian/dashboard',
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
        'bagian_code',
        'table_columns_preferences',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Check if user has 2FA enabled
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_secret && $this->two_factor_confirmed_at;
    }

    /**
     * Get decrypted two factor secret
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    /**
     * Get decrypted recovery codes
     */
    public function getRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
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
            ->map(fn(string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
