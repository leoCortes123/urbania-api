<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateAnnouncementRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string'],
            'segmento' => ['required', 'string', 'in:todos,torre,morosos,unidad'],
            'target_id' => ['required_if:segmento,torre,unidad', 'uuid'],
            'canales' => ['required', 'array', 'min:1'],
            'canales.*' => ['string', 'in:whatsapp,email,push'],
            'programado_para' => ['nullable', 'date'],
            'fijado' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'cuerpo.required' => 'El cuerpo es obligatorio.',
            'segmento.required' => 'El segmento es obligatorio.',
            'segmento.in' => 'El segmento seleccionado no es válido.',
            'target_id.required_if' => 'El target_id es obligatorio para el segmento seleccionado.',
            'canales.required' => 'Debe seleccionar al menos un canal.',
            'canales.*.in' => 'El canal seleccionado no es válido.',
            'programado_para.date' => 'La fecha programada no es válida.',
        ];
    }
}
