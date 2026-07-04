<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Guard produksi: seeder default ini menyisipkan user uji (test@example.com)
        // dengan factory. Dilarang berjalan di produksi agar tidak mengotori tabel users.
        // Untuk memelihara data roles di produksi, jalankan RoleSeeder langsung:
        //   php artisan db:seed --class=RoleSeeder --force
        if (app()->isProduction()) {
            throw new \RuntimeException('DatabaseSeeder (membuat user uji) dilarang dijalankan di environment produksi. Untuk seed roles gunakan: php artisan db:seed --class=RoleSeeder --force');
        }

        // Reference data idempoten (roles, termasuk 'system' untuk auto-forward).
        $this->call(RoleSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

