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
            $table->timestamp('approved_by_ibub_at')->nullable()->after('status');
            $table->string('approved_by_ibub_by')->nullable()->after('approved_by_ibub_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn(['approved_by_ibub_at', 'approved_by_ibub_by']);
        });
    }
};
