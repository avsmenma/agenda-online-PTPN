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
            // Make fields nullable to allow saving with only nomor_agenda
            $table->string('nomor_agenda')->nullable()->change();
            $table->string('bulan')->nullable()->change();
            $table->integer('tahun')->nullable()->change();
            $table->string('nomor_spp')->nullable()->change();
            $table->dateTime('tanggal_spp')->nullable()->change();
            $table->text('uraian_spp')->nullable()->change();
            $table->decimal('nilai_rupiah', 15, 2)->nullable()->change();
            $table->string('kategori')->nullable()->change();
            $table->string('jenis_dokumen')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Revert to NOT NULL (but keep nullable for some fields that should remain optional)
            $table->string('nomor_agenda')->nullable(false)->change();
            $table->string('bulan')->nullable(false)->change();
            $table->integer('tahun')->nullable(false)->change();
            $table->string('nomor_spp')->nullable(false)->change();
            $table->dateTime('tanggal_spp')->nullable(false)->change();
            $table->text('uraian_spp')->nullable(false)->change();
            $table->decimal('nilai_rupiah', 15, 2)->nullable(false)->change();
            $table->string('kategori')->nullable(false)->change();
            $table->string('jenis_dokumen')->nullable(false)->change();
        });
    }
};

