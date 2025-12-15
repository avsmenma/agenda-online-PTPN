<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'pembayaran@ptpn.com';
$user = User::where('email', $email)->first();

echo "User Check for ($email):\n";
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Role (Raw DB): '" . $user->role . "'\n";
    echo "Role (Lower): '" . strtolower($user->role) . "'\n";
} else {
    echo "User not found!\n";
}

echo "\nChecking all users:\n";
$users = User::all();
foreach ($users as $u) {
    echo "Email: {$u->email} | Role: '{$u->role}'\n";
}
