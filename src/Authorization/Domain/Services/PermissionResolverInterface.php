<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Services;

use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PermissionResolverInterface
{
    /**
     * Resuelve los permisos efectivos de un usuario en un scope dado.
     * Combina asignaciones explícitas + derivados de property_occupants (residentes).
     *
     * @return array<string> Formato: "recurso.accion"
     */
    public function resolvePermissions(Uuid $userId, string $scopeType, Uuid $scopeId): array;

    /**
     * Verifica si un usuario tiene un permiso específico en el scope dado.
     */
    public function can(Uuid $userId, string $resource, string $action, string $scopeType, Uuid $scopeId): bool;
}
