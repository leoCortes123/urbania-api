<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListPropertyDocumentTypesRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:code,name,sort_order,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
