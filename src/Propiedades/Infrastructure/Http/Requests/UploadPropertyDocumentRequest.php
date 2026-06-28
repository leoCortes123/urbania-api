<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UploadPropertyDocumentRequest extends FormRequest
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
            'property_document_type_id' => ['required', 'uuid', 'exists:property_document_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:20480'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
