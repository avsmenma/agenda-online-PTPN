<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Add tanggal_miro column after nomor_miro
            if (!Schema::hasColumn('dokumens', 'tanggal_miro')) {
                $table->date('tanggal_miro')->nullable()->after('nomor_miro');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (Schema::hasColumn('dokumens', 'tanggal_miro')) {
                $table->dropColumn('tanggal_miro');
            }
        });
    }
};
