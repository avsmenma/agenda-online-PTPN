<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProgrammerActivityLog extends Model
{
    /**
     * Hanya ada created_at, tidak ada updated_at
     */
    const UPDATED_AT = null;

    /**
     * Kolom yang bisa diisi massal
     */
    protected $fillable = [
        'programmer_id',
        'action',
        'target_type',
        'target_id',
        'target_description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Cast tipe data
     */
    protected $casts = [
        'target_id'  => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi: log ini dimiliki oleh programmer (user)
     */
    public function programmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'programmer_id');
    }

    /**
     * Label aksi yang mudah dibaca manusia
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'user_create'       => 'Buat User',
            'user_update'       => 'Ubah User',
            'user_delete'       => 'Hapus User',
            'reset_2fa'         => 'Reset 2FA',
            'disable_2fa'       => 'Nonaktifkan 2FA',
            'reset_password'    => 'Reset Password',
            'bulk_to_payment'   => 'Bulk ke Pembayaran',
            'bulk_set_date'     => 'Bulk Set Tanggal',
            'bulk_delete'       => 'Bulk Hapus',
            'bulk_import'       => 'Bulk Import CSV',
            'bulk_import_csv'   => 'Import CSV',
            'database_cleanup'  => 'Bersihkan Database',
            'update_timestamps' => 'Ubah Timestamp',
            default             => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Kelas badge warna berdasarkan jenis aksi
     */
    public function getBadgeClassAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'delete') || str_contains($this->action, 'cleanup')
                => 'bg-red-100 text-red-800',
            str_contains($this->action, '2fa') || str_contains($this->action, 'password')
                => 'bg-yellow-100 text-yellow-800',
            str_contains($this->action, 'bulk') || str_contains($this->action, 'import')
                => 'bg-purple-100 text-purple-800',
            str_contains($this->action, 'create')
                => 'bg-green-100 text-green-800',
            default
                => 'bg-blue-100 text-blue-800',
        };
    }
}
