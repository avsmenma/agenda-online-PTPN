<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip_address' => $request->ip(),
            ]);

            // Redirect to role-specific dashboard
            return redirect()
                ->intended($user->getDashboardRoute())
                ->with('success', 'Selamat datang, ' . $user->name . '!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Login attempt failed', [
                'username' => $request->input('username'),
                'ip_address' => $request->ip(),
                'errors' => $e->errors(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error occurred', [
                'username' => $request->input('username'),
                'ip_address' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Terjadi kesalahan saat login. Silakan coba lagi.',
                ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            Log::info('User logged out', [
                'user_id' => $user?->id,
                'username' => $user?->username,
                'role' => $user?->role,
            ]);

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->with('success', 'Anda telah berhasil keluar dari sistem.');

        } catch (\Exception $e) {
            Log::error('Logout error occurred', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect('/login')
                ->with('error', 'Terjadi kesalahan saat logout. Silakan coba lagi.');
        }
    }

    /**
     * Show the user's dashboard based on their role.
     */
    public function dashboard(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        return redirect($user->getDashboardRoute());
    }
}
