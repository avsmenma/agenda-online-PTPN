<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BagianUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bagians = [
            ['code' => 'AKN', 'name' => 'Akuntansi', 'role' => 'bagian_akn'],
            ['code' => 'DPM', 'name' => 'DPM', 'role' => 'bagian_dpm'],
            ['code' => 'KPL', 'name' => 'Kepatuhan', 'role' => 'bagian_kpl'],
            ['code' => 'PMO', 'name' => 'PMO', 'role' => 'bagian_pmo'],
            ['code' => 'SDM', 'name' => 'SDM', 'role' => 'bagian_sdm'],
            ['code' => 'SKH', 'name' => 'Sekretariat', 'role' => 'bagian_skh'],
            ['code' => 'TAN', 'name' => 'Tanaman', 'role' => 'bagian_tan'],
            ['code' => 'TEP', 'name' => 'Teknik & Pengolahan', 'role' => 'bagian_tep'],
        ];

        foreach ($bagians as $bagian) {
            $username = strtolower($bagian['code']);

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => 'User Bagian ' . $bagian['name'],
                    'email' => $username . '@ptpn.com',
                    'password' => Hash::make($username . '123'),
                    'role' => $bagian['role'],
                    'bagian_code' => $bagian['code'],
                ]
            );

            $this->command->info("Created user: {$username} / {$username}123 (Bagian {$bagian['code']})");
        }

        $this->command->info('');
        $this->command->info('=== Bagian Users Created ===');
        $this->command->info('Login credentials:');
        foreach ($bagians as $bagian) {
            $username = strtolower($bagian['code']);
            $this->command->info("  {$username} / {$username}123 ({$bagian['name']})");
        }
    }
}

