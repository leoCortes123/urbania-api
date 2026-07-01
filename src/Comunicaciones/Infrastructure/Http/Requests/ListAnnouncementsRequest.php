<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListAnnouncementsRequest extends FormRequest
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
            'estado' => ['nullable', 'string', 'in:borrador,programado,enviado'],
            'segmento' => ['nullable', 'string', 'in:todos,torre,morosos,unidad'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estado.in' => 'El estado no es válido.',
            'segmento.in' => 'El segmento no es válido.',
            'page.integer' => 'La página debe ser un número entero.',
            'per_page.integer' => 'El tamaño de página debe ser un número entero.',
        ];
    }
}
