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
        // Add data_source column to payment_logs
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->string('data_source', 50)->default('input_ks')->after('tu_tk_kontrol');
            $table->index('data_source');
        });

        // Add data_source column to document_position_trackings
        Schema::table('document_position_trackings', function (Blueprint $table) {
            $table->string('data_source', 50)->default('input_ks')->after('tu_tk_kontrol');
            $table->index('data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex(['data_source']);
            $table->dropColumn('data_source');
        });

        Schema::table('document_position_trackings', function (Blueprint $table) {
            $table->dropIndex(['data_source']);
            $table->dropColumn('data_source');
        });
    }
};
