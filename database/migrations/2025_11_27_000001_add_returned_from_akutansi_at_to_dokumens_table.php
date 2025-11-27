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
            if (!Schema::hasColumn('dokumens', 'returned_from_akutansi_at')) {
                $table->timestamp('returned_from_akutansi_at')->nullable()->after('returned_from_perpajakan_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (Schema::hasColumn('dokumens', 'returned_from_akutansi_at')) {
                $table->dropColumn('returned_from_akutansi_at');
            }
        });
    }
};

