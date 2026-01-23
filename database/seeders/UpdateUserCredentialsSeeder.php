<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Seeder untuk update kredensial user
 * 
 * Mapping:
 * - ibua -> ibutara (password: ibua123 -> ibutara825)
 * - ibub -> teamverifikasi (password: ibub123 -> teamverifikasi825)
 * - perpajakan -> teamperpajakan (password: perpajakan123 -> teamperpajakan825)
 * - akutansi -> teamakutansi (password: akutansi123 -> teamakutansi825)
 * 
 * Cara menjalankan:
 * php artisan db:seed --class=UpdateUserCredentialsSeeder
 */
final class UpdateUserCredentialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'old_username' => 'operator',
                'new_username' => 'ibutara',
                'new_password' => 'ibutara825',
                'name' => 'Operator',
                'email' => 'ibutara@ptpn.com',
                'role' => 'operator',
            ],
            [
                'old_username' => 'team_verifikasi',
                'new_username' => 'team_verifikasi',
                'new_password' => 'teamverifikasi825',
                'name' => 'Team Verifikasi',
                'email' => 'teamverifikasi@ptpn.com',
                'role' => 'team_verifikasi',
            ],
            [
                'old_username' => 'perpajakan',
                'new_username' => 'teamperpajakan',
                'new_password' => 'teamperpajakan825',
                'name' => 'Team Perpajakan',
                'email' => 'teamperpajakan@ptpn.com',
                'role' => 'Perpajakan',
            ],
            [
                'old_username' => 'akutansi',
                'new_username' => 'teamakutansi',
                'new_password' => 'teamakutansi825',
                'name' => 'Team Akutansi',
                'email' => 'teamakutansi@ptpn.com',
                'role' => 'Akutansi',
            ],
        ];

        foreach ($users as $userData) {
            try {
                // Cari user berdasarkan old_username atau new_username
                $user = User::where('username', $userData['old_username'])
                    ->orWhere('username', $userData['new_username'])
                    ->first();

                if ($user) {
                    // Update user yang sudah ada
                    $user->username = $userData['new_username'];
                    $user->password = Hash::make($userData['new_password']);
                    $user->name = $userData['name'];
                    $user->email = $userData['email'];
                    $user->role = $userData['role'];
                    $user->save();

                    $this->command->info("✓ Updated user: {$userData['old_username']} -> {$userData['new_username']}");
                    Log::info("Updated user credentials", [
                        'old_username' => $userData['old_username'],
                        'new_username' => $userData['new_username'],
                        'role' => $userData['role'],
                    ]);
                } else {
                    // Buat user baru jika belum ada
                    User::create([
                        'username' => $userData['new_username'],
                        'password' => Hash::make($userData['new_password']),
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'role' => $userData['role'],
                    ]);

                    $this->command->info("✓ Created user: {$userData['new_username']}");
                    Log::info("Created new user", [
                        'username' => $userData['new_username'],
                        'role' => $userData['role'],
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to process user {$userData['new_username']}: {$e->getMessage()}");
                Log::error("Failed to update/create user", [
                    'username' => $userData['new_username'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command->info("\n✓ User credentials update completed!");
    }
}


