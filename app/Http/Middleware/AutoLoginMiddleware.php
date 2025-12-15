<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AutoLoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is already logged in
        if (Auth::check()) {
            return $next($request);
        }

        // Auto-login based on URL parameter or default role
        $role = $request->query('role', 'IbuA'); // Default to IbuA

        // Map role names to user data
        $roleMap = [
            'IbuA' => ['name' => 'IbuA', 'email' => 'ibua@ptpn.com'],
            'ibuB' => ['name' => 'IbuB', 'email' => 'ibub@ptpn.com'],
            'Perpajakan' => ['name' => 'Perpajakan', 'email' => 'perpajakan@ptpn.com'],
            'Akutansi' => ['name' => 'Akutansi', 'email' => 'akutansi@ptpn.com'],
            'Pembayaran' => ['name' => 'Pembayaran', 'email' => 'pembayaran@ptpn.com']
        ];

        $userData = $roleMap[$role] ?? $roleMap['IbuA'];

        // Normalize role casing to match User::ROLES
        $normalizedRole = match ($role) {
            'ibuB', 'IbuB', 'Ibu B' => 'IbuB',
            'IbuA', 'ibuA', 'Ibu A' => 'IbuA',
            'pembayaran', 'Pembayaran' => 'Pembayaran',
            'perpajakan', 'Perpajakan' => 'Perpajakan',
            'akutansi', 'Akutansi' => 'Akutansi',
            'admin', 'Admin' => 'Admin',
            default => $role
        };

        // Find or create user - use updateOrCreate to fix existing user roles
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'username' => strtolower(str_replace(' ', '', $userData['name'])), // Generate username from name
                'password' => bcrypt('password'), // Default password
                'role' => $normalizedRole,
            ]
        );

        // Log in the user
        Auth::login($user);

        // Store current role in session for layout
        session(['current_role' => $normalizedRole]);

        return $next($request);
    }
}