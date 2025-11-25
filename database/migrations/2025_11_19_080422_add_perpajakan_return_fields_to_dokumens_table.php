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
            // Field untuk tracking status perbaikan dokumen
            $table->boolean('pengembalian_awaiting_fix')->default(false)->after('returned_from_perpajakan_at');

            // Field untuk tracking kapan dokumen selesai diperbaiki
            $table->timestamp('returned_from_perpajakan_fixed_at')->nullable()->after('pengembalian_awaiting_fix');

            // Field untuk menyimpan data dokumen saat dikirim kembali
            $table->json('perpajakan_return_data')->nullable()->after('returned_from_perpajakan_fixed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'pengembalian_awaiting_fix',
                'returned_from_perpajakan_fixed_at',
                'perpajakan_return_data'
            ]);
        });
    }
};
