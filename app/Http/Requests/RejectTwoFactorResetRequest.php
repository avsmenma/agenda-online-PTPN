<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RejectTwoFactorResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('two_factor_reset_requests', 'id')->where('status', 'pending'),
            ],
            'notes' => ['required', 'string', 'min:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Alasan penolakan wajib diisi.',
            'notes.min' => 'Alasan penolakan minimal 3 karakter.',
        ];
    }
}

