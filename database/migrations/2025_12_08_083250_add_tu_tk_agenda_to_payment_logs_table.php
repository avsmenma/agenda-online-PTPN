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
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->string('tu_tk_agenda')->nullable()->after('tu_tk_kontrol');
            $table->index('tu_tk_agenda'); // Add index for faster lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex(['tu_tk_agenda']);
            $table->dropColumn('tu_tk_agenda');
        });
    }
};
