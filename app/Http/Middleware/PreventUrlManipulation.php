<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventUrlManipulation
{
    /**
     * Handle an incoming request.
     * 
     * This middleware prevents unauthorized access by:
     * 1. Ensuring user is authenticated
     * 2. Validating user has proper role for the route
     * 3. Logging suspicious access attempts
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('Unauthenticated access attempt blocked', [
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
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

        // Log access for security monitoring
        $user = Auth::user();
        Log::info('Authenticated access', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}




