<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateInvitationRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'invitee_email' => ['required', 'email', 'max:255'],
            'invitee_name' => ['required', 'string', 'max:255'],
            'occupant_type_id' => ['required', 'uuid'],
        ];
    }
}
