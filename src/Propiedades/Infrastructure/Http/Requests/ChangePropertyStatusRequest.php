<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePropertyStatusRequest extends FormRequest
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
            'property_status_id' => ['required', 'uuid', 'exists:property_statuses,id'],
            'reason' => ['required', 'string', 'min:5'],
        ];
    }
}
