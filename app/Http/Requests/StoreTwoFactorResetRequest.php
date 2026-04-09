<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TwoFactorResetRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreTwoFactorResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            if (!$user) {
                return;
            }

            if (!$user->hasTwoFactorEnabled()) {
                $validator->errors()->add('reason', '2FA belum aktif, tidak bisa mengajukan reset.');
                return;
            }

            $exists = TwoFactorResetRequest::query()
                ->where('requester_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                $validator->errors()->add('reason', 'Anda masih memiliki pengajuan reset 2FA yang berstatus pending.');
            }
        });
    }
}

