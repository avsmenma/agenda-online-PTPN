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
            // Add workflow stage tracking fields
            $table->string('current_stage', 50)->nullable()->after('current_handler')->comment('Current stage: sender, reviewer, tax, accounting, payment');
            $table->string('last_action_status', 100)->nullable()->after('current_stage')->comment('Last action status per stage (for role-based visibility)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn(['current_stage', 'last_action_status']);
        });
    }
};
