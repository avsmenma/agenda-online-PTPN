<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * SECURITY FIX: This middleware now only checks authentication status.
 * Auto-login functionality has been REMOVED for security reasons.
 * All routes must use proper authentication via login page.
 */
class AutoLoginMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * SECURITY: This middleware now only checks if user is authenticated.
     * It does NOT perform auto-login. Users must login via /login route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        // SECURITY: Only check if user is authenticated
        if (!Auth::check()) {
            Log::warning('SECURITY: Unauthenticated access attempt blocked by AutoLoginMiddleware', [
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

            // Redirect to login page
            return redirect('/login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        // User is authenticated, proceed
        return $next($request);
    }
}

