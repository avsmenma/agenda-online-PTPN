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
            $table->string('bagian')->nullable()->after('jenis_pembayaran');
            $table->string('nama_pengirim')->nullable()->after('bagian');

            // Mengubah field keterangan menjadi nullable jika belum
            $table->text('keterangan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn(['bagian', 'nama_pengirim']);
        });
    }
};
