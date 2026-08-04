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

        if (! Schema::hasColumn('dokumens', 'nomor_kontrak')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table): void {
            $table->dropColumn('nomor_kontrak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if(Schema::hasColumn('dokumens', 'nomor_kontrak')) {
            return;
        }

        Schema::table('dokumens', function (Blueprint $table): void {
            $table->string('nomor_kontrak')->nullable()->after('no_spk');
        });
    }
};
