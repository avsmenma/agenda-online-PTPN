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
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('dokumens', 'sent_to_pembayaran_at')) {
                // Try to add after processed_akutansi_at if exists, otherwise after updated_at
                if (Schema::hasColumn('dokumens', 'processed_akutansi_at')) {
                    $table->timestamp('sent_to_pembayaran_at')->nullable()->after('processed_akutansi_at');
                } elseif (Schema::hasColumn('dokumens', 'updated_at')) {
                    $table->timestamp('sent_to_pembayaran_at')->nullable()->after('updated_at');
                } else {
                    $table->timestamp('sent_to_pembayaran_at')->nullable();
                }
            }
            
            if (!Schema::hasColumn('dokumens', 'status_pembayaran')) {
                // Add after sent_to_pembayaran_at if it was just added, otherwise at the end
                if (Schema::hasColumn('dokumens', 'sent_to_pembayaran_at')) {
                    $table->enum('status_pembayaran', ['siap_dibayar', 'sudah_dibayar'])->nullable()->after('sent_to_pembayaran_at');
                } elseif (Schema::hasColumn('dokumens', 'updated_at')) {
                    $table->enum('status_pembayaran', ['siap_dibayar', 'sudah_dibayar'])->nullable()->after('updated_at');
                } else {
                    $table->enum('status_pembayaran', ['siap_dibayar', 'sudah_dibayar'])->nullable();
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
            if (Schema::hasColumn('dokumens', 'status_pembayaran')) {
                $table->dropColumn('status_pembayaran');
            }
            
            if (Schema::hasColumn('dokumens', 'sent_to_pembayaran_at')) {
                $table->dropColumn('sent_to_pembayaran_at');
            }
        });
    }
};

