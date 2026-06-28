<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreatePropertyRequest extends FormRequest
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
            'tower_id' => ['required', 'uuid', 'exists:towers,id'],
            'property_type_id' => ['required', 'uuid', 'exists:property_types,id'],
            'property_status_id' => ['nullable', 'uuid', 'exists:property_statuses,id'],
            'floor' => ['required', 'integer', 'min:0'],
            'unit_number' => ['required', 'string', 'max:20'],
            'area_m2' => ['required', 'numeric', 'min:0.01'],
            'coefficient' => ['required', 'numeric', 'min:0.000001'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'has_parking' => ['nullable', 'boolean'],
            'parking_lot' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
