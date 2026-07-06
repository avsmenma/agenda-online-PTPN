<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

final class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Kode peran kanonik → label. Satu kunci per peran (lihat App\Support\Role).
    // Alias/campur-case ditangani oleh Role::normalize() di method, bukan di sini.
    public const ROLES = [
        'admin'           => 'Admin',
        'owner'           => 'Owner',
        'programmer'      => 'Programmer',
        'operator'        => 'Operator',
        'team_verifikasi' => 'Team Verifikasi',
        'perpajakan'      => 'Perpajakan',
        'akutansi'        => 'Akuntansi',
        'pembayaran'      => 'Pembayaran',
        'system'          => 'System',
        'bagian_akn'      => 'Bagian AKN',
        'bagian_dpm'      => 'Bagian DPM',
        'bagian_kpl'      => 'Bagian KPL',
        'bagian_pmo'      => 'Bagian PMO',
        'bagian_sdm'      => 'Bagian SDM',
        'bagian_skh'      => 'Bagian SKH',
        'bagian_tan'      => 'Bagian TAN',
        'bagian_tep'      => 'Bagian TEP',
    ];

    public const DASHBOARD_ROUTES = [
        'admin'           => '/owner/home',
        'owner'           => '/owner/home',
        'programmer'      => '/programmer/dashboard',
        'operator'        => '/documents',
        'team_verifikasi' => '/dashboard/verifikasi',
        'perpajakan'      => '/dashboard/perpajakan',
        'akutansi'        => '/dashboard/akutansi',
        'pembayaran'      => '/dashboard/pembayaran',
        'bagian_akn'      => '/bagian/documents',
        'bagian_dpm'      => '/bagian/documents',
        'bagian_kpl'      => '/bagian/documents',
        'bagian_pmo'      => '/bagian/documents',
        'bagian_sdm'      => '/bagian/documents',
        'bagian_skh'      => '/bagian/documents',
        'bagian_tan'      => '/bagian/documents',
        'bagian_tep'      => '/bagian/documents',
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
        'phone_number',
        'profile_photo_path',
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
            'two_factor_last_used_timestep' => 'integer',
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
        $role = \App\Support\Role::normalize($this->role);

        return self::DASHBOARD_ROUTES[$role] ?? self::DASHBOARD_ROUTES['operator'];
    }

    /**
     * Check if user has a specific role (case-insensitive + alias-aware).
     */
    public function hasRole(string $role): bool
    {
        return \App\Support\Role::matches($this->role, $role);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(\App\Support\Role::ADMIN);
    }

    /**
     * Get the display name for the user's role.
     */
    public function getRoleDisplayName(): string
    {
        $role = \App\Support\Role::normalize($this->role);

        return self::ROLES[$role] ?? 'Unknown';
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
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
            ->except(['system']) // 'system' peran virtual auto-forward, tak boleh dipilih manual
            ->map(fn(string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}





