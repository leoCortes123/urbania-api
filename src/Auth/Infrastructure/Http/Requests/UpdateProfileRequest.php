<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'regex:/^\\+?[0-9]{7,15}$/'],
            'avatar' => ['sometimes', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value)) {
                    $fail('El avatar debe ser una cadena base64 válida.');

                    return;
                }

                $decoded = base64_decode($value, true);

                if ($decoded === false) {
                    $fail('El avatar no es una cadena base64 válida.');

                    return;
                }

                if (strlen($decoded) > 2 * 1024 * 1024) {
                    $fail('El avatar no debe superar los 2MB.');

                    return;
                }

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($decoded);

                if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    $fail('El avatar debe ser una imagen JPEG, PNG o WebP.');
                }
            }],
        ];
    }
}
