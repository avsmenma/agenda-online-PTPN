<?php

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
        Schema::table('dokumens', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('dokumens', 'tanggal_dibayar')) {
                // Try to add after link_bukti_pembayaran if it exists, otherwise after status_pembayaran
                if (Schema::hasColumn('dokumens', 'link_bukti_pembayaran')) {
                    $table->date('tanggal_dibayar')->nullable()->after('link_bukti_pembayaran');
                } elseif (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                    $table->date('tanggal_dibayar')->nullable()->after('status_pembayaran');
                } else {
                    $table->date('tanggal_dibayar')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (Schema::hasColumn('dokumens', 'tanggal_dibayar')) {
                $table->dropColumn('tanggal_dibayar');
            }
        });
    }
};
