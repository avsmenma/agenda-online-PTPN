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

// Private channel for document notifications per department
Broadcast::channel('documents.{department}', function ($user, $department) {
    // Log channel authorization attempt for debugging
    \Log::info('Channel authorization request', [
        'channel' => 'documents.' . $department,
        'user_id' => $user?->id ?? 'guest',
        'user_authenticated' => auth()->check(),
        'session_module' => session('current_module'),
        'requested_department' => $department,
        'ip_address' => request()->ip(),
        'csrf_token' => csrf_token(),
    ]);

    // For development: allow all access to IbuB channel
    if ($department === 'ibuB') {
        \Log::info('IbuB channel access granted - development mode');
        return true;
    }

    // In production, you should implement proper user authorization
    // For now, allow all for testing
    \Log::info('Channel access granted for testing');
    return true;
});

// Private channel for inbox notifications per role
Broadcast::channel('inbox.{role}', function ($user, $role) {
    // Always allow in development for testing
    \Log::info('Inbox channel authorization attempt', [
        'channel' => 'inbox.' . $role,
        'user_id' => $user->id ?? 'guest',
        'user_role' => $user->role ?? 'none',
        'user_name' => $user->name ?? 'none',
        'requested_role' => $role,
        'auth_check' => auth()->check(),
        'session_valid' => session()->isStarted(),
    ]);

    // FOR DEVELOPMENT: Allow all authenticated users
    if (auth()->check()) {
        \Log::info('✅ Channel access granted - development mode', [
            'channel' => 'inbox.' . $role,
            'user_id' => $user->id,
            'user_role' => $user->role
        ]);
        return true;
    }

    \Log::error('❌ Channel access denied - user not authenticated', [
        'channel' => 'inbox.' . $role,
        'user_id' => $user->id ?? 'none'
    ]);

    return false;
});

