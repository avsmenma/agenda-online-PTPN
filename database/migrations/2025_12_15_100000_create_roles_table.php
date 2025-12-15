<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('name', 100);
            $table->integer('sequence')->default(0);
            $table->timestamps();
        });

        // Seed default roles
        DB::table('roles')->insert([
            ['code' => 'ibuA', 'name' => 'Ibu Tarapul', 'sequence' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ibuB', 'name' => 'Ibu Yuni', 'sequence' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'perpajakan', 'name' => 'Perpajakan', 'sequence' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'akutansi', 'name' => 'Akutansi', 'sequence' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pembayaran', 'name' => 'Pembayaran', 'sequence' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
