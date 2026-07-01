<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTemplateRequest extends FormRequest
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
            'nombre' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'cuerpo' => ['nullable', 'string'],
        ];
    }
}
