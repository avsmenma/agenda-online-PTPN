<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists and is JSON type
        if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
            // For MySQL/MariaDB, we need to change the column type using raw SQL
            // because Laravel's change() method may not work properly with JSON to TEXT conversion
            DB::statement('ALTER TABLE `users` MODIFY `two_factor_recovery_codes` TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to JSON (but this might cause issues if data is encrypted)
        if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
            DB::statement('ALTER TABLE `users` MODIFY `two_factor_recovery_codes` JSON NULL');
        }
    }
};

