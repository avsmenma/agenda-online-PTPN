<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing returned_to_ibua documents to returned_to_department
        // Set default target department based on document category or use 'pembayaran' as default
        DB::table('dokumens')
            ->where('status', 'returned_to_ibua')
            ->update([
                'status' => 'returned_to_department',
                'target_department' => DB::raw("COALESCE(target_department,
                    CASE
                        WHEN LOWER(kategori) LIKE '%pembayaran%' THEN 'pembayaran'
                        WHEN LOWER(kategori) LIKE '%akutansi%' OR LOWER(kategori) LIKE '%akunting%' THEN 'akutansi'
                        WHEN LOWER(kategori) LIKE '%pajak%' OR LOWER(kategori) LIKE '%perpajakan%' THEN 'perpajakan'
                        ELSE 'pembayaran'
                    END)"),
                'department_returned_at' => DB::raw('COALESCE(returned_to_ibua_at, NOW())'),
                'department_return_reason' => DB::raw("COALESCE(alasan_pengembalian, 'Dialihkan dari return ke IbuA')"),
                'current_handler' => 'ibuB'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert returned_to_department back to returned_to_ibua
        DB::table('dokumens')
            ->where('status', 'returned_to_department')
            ->where(function($query) {
                $query->where('department_return_reason', 'Dialihkan dari return ke IbuA')
                      ->orWhere('department_return_reason', 'like', '%Dialihkan%');
            })
            ->update([
                'status' => 'returned_to_ibua',
                'target_department' => null,
                'department_returned_at' => null,
                'department_return_reason' => null,
                'current_handler' => 'ibuA'
            ]);
    }
};
