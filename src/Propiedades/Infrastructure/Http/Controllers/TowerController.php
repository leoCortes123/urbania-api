<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\CreateTowerRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdateTowerRequestDto;
use Urbania\Propiedades\Application\UseCases\Towers\CreateTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\DeleteTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\GetTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\ListTowersUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\UpdateTowerUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\CreateTowerRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListTowersRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdateTowerRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\TowerCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\TowerResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class TowerController extends Controller
{
    public function index(ListTowersRequest $request, string $condominiumId, ListTowersUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute(
            condominiumId: $condominiumId,
            filters: [
                'search' => $validated['search'] ?? null,
                'sort_by' => $validated['sort_by'] ?? null,
                'sort_order' => $validated['sort_order'] ?? null,
            ],
            page: $page,
            perPage: $perPage,
        );

        $collection = new TowerCollection($result);

        /** @var array{data: mixed, meta: array<string, mixed>} $resolved */
        $resolved = $collection->resolve($request);

        return response()->json([
            'data' => $resolved['data'],
            'meta' => array_merge(
                $resolved['meta'],
                ['trace_id' => $request->attributes->get('trace_id')],
            ),
        ]);
    }

    public function store(CreateTowerRequest $request, CreateTowerUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumId */
        $condominiumId = $request->validated('condominium_id');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $code */
        $code = $request->validated('code');
        /** @var int $floorCount */
        $floorCount = $request->validated('floor_count');
        /** @var bool $hasElevator */
        $hasElevator = (bool) $request->validated('has_elevator');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new CreateTowerRequestDto(
            condominiumId: Uuid::fromString($condominiumId),
            name: $name,
            code: $code,
            floorCount: $floorCount,
            hasElevator: $hasElevator,
            description: $description,
            sortOrder: $sortOrder ?? 0,
        );

        $result = $useCase->execute($dto);
        $resource = new TowerResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function show(string $id, GetTowerUseCase $useCase, ListTowersRequest $request): JsonResponse
    {
        $result = $useCase->execute($id);
        $resource = new TowerResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function update(UpdateTowerRequest $request, string $id, UpdateTowerUseCase $useCase): JsonResponse
    {
        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $code */
        $code = $request->validated('code');
        /** @var int|null $floorCount */
        $floorCount = $request->validated('floor_count');
        /** @var bool|null $hasElevator */
        $hasElevator = $request->validated('has_elevator');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new UpdateTowerRequestDto(
            name: $name,
            code: $code,
            floorCount: $floorCount,
            hasElevator: $hasElevator,
            description: $description,
            sortOrder: $sortOrder,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new TowerResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeleteTowerUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(null, 204);
    }
}
