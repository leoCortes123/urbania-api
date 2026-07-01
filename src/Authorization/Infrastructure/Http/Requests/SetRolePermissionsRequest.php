<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SetRolePermissionsRequest extends FormRequest
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
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}
