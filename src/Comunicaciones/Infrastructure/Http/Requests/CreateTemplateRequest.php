<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateTemplateRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'cuerpo' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'cuerpo.required' => 'El cuerpo es obligatorio.',
        ];
    }
}
