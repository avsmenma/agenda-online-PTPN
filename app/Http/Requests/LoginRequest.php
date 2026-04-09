<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

final class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.string' => 'Username harus berupa teks',
            'username.max' => 'Username maksimal 255 karakter',
            'password.required' => 'Password wajib diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 8 karakter',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
        ];
    }

    /**
     * Get the validated credentials.
     *
     * @return array<string, string>
     */
    public function getCredentials(): array
    {
        return $this->only(['username', 'password']);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('username')) . '|' . $this->ip()
        );
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->getCredentials();
        
        // Find user by username OR email
        $loginField = $credentials['username'];
        $user = User::where(function($query) use ($loginField) {
            $query->where('username', $loginField)
                  ->orWhere('email', $loginField);
        })->first();
        
        // Check if user exists and password matches
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey(), 300); // Block selama 5 menit setelah terlalu banyak percobaan

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }
        
        RateLimiter::clear($this->throttleKey());

        // Log in the user
        Auth::login($user, $this->boolean('remember'));
        
        session()->regenerate();
    }
}
