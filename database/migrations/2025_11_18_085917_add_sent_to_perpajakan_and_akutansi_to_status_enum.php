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
            $table->enum('status', [
                'draft',
                'sedang diproses',
                'sent_to_ibub',
                'approved_ibub',
                'rejected_ibub',
                'returned_to_ibua',
                'selesai',
                'dikembalikan',
                'returned_to_department',
                'returned_to_bidang',
                'sent_to_perpajakan',
                'sent_to_akutansi'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'sedang diproses',
                'sent_to_ibub',
                'approved_ibub',
                'rejected_ibub',
                'returned_to_ibua',
                'selesai',
                'dikembalikan',
                'returned_to_department',
                'returned_to_bidang'
            ])->change();
        });
    }
};
