<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRoleRequest extends FormRequest
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
            'nombre' => ['sometimes', 'string', 'max:100'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'nivel_alcance' => ['sometimes', 'in:organization,condominium,tower,unit'],
        ];
    }
}
