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
            $table->timestamp('deadline_at')->nullable()->after('sent_to_ibub_at');
            $table->integer('deadline_days')->nullable()->after('deadline_at');
            $table->text('deadline_note')->nullable()->after('deadline_days');
            $table->timestamp('deadline_completed_at')->nullable()->after('deadline_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn([
                'deadline_at',
                'deadline_days',
                'deadline_note',
                'deadline_completed_at'
            ]);
        });
    }
};
