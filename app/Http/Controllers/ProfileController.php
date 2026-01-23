<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

final class ProfileController extends Controller
{
    /**
     * Show the account/profile settings page
     */
    public function showAccount(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if 2FA is enabled (required to access account settings)
        if (!$user->hasTwoFactorEnabled()) {
            return view('profile.require-2fa');
        }

        return view('profile.account', [
            'user' => $user,
        ]);
    }

    /**
     * Update user email
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan oleh user lain',
            'password.required' => 'Password wajib diisi untuk konfirmasi perubahan email',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Check if 2FA is enabled (required)
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.account')
                ->with('error', '2FA harus diaktifkan terlebih dahulu untuk mengubah email.');
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password tidak valid.'],
            ]);
        }

        // Update email
        $oldEmail = $user->email;
        $user->email = $request->email;
        $user->save();

        Log::info('User email updated', [
            'user_id' => $user->id,
            'username' => $user->username,
            'old_email' => $oldEmail,
            'new_email' => $request->email,
        ]);

        return redirect()->route('profile.account')
            ->with('success', 'Email berhasil diubah.');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Check if 2FA is enabled (required)
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.account')
                ->with('error', '2FA harus diaktifkan terlebih dahulu untuk mengubah password.');
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak valid.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        Log::info('User password updated', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()->route('profile.account')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Update user username
     */
    public function updateUsername(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username,' . Auth::id()],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi',
            'username.string' => 'Username harus berupa teks',
            'username.max' => 'Username maksimal 255 karakter',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash dan underscore',
            'username.unique' => 'Username sudah digunakan oleh user lain',
            'password.required' => 'Password wajib diisi untuk konfirmasi perubahan username',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Check if 2FA is enabled (required)
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.account')
                ->with('error', '2FA harus diaktifkan terlebih dahulu untuk mengubah username.');
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password tidak valid.'],
            ]);
        }

        // Update username
        $oldUsername = $user->username;
        $user->username = $request->username;
        $user->save();

        Log::info('User username updated', [
            'user_id' => $user->id,
            'old_username' => $oldUsername,
            'new_username' => $request->username,
        ]);

        return redirect()->route('profile.account')
            ->with('success', 'Username berhasil diubah.');
    }
}






