<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MfaDisableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => 'La contraseña es obligatoria',
            'code.required' => 'El código MFA es obligatorio',
            'code.size' => 'El código MFA debe tener 6 dígitos',
        ];
    }
}
