<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add paraf and processing tracking columns to dokumens table.
     * - tanggal_paraf: timestamp when document was signed off
     * - pemaraf: who signed off (Ibu Yuni / Sekar)
     * - tanggal_selesai_diproses: timestamp when document was sent to next role
     */
    public function up(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumens', 'tanggal_paraf')) {
                $table->timestamp('tanggal_paraf')->nullable()->after('returned_at')
                    ->comment('Timestamp when document was parafed/signed off by verifikasi');
            }
            if (!Schema::hasColumn('dokumens', 'pemaraf')) {
                $table->string('pemaraf', 50)->nullable()->after('tanggal_paraf')
                    ->comment('Name of person who parafed: Ibu Yuni or Sekar');
            }
            if (!Schema::hasColumn('dokumens', 'tanggal_selesai_diproses')) {
                $table->timestamp('tanggal_selesai_diproses')->nullable()->after('pemaraf')
                    ->comment('Timestamp when document was sent to next role (perpajakan/akutansi/pembayaran)');
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
            if (Schema::hasColumn('dokumens', 'tanggal_paraf'))
                $columns[] = 'tanggal_paraf';
            if (Schema::hasColumn('dokumens', 'pemaraf'))
                $columns[] = 'pemaraf';
            if (Schema::hasColumn('dokumens', 'tanggal_selesai_diproses'))
                $columns[] = 'tanggal_selesai_diproses';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
