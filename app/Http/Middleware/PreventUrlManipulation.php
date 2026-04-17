<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * DEPRECATED: This middleware is no longer registered in bootstrap/app.php.
 * Kept for reference only. Use Laravel's built-in 'auth' middleware instead.
 *
 * Original purpose: Prevent unauthenticated URL access.
 * Why deprecated: Duplicates Laravel's 'auth' middleware functionality.
 */
class PreventUrlManipulation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            Log::warning('Unauthenticated access attempt blocked', [
                'path' => $request->path(),
                'ip' => $request->ip(),
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

        // REMOVED: Log::info('Authenticated access') — was logging every single request,
        // causing log bloating and potential information leakage (user_id, username, IP, path).

        return $next($request);
    }
}
