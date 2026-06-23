<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anti-replay TOTP (C3): simpan time-slice (timestep) kode 2FA terakhir yang
 * dipakai, agar kode yang sama/lebih lama tidak bisa dipakai ulang.
 *
 * Nilai diisi oleh PragmaRX\Google2FA::verifyKeyNewer() (integer time-slice),
 * bukan datetime — karena itu bertipe unsignedBigInteger, bukan timestamp.
 * Kolom nullable & aman (additive). Dibungkus guard hasColumn agar idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'two_factor_last_used_timestep')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('two_factor_last_used_timestep')
                    ->nullable()
                    ->after('two_factor_recovery_codes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'two_factor_last_used_timestep')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('two_factor_last_used_timestep');
            });
        }
    }
};
