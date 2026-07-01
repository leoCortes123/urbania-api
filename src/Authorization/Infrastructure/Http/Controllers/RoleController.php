<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Authorization\Application\DTOs\CreateRoleRequestDto;
use Urbania\Authorization\Application\DTOs\SetRolePermissionsRequestDto;
use Urbania\Authorization\Application\DTOs\UpdateRoleRequestDto;
use Urbania\Authorization\Application\UseCases\Roles\CreateRoleUseCase;
use Urbania\Authorization\Application\UseCases\Roles\ListRolesUseCase;
use Urbania\Authorization\Application\UseCases\Roles\SetRolePermissionsUseCase;
use Urbania\Authorization\Application\UseCases\Roles\UpdateRoleUseCase;
use Urbania\Authorization\Infrastructure\Http\Requests\CreateRoleRequest;
use Urbania\Authorization\Infrastructure\Http\Requests\ListRolesRequest;
use Urbania\Authorization\Infrastructure\Http\Requests\SetRolePermissionsRequest;
use Urbania\Authorization\Infrastructure\Http\Requests\UpdateRoleRequest;
use Urbania\Authorization\Infrastructure\Http\Resources\RoleCollection;
use Urbania\Authorization\Infrastructure\Http\Resources\RoleResource;

final class RoleController extends Controller
{
    use HandlesAuthorizationRequest;

    public function index(ListRolesRequest $request, ListRolesUseCase $useCase): JsonResponse
    {
        $roles = $useCase->execute($this->organizationId($request));
        $collection = new RoleCollection($roles);

        /** @var array{data: mixed} $resolved */
        $resolved = $collection->resolve($request);

        return response()->json([
            'data' => $resolved['data'],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function store(CreateRoleRequest $request, CreateRoleUseCase $useCase): JsonResponse
    {
        /** @var array{nombre: string, descripcion?: string|null, nivel_alcance: string, base_role_id?: string|null} $validated */
        $validated = $request->validated();

        $dto = new CreateRoleRequestDto(
            name: $validated['nombre'],
            description: $validated['descripcion'] ?? null,
            level: $validated['nivel_alcance'],
            baseRoleId: $validated['base_role_id'] ?? null,
            organizationId: $this->organizationId($request),
        );

        $role = $useCase->execute($dto, $this->actorUserId($request));
        $resource = new RoleResource($role);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function update(UpdateRoleRequest $request, string $id, UpdateRoleUseCase $useCase): JsonResponse
    {
        /** @var array{nombre?: string, descripcion?: string|null, nivel_alcance?: string} $validated */
        $validated = $request->validated();

        $dto = new UpdateRoleRequestDto(
            name: $validated['nombre'] ?? null,
            description: $validated['descripcion'] ?? null,
            level: $validated['nivel_alcance'] ?? null,
        );

        $role = $useCase->execute(
            $id,
            $dto,
            $this->organizationId($request),
            $this->isSaasOperator($request),
            $this->actorUserId($request),
        );

        $resource = new RoleResource($role);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function setPermissions(SetRolePermissionsRequest $request, string $id, SetRolePermissionsUseCase $useCase): JsonResponse
    {
        /** @var array{permissions: array<int, string>} $validated */
        $validated = $request->validated();

        $dto = new SetRolePermissionsRequestDto(
            roleId: $id,
            permissions: $validated['permissions'],
            organizationId: $this->organizationId($request),
        );

        $role = $useCase->execute($dto, $this->actorUserId($request));
        $resource = new RoleResource($role);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }
}
