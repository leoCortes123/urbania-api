<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Urbania\Authorization\Domain\Exceptions\AuthorizationContextException;

trait HandlesAuthorizationRequest
{
    private function organizationId(Request $request): string
    {
        $organizationId = $request->attributes->get('org_id');

        if (! is_string($organizationId) || $organizationId === '') {
            throw new AuthorizationContextException('Usuario sin organización asignada');
        }

        return $organizationId;
    }

    private function actorUserId(Request $request): string
    {
        $userId = $request->attributes->get('auth_user_id');

        if (! is_string($userId) || $userId === '') {
            throw new AuthorizationContextException('No autenticado');
        }

        return $userId;
    }

    private function isSaasOperator(Request $request): bool
    {
        $role = $request->attributes->get('auth_role');

        return $role === 'saas_operador';
    }
}
