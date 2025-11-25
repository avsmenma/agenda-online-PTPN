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
            // Perpajakan fields
            $table->string('npwp')->nullable()->after('keterangan');
            $table->enum('status_perpajakan', ['sedang_diproses', 'selesai'])->nullable()->after('npwp');
            $table->string('no_faktur')->nullable()->after('status_perpajakan');
            $table->date('tanggal_faktur')->nullable()->after('no_faktur');
            $table->date('tanggal_selesai_verifikasi_pajak')->nullable()->after('tanggal_faktur');
            $table->string('jenis_pph')->nullable()->after('tanggal_selesai_verifikasi_pajak');
            $table->decimal('dpp_pph', 20, 2)->nullable()->after('jenis_pph');
            $table->decimal('ppn_terhutang', 20, 2)->nullable()->after('dpp_pph');
            $table->text('link_dokumen_pajak')->nullable()->after('ppn_terhutang');

            // Deadline for perpajakan (similar to ibuB)
            $table->timestamp('deadline_perpajakan_at')->nullable()->after('link_dokumen_pajak');
            $table->integer('deadline_perpajakan_days')->nullable()->after('deadline_perpajakan_at');
            $table->text('deadline_perpajakan_note')->nullable()->after('deadline_perpajakan_days');

            // Timestamps for perpajakan workflow
            $table->timestamp('sent_to_perpajakan_at')->nullable()->after('deadline_perpajakan_note');
            $table->timestamp('processed_perpajakan_at')->nullable()->after('sent_to_perpajakan_at');
            $table->timestamp('returned_from_perpajakan_at')->nullable()->after('processed_perpajakan_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'npwp',
                'status_perpajakan',
                'no_faktur',
                'tanggal_faktur',
                'tanggal_selesai_verifikasi_pajak',
                'jenis_pph',
                'dpp_pph',
                'ppn_terhutang',
                'link_dokumen_pajak',
                'deadline_perpajakan_at',
                'deadline_perpajakan_days',
                'deadline_perpajakan_note',
                'sent_to_perpajakan_at',
                'processed_perpajakan_at',
                'returned_from_perpajakan_at',
            ]);
        });
    }
};
