<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTwoFactorResetReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Pengajuan reset 2FA dari halaman profil — pengaju sudah login.
 *
 * Aturan alasan & kelayakan dibagi dengan jalur /2fa/verify lewat
 * ValidatesTwoFactorResetReason; yang khas di sini hanyalah cara
 * mengenali pengaju (user terautentikasi).
 */
final class StoreTwoFactorResetRequest extends FormRequest
{
    use ValidatesTwoFactorResetReason;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateResetEligibility($validator, $this->user());
        });
    }
}
