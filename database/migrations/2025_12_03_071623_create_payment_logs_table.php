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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tu_tk_kontrol')->unsigned(); // Foreign key ke tu_tk_2023.KONTROL
            $table->integer('payment_sequence')->default(1); // Urutan pembayaran (1-6)
            $table->date('tanggal_bayar');
            $table->decimal('jumlah', 15, 2);
            $table->string('keterangan')->nullable();
            $table->string('created_by')->nullable(); // User yang membuat log
            $table->timestamps();

            // Indexes
            $table->index(['tu_tk_kontrol', 'payment_sequence']);
            $table->index('tanggal_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
