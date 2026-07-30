<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTwoFactorResetReason;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Pengajuan reset 2FA dari halaman /2fa/verify — pengaju BELUM login.
 *
 * Identitas diambil dari session('2fa_user_id'), yang hanya diisi
 * LoginController SETELAH username + password terbukti benar. Jadi
 * endpoint ini tidak bisa dipakai orang yang tak tahu kata sandi akun.
 */
final class StoreTwoFactorResetRequestFromVerify extends FormRequest
{
    use ValidatesTwoFactorResetReason;

    /**
     * Sengaja true: sesi yang hilang dijawab dengan pengalihan ke login oleh
     * controller, bukan 403 — pengaju di sini adalah user sah yang terkunci.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * User yang sedang berada di tengah proses login (lolos password, belum 2FA).
     */
    public function pendingUser(): ?User
    {
        $userId = $this->session()->get('2fa_user_id');

        return $userId ? User::find($userId) : null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateResetEligibility($validator, $this->pendingUser());
        });
    }
}
