<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StartImpersonationRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario a suplantar es obligatorio.',
            'user_id.uuid' => 'El ID del usuario debe ser un UUID válido.',
            'reason.required' => 'El motivo de la suplantación es obligatorio.',
            'reason.max' => 'El motivo no puede exceder los 500 caracteres.',
        ];
    }
}
