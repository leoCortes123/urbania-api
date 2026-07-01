<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateChannelRequest extends FormRequest
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
            'canal' => ['required', 'string', 'in:whatsapp,email,push'],
            'provider' => ['nullable', 'string', 'max:50'],
            'config' => ['nullable', 'array'],
            'activo' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'canal.required' => 'El canal es obligatorio.',
            'canal.in' => 'El canal no es válido.',
            'activo.required' => 'El estado activo es obligatorio.',
        ];
    }
}
