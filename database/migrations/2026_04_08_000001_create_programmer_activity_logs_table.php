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
        Schema::create('programmer_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programmer_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 100);
            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_description', 500)->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500);
            $table->timestamp('created_at')->useCurrent();

            // Index untuk query performa
            $table->index(['programmer_id', 'created_at']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmer_activity_logs');
    }
};
