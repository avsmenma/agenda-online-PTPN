<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@ptpn.com',
                'password' => 'admin123',
                'role' => 'Admin',
            ],
            [
                'name' => 'Operator',
                'username' => 'operator',
                'email' => 'ibua@ptpn.com',
                'password' => 'ibua123',
                'role' => 'operator',
            ],
            [
                'name' => 'Team Verifikasi',
                'username' => 'team_verifikasi',
                'email' => 'ibub@ptpn.com',
                'password' => 'ibub123',
                'role' => 'team_verifikasi',
            ],
            [
                'name' => 'Pembayaran',
                'username' => 'pembayaran',
                'email' => 'pembayaran@ptpn.com',
                'password' => 'pembayaran123',
                'role' => 'Pembayaran',
            ],
            [
                'name' => 'Akutansi',
                'username' => 'akutansi',
                'email' => 'akutansi@ptpn.com',
                'password' => 'akutansi123',
                'role' => 'Akutansi',
            ],
            [
                'name' => 'Perpajakan',
                'username' => 'perpajakan',
                'email' => 'perpajakan@ptpn.com',
                'password' => 'perpajakan123',
                'role' => 'Perpajakan',
            ],
            [
                'name' => 'Verifikasi',
                'username' => 'verifikasi',
                'email' => 'verifikasi@ptpn.com',
                'password' => 'verifikasi123',
                'role' => 'Verifikasi',
            ],
        ];

        foreach ($users as $userData) {
            try {
                User::updateOrCreate(
                    ['username' => $userData['username']],
                    [
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make($userData['password']),
                        'role' => $userData['role'],
                        'email_verified_at' => now(),
                    ]
                );

                $this->command->info("User {$userData['username']} created/updated successfully.");
            } catch (\Exception $e) {
                Log::error("Failed to create user {$userData['username']}: " . $e->getMessage());
                $this->command->error("Failed to create user {$userData['username']}: " . $e->getMessage());
            }
        }

        $this->command->info('User seeding completed successfully!');
    }
}

