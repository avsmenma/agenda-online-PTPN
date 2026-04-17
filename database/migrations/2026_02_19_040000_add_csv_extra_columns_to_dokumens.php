<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add kepala_sub_bagian and status_dokumen_csv columns for CSV import.
     * - kepala_sub_bagian: name of the sub-division head
     * - status_dokumen_csv: payment/processing status from CSV (e.g., "Selesai Dibayar", "Dikembalikan")
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumens', 'kepala_sub_bagian')) {
                $table->string('kepala_sub_bagian', 100)->nullable()->after('tanggal_selesai_diproses');
            }
            if (!Schema::hasColumn('dokumens', 'status_dokumen_csv')) {
                $table->string('status_dokumen_csv', 100)->nullable()->after('kepala_sub_bagian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('dokumens', 'kepala_sub_bagian'))
                $columns[] = 'kepala_sub_bagian';
            if (Schema::hasColumn('dokumens', 'status_dokumen_csv'))
                $columns[] = 'status_dokumen_csv';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
