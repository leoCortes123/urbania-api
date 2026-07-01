<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListSurveysRequest extends FormRequest
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
            'activa' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activa.boolean' => 'El filtro activa debe ser un valor booleano.',
            'page.integer' => 'La página debe ser un número entero.',
            'page.min' => 'La página debe ser al menos 1.',
        ];
    }
}
