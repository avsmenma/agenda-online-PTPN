<?php

/**
 * Debug script untuk masalah login
 * Jalankan: php debug_login.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG LOGIN ISSUE ===\n\n";

// 1. Cek default connection
echo "1. Default Connection: " . config('database.default') . "\n";

// 2. Cek database name dari default connection
$defaultConnection = config('database.connections.' . config('database.default'));
echo "2. Database Name: " . ($defaultConnection['database'] ?? 'NOT SET') . "\n";
echo "3. Database Host: " . ($defaultConnection['host'] ?? 'NOT SET') . "\n";
echo "4. Database Username: " . ($defaultConnection['username'] ?? 'NOT SET') . "\n\n";

// 3. Test koneksi langsung
try {
    $pdo = DB::connection()->getPdo();
    echo "5. PDO Connection: OK\n";
    echo "   Server Info: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n\n";
} catch (\Exception $e) {
    echo "5. PDO Connection: ERROR - " . $e->getMessage() . "\n\n";
}

// 4. Test query langsung ke tabel users
try {
    $count = DB::table('users')->count();
    echo "6. DB::table('users')->count(): {$count}\n";
} catch (\Exception $e) {
    echo "6. DB::table('users')->count(): ERROR - " . $e->getMessage() . "\n";
}

// 5. Test query dengan connection name
try {
    $count = DB::connection(config('database.default'))->table('users')->count();
    echo "7. DB::connection('" . config('database.default') . "')->table('users')->count(): {$count}\n";
} catch (\Exception $e) {
    echo "7. DB::connection()->table('users')->count(): ERROR - " . $e->getMessage() . "\n";
}

// 6. Test User model
try {
    $count = \App\Models\User::count();
    echo "8. User::count(): {$count}\n";
} catch (\Exception $e) {
    echo "8. User::count(): ERROR - " . $e->getMessage() . "\n";
}

// 7. Test User model dengan connection explicit
try {
    $user = new \App\Models\User();
    $connection = $user->getConnectionName();
    echo "9. User Model Connection: " . ($connection ?: 'default') . "\n";
} catch (\Exception $e) {
    echo "9. User Model Connection: ERROR - " . $e->getMessage() . "\n";
}

// 8. Test query user by username
try {
    $user = \App\Models\User::where('username', 'ibutara')->first();
    if ($user) {
        echo "10. User::where('username', 'ibutara')->first(): FOUND (ID: {$user->id})\n";
    } else {
        echo "10. User::where('username', 'ibutara')->first(): NOT FOUND\n";
    }
} catch (\Exception $e) {
    echo "10. User::where('username', 'ibutara')->first(): ERROR - " . $e->getMessage() . "\n";
}

// 9. Cek apakah ada cache config
$configCache = base_path('bootstrap/cache/config.php');
if (file_exists($configCache)) {
    echo "\n11. Config Cache File: EXISTS (mungkin perlu di-clear)\n";
    echo "    Jalankan: php artisan config:clear\n";
} else {
    echo "\n11. Config Cache File: NOT EXISTS\n";
}

echo "\n=== END DEBUG ===\n";

