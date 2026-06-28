<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\CreatePropertyTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyTypeRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\CreatePropertyTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\DeletePropertyTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\ListPropertyTypesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\UpdatePropertyTypeUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\CreatePropertyTypeRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListPropertyTypesRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdatePropertyTypeRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyTypeCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyTypeResource;

final class PropertyTypeController extends Controller
{
    public function index(ListPropertyTypesRequest $request, ListPropertyTypesUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute(
            filters: [
                'search' => $validated['search'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
                'sort_by' => $validated['sort_by'] ?? null,
                'sort_order' => $validated['sort_order'] ?? null,
            ],
            page: $page,
            perPage: $perPage,
        );

        $collection = new PropertyTypeCollection($result);

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

    public function store(CreatePropertyTypeRequest $request, CreatePropertyTypeUseCase $useCase): JsonResponse
    {
        /** @var string $code */
        $code = $request->validated('code');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new CreatePropertyTypeRequestDto(
            code: $code,
            name: $name,
            description: $description,
            sortOrder: $sortOrder ?? 0,
        );

        $result = $useCase->execute($dto);
        $resource = new PropertyTypeResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function update(UpdatePropertyTypeRequest $request, string $id, UpdatePropertyTypeUseCase $useCase): JsonResponse
    {
        /** @var string|null $code */
        $code = $request->validated('code');
        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new UpdatePropertyTypeRequestDto(
            code: $code,
            name: $name,
            description: $description,
            sortOrder: $sortOrder,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new PropertyTypeResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeletePropertyTypeUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(null, 204);
    }
}
