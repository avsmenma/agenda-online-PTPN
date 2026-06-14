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
            if (!Schema::hasColumn('dokumens', 'link')) {
                if (Schema::hasColumn('dokumens', 'bagian')) {
                    $table->text('link')->nullable()->after('bagian');
                } else {
                    $table->text('link')->nullable();
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
            if (Schema::hasColumn('dokumens', 'link')) {
                $table->dropColumn('link');
            }
        });
    }
};
