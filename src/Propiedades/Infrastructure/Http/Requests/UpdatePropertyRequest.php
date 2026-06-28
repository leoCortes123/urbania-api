<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePropertyRequest extends FormRequest
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
            'tower_id' => ['sometimes', 'uuid', 'exists:towers,id'],
            'property_type_id' => ['sometimes', 'uuid', 'exists:property_types,id'],
            'floor' => ['sometimes', 'integer', 'min:0'],
            'unit_number' => ['sometimes', 'string', 'max:20'],
            'area_m2' => ['sometimes', 'numeric', 'min:0.01'],
            'coefficient' => ['sometimes', 'numeric', 'min:0.000001'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'has_parking' => ['nullable', 'boolean'],
            'parking_lot' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
