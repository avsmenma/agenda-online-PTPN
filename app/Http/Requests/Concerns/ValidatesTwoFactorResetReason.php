<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\TwoFactorResetRequest;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * Aturan bersama pengajuan reset 2FA.
 *
 * Dipakai dua jalur yang bedanya HANYA cara mengenali pengaju:
 * - StoreTwoFactorResetRequest            → pengaju sudah login ($this->user())
 * - StoreTwoFactorResetRequestFromVerify  → pengaju belum login (session 2fa_user_id)
 *
 * Aturannya sengaja ditaruh di satu tempat supaya tidak melahirkan salinan
 * kedua yang bisa menyimpang diam-diam.
 */
trait ValidatesTwoFactorResetReason
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter.',
        ];
    }

    /**
     * Pengaju harus punya 2FA aktif dan belum punya pengajuan yang menggantung.
     * Pengaju yang tak dikenal dilewatkan di sini — penolakannya urusan pemanggil.
     */
    protected function validateResetEligibility(Validator $validator, ?User $user): void
    {
        if (!$user) {
            return;
        }

        if (!$user->hasTwoFactorEnabled()) {
            $validator->errors()->add('reason', '2FA belum aktif, tidak bisa mengajukan reset.');

            return;
        }

        $hasPending = TwoFactorResetRequest::query()
            ->where('requester_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            $validator->errors()->add('reason', 'Anda masih memiliki pengajuan reset 2FA yang berstatus pending.');
        }
    }
}
