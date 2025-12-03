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
        Schema::create('document_position_trackings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tu_tk_kontrol')->unsigned(); // Foreign key ke tu_tk_2023.KONTROL
            $table->string('posisi_lama')->nullable(); // Posisi sebelum perubahan
            $table->string('posisi_baru'); // Posisi setelah perubahan
            $table->string('changed_by')->nullable(); // User yang mengubah
            $table->text('keterangan')->nullable(); // Keterangan perubahan
            $table->timestamp('changed_at'); // Waktu perubahan
            $table->timestamps();

            // Indexes
            $table->index(['tu_tk_kontrol', 'changed_at']);
            $table->index('posisi_baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_position_trackings');
    }
};
