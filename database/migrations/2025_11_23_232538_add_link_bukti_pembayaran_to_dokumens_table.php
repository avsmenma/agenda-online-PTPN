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
            if (!Schema::hasColumn('dokumens', 'link_bukti_pembayaran')) {
                // Try to add after status_pembayaran if it exists, otherwise just add at the end
                if (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                    $table->text('link_bukti_pembayaran')->nullable()->after('status_pembayaran');
                } else {
                    $table->text('link_bukti_pembayaran')->nullable();
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
            $table->dropColumn('link_bukti_pembayaran');
        });
    }
};
