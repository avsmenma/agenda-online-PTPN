<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->unique()->after('id');

            // SQLite doesn't support ENUM CHECK constraints well across migrations —
            // use string for tests; MySQL will get a VARCHAR later via MODIFY migration.
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                $table->string('role')->default('operator')->after('email');
            } else {
                $table->enum('role', [
                    'Admin',
                    'IbuA',
                    'IbuB',
                    'Pembayaran',
                    'Akutansi',
                    'Perpajakan',
                    'Verifikasi'
                ])->default('IbuA')->after('email');
            }

            // Indexes untuk performa
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'username']);
        });
    }
};
