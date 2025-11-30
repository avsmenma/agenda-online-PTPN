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
        Schema::table('dokumens', function (Blueprint $table) {
            // Change status_pembayaran from ENUM to VARCHAR to support CSV import values
            if (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                // First, drop the index if it exists
                try {
                    $table->dropIndex(['status_pembayaran']);
                } catch (\Exception $e) {
                    // Index might not exist
                }
                
                // Change column type from ENUM to VARCHAR(255)
                DB::statement("ALTER TABLE dokumens MODIFY COLUMN status_pembayaran VARCHAR(255) NULL");
                
                // Re-add index
                $table->index(['status_pembayaran']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                // Drop index
                try {
                    $table->dropIndex(['status_pembayaran']);
                } catch (\Exception $e) {
                    // Index might not exist
                }
                
                // Change back to ENUM
                DB::statement("ALTER TABLE dokumens MODIFY COLUMN status_pembayaran ENUM('siap_dibayar', 'sudah_dibayar') NULL");
                
                // Re-add index
                $table->index(['status_pembayaran']);
            }
        });
    }
};

