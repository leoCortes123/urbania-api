<?php

declare(strict_types=1);

namespace Urbania\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Urbania\Authorization\Domain\Services\PermissionResolverInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class AuthorizationMiddleware
{
    /** @var array<string, array{resource: string, action: string}> */
    private const array ROUTE_PERMISSION_MAP = [
        'propiedades.index' => ['resource' => 'propiedades', 'action' => 'ver'],
        'propiedades.store' => ['resource' => 'propiedades', 'action' => 'crear'],
        'propiedades.update' => ['resource' => 'propiedades', 'action' => 'editar'],
        'propiedades.destroy' => ['resource' => 'propiedades', 'action' => 'eliminar'],
        'directorio.index' => ['resource' => 'directorio', 'action' => 'ver'],
        'directorio.store' => ['resource' => 'directorio', 'action' => 'crear'],
        'directorio.update' => ['resource' => 'directorio', 'action' => 'editar'],
        'directorio.destroy' => ['resource' => 'directorio', 'action' => 'eliminar'],
    ];

    public function __construct(
        private PermissionResolverInterface $permissionResolver,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        if ($route === null) {
            return $next($request);
        }

        $routeName = $route->getName();
        if ($routeName === null || ! isset(self::ROUTE_PERMISSION_MAP[$routeName])) {
            return $next($request);
        }

        $permission = self::ROUTE_PERMISSION_MAP[$routeName];
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'No autenticado',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 401);
        }

        /** @var string $userIdRaw */
        $userIdRaw = $user->id;
        $userId = Uuid::fromString($userIdRaw);

        /** @var string|null $organizationIdRaw */
        $organizationIdRaw = $user->organization_id;
        if ($organizationIdRaw === null || $organizationIdRaw === '') {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Usuario sin organización asignada',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 403);
        }

        $organizationId = Uuid::fromString($organizationIdRaw);

        if (! $this->permissionResolver->can($userId, $permission['resource'], $permission['action'], 'organization', $organizationId)) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => "Acción '{$permission['action']}' no permitida sobre '{$permission['resource']}'",
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 403);
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
