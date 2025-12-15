<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'password' => ['required', 'string', 'min:6'],
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
            'password.min' => 'Password minimal 6 karakter',
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
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $credentials = $this->getCredentials();
        
        // Find user by username instead of email
        $user = User::where('username', $credentials['username'])->first();
        
        // Check if user exists and password matches
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }
        
        // Log in the user
        Auth::login($user, $this->boolean('remember'));
        
        session()->regenerate();
    }
}
