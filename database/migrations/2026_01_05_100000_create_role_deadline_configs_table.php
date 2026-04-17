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
        Schema::create('role_deadline_configs', function (Blueprint $table) {
            $table->id();
            $table->string('role_code', 50)->unique();
            $table->integer('default_deadline_days')->default(3); // Default deadline dalam hari
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key to roles table
            $table->foreign('role_code')->references('code')->on('roles')->onDelete('cascade');
        });

        // Insert default values for ibuB, perpajakan, and akutansi
        DB::table('role_deadline_configs')->insert([
            [
                'role_code' => 'ibuB',
                'default_deadline_days' => 3,
                'description' => 'Default deadline untuk Team Verifikasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_code' => 'perpajakan',
                'default_deadline_days' => 3,
                'description' => 'Default deadline untuk Team Perpajakan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_code' => 'akutansi',
                'default_deadline_days' => 3,
                'description' => 'Default deadline untuk Team Akutansi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_deadline_configs');
    }
};

