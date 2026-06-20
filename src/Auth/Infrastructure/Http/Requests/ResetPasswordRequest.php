<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ResetPasswordRequest extends FormRequest
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
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }
}
