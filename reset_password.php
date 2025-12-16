<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Reset password untuk user ibua
$user = User::where('username', 'ibua')->first();

if ($user) {
    $user->password = Hash::make('ibua123');
    $user->save();
    echo "✅ Password berhasil direset untuk user 'ibua'\n";
    echo "Username: ibua\n";
    echo "Password baru: ibua123\n";
} else {
    echo "❌ User 'ibua' tidak ditemukan di database\n";
    echo "Silakan jalankan: php artisan db:seed --class=UserSeeder\n";
}


