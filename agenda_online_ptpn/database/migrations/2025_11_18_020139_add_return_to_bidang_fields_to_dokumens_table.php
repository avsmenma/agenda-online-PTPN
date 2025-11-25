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
            $table->string('target_bidang')->nullable()->after('department_return_reason');
            $table->timestamp('bidang_returned_at')->nullable()->after('target_bidang');
            $table->text('bidang_return_reason')->nullable()->after('bidang_returned_at');

            // Indexes for performance
            $table->index('target_bidang');
            $table->index('bidang_returned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex(['target_bidang']);
            $table->dropIndex(['bidang_returned_at']);
            $table->dropColumn(['target_bidang', 'bidang_returned_at', 'bidang_return_reason']);
        });
    }
};
