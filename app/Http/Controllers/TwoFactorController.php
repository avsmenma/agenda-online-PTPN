<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA setup page
     */
    public function showSetup(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return view('auth.2fa.already-enabled');
        }

        // Generate secret key if not exists
        if (!$user->two_factor_secret) {
            $secretKey = $this->google2fa->generateSecretKey();
            $user->two_factor_secret = encrypt($secretKey);
            $user->save();
        }

        $secretKey = decrypt($user->two_factor_secret);
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'Agenda Online PTPN'),
            $user->email,
            $secretKey
        );

        return view('auth.2fa.setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secretKey' => $secretKey,
        ]);
    }

    /**
     * Verify and enable 2FA
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi',
            'code.size' => 'Kode verifikasi harus 6 digit',
            'code.regex' => 'Kode verifikasi harus berupa 6 digit angka',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('2fa.setup')
                ->with('error', '2FA sudah diaktifkan sebelumnya.');
        }

        if (!$user->two_factor_secret) {
            return redirect()->route('2fa.setup')
                ->with('error', 'Secret key tidak ditemukan. Silakan refresh halaman.');
        }

        $secretKey = decrypt($user->two_factor_secret);

        // Verify the code
        $valid = $this->google2fa->verifyKey($secretKey, $request->code, 2); // 2 = 60 seconds window

        if (!$valid) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Kode verifikasi tidak valid. Pastikan kode dari aplikasi authenticator Anda.']);
        }

        // Generate recovery codes
        $recoveryCodes = collect(range(1, 8))->map(function () {
            return strtoupper(Str::random(10));
        })->all();

        // Enable 2FA
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $user->save();

        Log::info('2FA enabled for user', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()->route('2fa.recovery-codes')
            ->with('success', '2FA berhasil diaktifkan! Silakan simpan recovery codes Anda.');
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->two_factor_enabled || !$user->two_factor_recovery_codes) {
            return redirect()->route('2fa.setup');
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        return view('auth.2fa.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Show 2FA verification page (after login)
     */
    public function showVerify(): View
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login')
                ->with('error', 'Sesi verifikasi 2FA tidak ditemukan. Silakan login ulang.');
        }

        return view('auth.2fa.verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi',
            'code.size' => 'Kode verifikasi harus 6 digit',
            'code.regex' => 'Kode verifikasi harus berupa 6 digit angka',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi verifikasi 2FA tidak ditemukan. Silakan login ulang.');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('2fa_user_id');
            return redirect()->route('login')
                ->with('error', 'User tidak ditemukan. Silakan login ulang.');
        }

        if (!$user->two_factor_secret) {
            session()->forget('2fa_user_id');
            return redirect()->route('login')
                ->with('error', 'Secret key tidak ditemukan. Silakan hubungi administrator.');
        }

        $secretKey = decrypt($user->two_factor_secret);

        // Verify the code (allow 2 time windows = 60 seconds)
        $valid = $this->google2fa->verifyKey($secretKey, $request->code, 2);

        if (!$valid) {
            Log::warning('2FA verification failed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['code' => 'Kode verifikasi tidak valid. Pastikan kode dari aplikasi authenticator Anda.']);
        }

        // Clear 2FA session and login user
        session()->forget('2fa_user_id');
        Auth::login($user, session('2fa_remember', false));
        session()->forget('2fa_remember');
        session()->regenerate();

        Log::info('2FA verification successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->intended($user->getDashboardRoute())
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * Verify using recovery code
     */
    public function verifyRecoveryCode(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => ['required', 'string', 'size:10', 'regex:/^[A-Z0-9]{10}$/'],
        ], [
            'recovery_code.required' => 'Recovery code wajib diisi',
            'recovery_code.size' => 'Recovery code harus 10 karakter',
            'recovery_code.regex' => 'Recovery code format tidak valid',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi verifikasi 2FA tidak ditemukan. Silakan login ulang.');
        }

        $user = User::find($userId);
        if (!$user || !$user->two_factor_recovery_codes) {
            session()->forget('2fa_user_id');
            return redirect()->route('login')
                ->with('error', 'Recovery codes tidak ditemukan.');
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $recoveryCode = strtoupper(trim($request->recovery_code));

        if (!in_array($recoveryCode, $recoveryCodes)) {
            Log::warning('Invalid recovery code used', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['recovery_code' => 'Recovery code tidak valid atau sudah digunakan.']);
        }

        // Remove used recovery code
        $recoveryCodes = array_values(array_filter($recoveryCodes, fn($code) => $code !== $recoveryCode));
        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $user->save();

        // Clear 2FA session and login user
        session()->forget('2fa_user_id');
        Auth::login($user, session('2fa_remember', false));
        session()->forget('2fa_remember');
        session()->regenerate();

        Log::info('Recovery code used successfully', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->intended($user->getDashboardRoute())
            ->with('success', 'Selamat datang, ' . $user->name . '!')
            ->with('warning', 'Recovery code telah digunakan. Silakan generate recovery codes baru di pengaturan 2FA.');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi untuk menonaktifkan 2FA',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!\Hash::check($request->password, $user->password)) {
            return back()
                ->withInput()
                ->withErrors(['password' => 'Password tidak valid.']);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        Log::info('2FA disabled for user', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()->route('2fa.setup')
            ->with('success', '2FA berhasil dinonaktifkan.');
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        $recoveryCodes = collect(range(1, 8))->map(function () {
            return strtoupper(Str::random(10));
        })->all();

        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $user->save();

        Log::info('Recovery codes regenerated', [
            'user_id' => $user->id,
            'username' => $user->username,
        ]);

        return redirect()->route('2fa.recovery-codes')
            ->with('success', 'Recovery codes berhasil di-generate ulang. Silakan simpan recovery codes baru Anda.');
    }
}







