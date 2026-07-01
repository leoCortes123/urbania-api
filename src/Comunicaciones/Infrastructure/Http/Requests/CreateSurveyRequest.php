<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateSurveyRequest extends FormRequest
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
            'pregunta' => ['required', 'string', 'max:500'],
            'tipo' => ['required', 'string', 'in:simple,multiple'],
            'cierra_el' => ['nullable', 'date'],
            'opciones' => ['required', 'array', 'min:2'],
            'opciones.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pregunta.required' => 'La pregunta es obligatoria.',
            'tipo.required' => 'El tipo de encuesta es obligatorio.',
            'tipo.in' => 'El tipo de encuesta no es válido.',
            'opciones.required' => 'Debe incluir al menos dos opciones.',
            'opciones.min' => 'Debe incluir al menos dos opciones.',
            'opciones.*.required' => 'El texto de la opción es obligatorio.',
        ];
    }
}
