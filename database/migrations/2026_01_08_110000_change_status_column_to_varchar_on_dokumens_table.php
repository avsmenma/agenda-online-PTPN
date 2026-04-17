<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * This migration changes the status column from ENUM to VARCHAR(100) to support all status values
     * including 'belum dikirim', 'sedang diproses', 'selesai', 'menunggu approve', etc.
     */
    public function up(): void
    {
        // Use raw SQL to change ENUM to VARCHAR since Laravel Schema doesn't support this directly
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status VARCHAR(100) DEFAULT 'belum dikirim'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting to ENUM may cause data loss if any status values don't match
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM('sedang diproses', 'selesai', 'belum dikirim') DEFAULT 'sedang diproses'");
    }
};
