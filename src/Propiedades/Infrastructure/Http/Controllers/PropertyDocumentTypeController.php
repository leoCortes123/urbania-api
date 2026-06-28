<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\CreatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\CreatePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\DeletePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\ListPropertyDocumentTypesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\UpdatePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\CreatePropertyDocumentTypeRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListPropertyDocumentTypesRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdatePropertyDocumentTypeRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyDocumentTypeCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyDocumentTypeResource;

final class PropertyDocumentTypeController extends Controller
{
    public function index(ListPropertyDocumentTypesRequest $request, ListPropertyDocumentTypesUseCase $useCase): JsonResponse
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

        $collection = new PropertyDocumentTypeCollection($result);

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

    public function store(CreatePropertyDocumentTypeRequest $request, CreatePropertyDocumentTypeUseCase $useCase): JsonResponse
    {
        /** @var string $code */
        $code = $request->validated('code');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new CreatePropertyDocumentTypeRequestDto(
            code: $code,
            name: $name,
            description: $description,
            sortOrder: $sortOrder ?? 0,
        );

        $result = $useCase->execute($dto);
        $resource = new PropertyDocumentTypeResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function update(UpdatePropertyDocumentTypeRequest $request, string $id, UpdatePropertyDocumentTypeUseCase $useCase): JsonResponse
    {
        /** @var string|null $code */
        $code = $request->validated('code');
        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new UpdatePropertyDocumentTypeRequestDto(
            code: $code,
            name: $name,
            description: $description,
            sortOrder: $sortOrder,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new PropertyDocumentTypeResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeletePropertyDocumentTypeUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(null, 204);
    }
}
