<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            Log::warning('Unauthenticated user attempted to access protected route', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

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

        // Check if user has the required role (case-insensitive)
        $userRole = strtolower($user->role);
        $requiredRoles = array_map('strtolower', $roles);

        // DEBUG LOGGING
        Log::info('CheckRole Debug:', [
            'user_id' => $user->id,
            'user_role_raw' => $user->role,
            'user_role_lower' => $userRole,
            'required_roles_raw' => $roles,
            'required_roles_lower' => $requiredRoles,
            'is_empty_roles' => empty($roles),
            'in_array_check' => in_array($userRole, $requiredRoles, true),
        ]);

        if (empty($roles) || !in_array($userRole, $requiredRoles, true)) {
            Log::warning('Unauthorized access attempt - FAILED CHECK', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized access.',
                    'code' => 403,
                    'required_role' => $roles[0] ?? 'unknown'
                ], 403);
            }

            // Redirect to user's dashboard if they have valid role
            return redirect($user->getDashboardRoute())
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        Log::info('Role check passed', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'role_lower' => $userRole,
            'required_roles' => $roles,
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}




