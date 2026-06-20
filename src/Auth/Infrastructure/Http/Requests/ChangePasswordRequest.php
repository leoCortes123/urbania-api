<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'new_password_confirmation' => ['required', 'string', 'same:new_password'],
        ];
    }
}
