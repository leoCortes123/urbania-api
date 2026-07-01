<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateAssignmentRequest extends FormRequest
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
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'scope_type' => ['required', 'in:organization,condominium,tower,unit'],
            'scope_id' => ['required', 'uuid'],
            'vigencia_inicio' => ['nullable', 'date'],
            'vigencia_fin' => ['nullable', 'date', 'after_or_equal:vigencia_inicio'],
        ];
    }
}
