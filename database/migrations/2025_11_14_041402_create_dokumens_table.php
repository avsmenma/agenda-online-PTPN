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
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique();
            $table->string('bulan');
            $table->integer('tahun');
            $table->dateTime('tanggal_masuk');
            $table->string('nomor_spp');
            $table->dateTime('tanggal_spp');
            $table->text('uraian_spp');
            $table->decimal('nilai_rupiah', 15, 2);
            $table->string('kategori');
            $table->string('jenis_dokumen');
            $table->string('jenis_sub_pekerjaan')->nullable();
            $table->string('jenis_pembayaran')->nullable();
            $table->string('dibayar_kepada')->nullable();
            $table->string('no_berita_acara')->nullable();
            $table->date('tanggal_berita_acara')->nullable();
            $table->string('no_spk')->nullable();
            $table->date('tanggal_spk')->nullable();
            $table->date('tanggal_berakhir_spk')->nullable();
            $table->string('nomor_mirror')->nullable();
            $table->enum('status', ['sedang diproses', 'selesai'])->default('sedang diproses');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumens');
    }
};
