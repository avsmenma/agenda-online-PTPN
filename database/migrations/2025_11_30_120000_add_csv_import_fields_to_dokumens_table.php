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
            // Add fields from CSV that don't exist in current table
            if (!Schema::hasColumn('dokumens', 'nama_kebuns')) {
                $table->string('nama_kebuns')->nullable()->after('dibayar_kepada');
            }

            if (!Schema::hasColumn('dokumens', 'no_ba')) {
                $table->string('no_ba')->nullable()->after('tanggal_berita_acara');
            }

            if (!Schema::hasColumn('dokumens', 'tanggal_faktur')) {
                $table->date('tanggal_faktur')->nullable()->after('tanggal_berita_acara');
            }

            if (!Schema::hasColumn('dokumens', 'NO_PO')) {
                $table->string('NO_PO')->nullable()->after('nomor_miro');
            }

            if (!Schema::hasColumn('dokumens', 'NO_MIRO_SES')) {
                $table->string('NO_MIRO_SES')->nullable()->after('NO_PO');
            }

            if (!Schema::hasColumn('dokumens', 'DIBAYAR')) {
                $table->decimal('DIBAYAR', 15, 2)->nullable()->after('status_pembayaran');
            }

            if (!Schema::hasColumn('dokumens', 'BELUM_DIBAYAR')) {
                $table->decimal('BELUM_DIBAYAR', 15, 2)->nullable()->after('DIBAYAR');
            }

            if (!Schema::hasColumn('dokumens', 'KATEGORI')) {
                $table->string('KATEGORI')->nullable()->after('jenis_dokumen');
            }

            // Add index for better performance
            $table->index(['nama_kebuns']);
            $table->index(['tanggal_spp']);
            $table->index(['status_pembayaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropIndex(['nama_kebuns']);
            $table->dropIndex(['tanggal_spp']);
            $table->dropIndex(['status_pembayaran']);

            $table->dropColumn([
                'nama_kebuns',
                'no_ba',
                'tanggal_faktur',
                'NO_PO',
                'NO_MIRO_SES',
                'DIBAYAR',
                'BELUM_DIBAYAR',
                'KATEGORI'
            ]);
        });
    }
};