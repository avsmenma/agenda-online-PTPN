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
            // Perpajakan Extended Fields
            // 1. Komoditi (dropdown)
            $table->string('komoditi_perpajakan')->nullable()->after('link_dokumen_pajak');
            
            // 2. NPWP Pembeli - sudah ada sebagai 'npwp', rename column untuk clarity
            // $table->renameColumn('npwp', 'npwp_pembeli'); // Skip rename, keep existing
            
            // 3. Alamat Pembeli
            $table->text('alamat_pembeli')->nullable()->after('npwp');
            
            // 4. No Kontrak
            $table->string('no_kontrak')->nullable()->after('alamat_pembeli');
            
            // 5. No Invoice
            $table->string('no_invoice')->nullable()->after('no_kontrak');
            
            // 6. Tanggal Invoice
            $table->date('tanggal_invoice')->nullable()->after('no_invoice');
            
            // 7. DPP Invoice
            $table->decimal('dpp_invoice', 20, 2)->nullable()->after('tanggal_invoice');
            
            // 8. PPN Invoice
            $table->decimal('ppn_invoice', 20, 2)->nullable()->after('dpp_invoice');
            
            // 9. DPP + PPN Invoice (total)
            $table->decimal('dpp_ppn_invoice', 20, 2)->nullable()->after('ppn_invoice');
            
            // 10. Tanggal Pengajuan
            $table->date('tanggal_pengajuan_pajak')->nullable()->after('dpp_ppn_invoice');
            
            // 11 & 12. No Faktur dan Tanggal Faktur - sudah ada
            
            // 13. DPP Faktur (berbeda dari dpp_invoice)
            $table->decimal('dpp_faktur', 20, 2)->nullable()->after('tanggal_faktur');
            
            // 14. PPN Faktur
            $table->decimal('ppn_faktur', 20, 2)->nullable()->after('dpp_faktur');
            
            // 15. Selisih
            $table->decimal('selisih_pajak', 20, 2)->nullable()->after('ppn_faktur');
            
            // 16. Keterangan Pajak
            $table->text('keterangan_pajak')->nullable()->after('selisih_pajak');
            
            // 17. Penggantian
            $table->decimal('penggantian_pajak', 20, 2)->nullable()->after('keterangan_pajak');
            
            // 18. DPP Penggantian
            $table->decimal('dpp_penggantian', 20, 2)->nullable()->after('penggantian_pajak');
            
            // 19. PPN Penggantian
            $table->decimal('ppn_penggantian', 20, 2)->nullable()->after('dpp_penggantian');
            
            // 20. Selisih PPN
            $table->decimal('selisih_ppn', 20, 2)->nullable()->after('ppn_penggantian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'komoditi_perpajakan',
                'alamat_pembeli',
                'no_kontrak',
                'no_invoice',
                'tanggal_invoice',
                'dpp_invoice',
                'ppn_invoice',
                'dpp_ppn_invoice',
                'tanggal_pengajuan_pajak',
                'dpp_faktur',
                'ppn_faktur',
                'selisih_pajak',
                'keterangan_pajak',
                'penggantian_pajak',
                'dpp_penggantian',
                'ppn_penggantian',
                'selisih_ppn',
            ]);
        });
    }
};
