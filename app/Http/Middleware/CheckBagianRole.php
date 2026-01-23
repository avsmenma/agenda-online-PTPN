<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user has a bagian role.
 * Allows any user with a role starting with 'bagian_' to access the route.
 */
final class CheckBagianRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'code' => 401
                ], 401);
            }

            return redirect('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Check if user has a bagian role (starts with 'bagian_')
        if (!str_starts_with($userRole, 'bagian_')) {
            Log::warning('Non-bagian user attempted to access bagian route', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'path' => $request->path(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized access. This route is only for bagian users.',
                    'code' => 403
                ], 403);
            }

            return redirect($user->getDashboardRoute())
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        // Also check if bagian_code is set
        if (empty($user->bagian_code)) {
            Log::warning('Bagian user without bagian_code attempted access', [
                'user_id' => $user->id,
                'user_role' => $user->role,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bagian code not configured for this user.',
                    'code' => 403
                ], 403);
            }

            return redirect('/login')
                ->with('error', 'Konfigurasi akun Bagian tidak lengkap. Hubungi administrator.');
        }

        Log::info('Bagian role check passed', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'bagian_code' => $user->bagian_code,
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}


