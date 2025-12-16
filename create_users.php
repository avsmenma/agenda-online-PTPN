<?php

/**
 * Script untuk membuat/update user credentials
 * 
 * Mapping:
 * - ibua -> ibutara (password: ibua123 -> ibutara825)
 * - ibub -> teamverifikasi (password: ibub123 -> teamverifikasi825)
 * - perpajakan -> teamperpajakan (password: perpajakan123 -> teamperpajakan825)
 * - akutansi -> teamakutansi (password: akutansi123 -> teamakutansi825)
 * 
 * Cara menjalankan:
 * php create_users.php
 * 
 * Atau dari browser:
 * http://localhost:8000/create_users.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

$users = [
    [
        'old_username' => 'ibua',
        'new_username' => 'ibutara',
        'new_password' => 'ibutara825',
        'name' => 'Ibu Tarapul',
        'email' => 'ibutara@ptpn.com',
        'role' => 'IbuA',
    ],
    [
        'old_username' => 'ibub',
        'new_username' => 'teamverifikasi',
        'new_password' => 'teamverifikasi825',
        'name' => 'Team Verifikasi',
        'email' => 'teamverifikasi@ptpn.com',
        'role' => 'IbuB',
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

echo "=== Creating/Updating User Credentials ===\n\n";

$successCount = 0;
$errorCount = 0;

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

            echo "✓ Updated user: {$userData['old_username']} -> {$userData['new_username']}\n";
            echo "  Name: {$userData['name']}\n";
            echo "  Email: {$userData['email']}\n";
            echo "  Role: {$userData['role']}\n";
            echo "  Password: {$userData['new_password']}\n\n";
            
            Log::info("Updated user credentials", [
                'old_username' => $userData['old_username'],
                'new_username' => $userData['new_username'],
                'role' => $userData['role'],
            ]);
            
            $successCount++;
        } else {
            // Buat user baru jika belum ada
            User::create([
                'username' => $userData['new_username'],
                'password' => Hash::make($userData['new_password']),
                'name' => $userData['name'],
                'email' => $userData['email'],
                'role' => $userData['role'],
            ]);

            echo "✓ Created user: {$userData['new_username']}\n";
            echo "  Name: {$userData['name']}\n";
            echo "  Email: {$userData['email']}\n";
            echo "  Role: {$userData['role']}\n";
            echo "  Password: {$userData['new_password']}\n\n";
            
            Log::info("Created new user", [
                'username' => $userData['new_username'],
                'role' => $userData['role'],
            ]);
            
            $successCount++;
        }
    } catch (\Exception $e) {
        echo "✗ Failed to process user {$userData['new_username']}: {$e->getMessage()}\n\n";
        Log::error("Failed to update/create user", [
            'username' => $userData['new_username'],
            'error' => $e->getMessage(),
        ]);
        $errorCount++;
    }
}

echo "=== Summary ===\n";
echo "Success: {$successCount}\n";
echo "Errors: {$errorCount}\n";
echo "\n✓ User credentials update completed!\n";

