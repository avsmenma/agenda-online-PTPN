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
        Schema::create('dokumen_pos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained()->onDelete('cascade');
            $table->string('nomor_po');
            $table->timestamps();
        });

        Schema::create('dokumen_prs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained()->onDelete('cascade');
            $table->string('nomor_pr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_prs');
        Schema::dropIfExists('dokumen_pos');
    }
};