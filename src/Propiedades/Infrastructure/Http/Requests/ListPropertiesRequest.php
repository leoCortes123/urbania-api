<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListPropertiesRequest extends FormRequest
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
            'condominium_id' => ['nullable', 'uuid', 'exists:condominiums,id'],
            'tower_id' => ['nullable', 'uuid', 'exists:towers,id'],
            'property_type_id' => ['nullable', 'uuid', 'exists:property_types,id'],
            'property_status_id' => ['nullable', 'uuid', 'exists:property_statuses,id'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'floor_min' => ['nullable', 'integer', 'min:0'],
            'floor_max' => ['nullable', 'integer', 'min:0'],
            'search' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:floor,unit_number,area_m2,coefficient,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
