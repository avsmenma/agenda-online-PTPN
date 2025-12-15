<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Untuk MySQL, kita perlu menggunakan raw SQL untuk memodifikasi ENUM
        // karena Laravel Blueprint tidak support modify enum secara langsung
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN inbox_approval_for ENUM('IbuB', 'Perpajakan', 'Akutansi', 'Pembayaran') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: hapus 'Pembayaran' dari enum
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN inbox_approval_for ENUM('IbuB', 'Perpajakan', 'Akutansi') NULL");
    }
};
