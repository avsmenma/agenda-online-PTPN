<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Otorisasi channel privat: user hanya boleh mendengarkan channel
 * departemen/role-nya sendiri (admin & owner boleh semua).
 * Pembanding lower-case agar konsisten dengan penamaan role campuran.
 */
$roleMatches = function ($user, string $target): bool {
    if (! $user) {
        return false;
    }
    $userRole = strtolower((string) ($user->role ?? ''));
    if (in_array($userRole, ['admin', 'owner'], true)) {
        return true;
    }
    return $userRole === strtolower($target);
};

// Private channel notifikasi dokumen per departemen
Broadcast::channel('documents.{department}', function ($user, $department) use ($roleMatches) {
    return $roleMatches($user, $department);
});

// Private channel notifikasi inbox per role
Broadcast::channel('inbox.{role}', function ($user, $role) use ($roleMatches) {
    return $roleMatches($user, $role);
});

