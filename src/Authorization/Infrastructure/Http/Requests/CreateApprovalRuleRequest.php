<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateApprovalRuleRequest extends FormRequest
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
            'resource' => ['required', 'string', 'max:50'],
            'action' => ['required', 'string', 'max:50'],
            'threshold' => ['nullable', 'numeric', 'min:0'],
            'approver_role_id' => ['required', 'uuid', 'exists:roles,id'],
            'requires_second_approval' => ['sometimes', 'boolean'],
        ];
    }
}
