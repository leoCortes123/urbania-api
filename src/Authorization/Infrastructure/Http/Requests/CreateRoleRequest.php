<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRoleRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'nivel_alcance' => ['required', 'in:organization,condominium,tower,unit'],
            'base_role_id' => ['nullable', 'uuid', 'exists:roles,id'],
        ];
    }
}
