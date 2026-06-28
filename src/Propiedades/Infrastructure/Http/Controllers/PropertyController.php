<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\ChangePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\CreatePropertyRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyRequestDto;
use Urbania\Propiedades\Application\UseCases\Properties\ChangePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\CreatePropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\DeletePropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\GetPropertyStatusLogUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\GetPropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\ListPropertiesUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\UpdatePropertyUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\ChangePropertyStatusRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\CreatePropertyRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListPropertiesRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdatePropertyRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyResource;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyStatusLogCollection;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyController extends Controller
{
    public function index(ListPropertiesRequest $request, ListPropertiesUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute(
            filters: [
                'condominium_id' => $validated['condominium_id'] ?? null,
                'tower_id' => $validated['tower_id'] ?? null,
                'property_type_id' => $validated['property_type_id'] ?? null,
                'property_status_id' => $validated['property_status_id'] ?? null,
                'floor' => $validated['floor'] ?? null,
                'floor_min' => $validated['floor_min'] ?? null,
                'floor_max' => $validated['floor_max'] ?? null,
                'search' => $validated['search'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
                'sort_by' => $validated['sort_by'] ?? null,
                'sort_order' => $validated['sort_order'] ?? null,
            ],
            page: $page,
            perPage: $perPage,
        );

        $collection = new PropertyCollection($result);

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

    public function store(CreatePropertyRequest $request, CreatePropertyUseCase $useCase): JsonResponse
    {
        /** @var string $towerId */
        $towerId = $request->validated('tower_id');
        /** @var string $propertyTypeId */
        $propertyTypeId = $request->validated('property_type_id');
        /** @var string|null $propertyStatusId */
        $propertyStatusId = $request->validated('property_status_id');
        /** @var int $floor */
        $floor = $request->validated('floor');
        /** @var string $unitNumber */
        $unitNumber = $request->validated('unit_number');
        /** @var string $areaM2 */
        $areaM2 = $request->validated('area_m2');
        /** @var string $coefficient */
        $coefficient = $request->validated('coefficient');
        /** @var int|null $bedrooms */
        $bedrooms = $request->validated('bedrooms');
        /** @var int|null $bathrooms */
        $bathrooms = $request->validated('bathrooms');
        /** @var bool $hasParking */
        $hasParking = (bool) $request->validated('has_parking');
        /** @var string|null $parkingLot */
        $parkingLot = $request->validated('parking_lot');
        /** @var string|null $notes */
        $notes = $request->validated('notes');
        /** @var string $changedByUserId */
        $changedByUserId = $request->attributes->get('auth_user_id');

        $dto = new CreatePropertyRequestDto(
            towerId: Uuid::fromString($towerId),
            propertyTypeId: Uuid::fromString($propertyTypeId),
            propertyStatusId: $propertyStatusId === null ? null : Uuid::fromString($propertyStatusId),
            floor: $floor,
            unitNumber: $unitNumber,
            areaM2: $areaM2,
            coefficient: $coefficient,
            bedrooms: $bedrooms,
            bathrooms: $bathrooms,
            hasParking: $hasParking,
            parkingLot: $parkingLot,
            notes: $notes,
        );

        $result = $useCase->execute($dto, $changedByUserId);
        $resource = new PropertyResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function show(string $id, GetPropertyUseCase $useCase, ListPropertiesRequest $request): JsonResponse
    {
        $result = $useCase->execute($id);
        $resource = new PropertyResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function update(UpdatePropertyRequest $request, string $id, UpdatePropertyUseCase $useCase): JsonResponse
    {
        /** @var string|null $towerId */
        $towerId = $request->validated('tower_id');
        /** @var string|null $propertyTypeId */
        $propertyTypeId = $request->validated('property_type_id');
        /** @var int|null $floor */
        $floor = $request->validated('floor');
        /** @var string|null $unitNumber */
        $unitNumber = $request->validated('unit_number');
        /** @var string|null $areaM2 */
        $areaM2 = $request->validated('area_m2');
        /** @var string|null $coefficient */
        $coefficient = $request->validated('coefficient');
        /** @var int|null $bedrooms */
        $bedrooms = $request->validated('bedrooms');
        /** @var int|null $bathrooms */
        $bathrooms = $request->validated('bathrooms');
        /** @var bool|null $hasParking */
        $hasParking = $request->validated('has_parking');
        /** @var string|null $parkingLot */
        $parkingLot = $request->validated('parking_lot');
        /** @var string|null $notes */
        $notes = $request->validated('notes');

        $dto = new UpdatePropertyRequestDto(
            towerId: $towerId === null ? null : Uuid::fromString($towerId),
            propertyTypeId: $propertyTypeId === null ? null : Uuid::fromString($propertyTypeId),
            floor: $floor,
            unitNumber: $unitNumber,
            areaM2: $areaM2,
            coefficient: $coefficient,
            bedrooms: $bedrooms,
            bathrooms: $bathrooms,
            hasParking: $hasParking,
            parkingLot: $parkingLot,
            notes: $notes,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new PropertyResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeletePropertyUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(null, 204);
    }

    public function changeStatus(ChangePropertyStatusRequest $request, string $id, ChangePropertyStatusUseCase $useCase): JsonResponse
    {
        /** @var string $propertyStatusId */
        $propertyStatusId = $request->validated('property_status_id');
        /** @var string $reason */
        $reason = $request->validated('reason');
        /** @var string $changedByUserId */
        $changedByUserId = $request->attributes->get('auth_user_id');

        $dto = new ChangePropertyStatusRequestDto(
            propertyStatusId: Uuid::fromString($propertyStatusId),
            reason: $reason,
        );

        $result = $useCase->execute($id, $dto, $changedByUserId);
        $resource = new PropertyResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function statusLog(ListPropertiesRequest $request, string $id, GetPropertyStatusLogUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute($id, $page, $perPage);
        $collection = new PropertyStatusLogCollection($result);

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
}
