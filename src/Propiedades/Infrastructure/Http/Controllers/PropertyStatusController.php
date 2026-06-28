<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\CreatePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyStatusRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\CreatePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\DeletePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\ListPropertyStatusesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\UpdatePropertyStatusUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\CreatePropertyStatusRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListPropertyStatusesRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdatePropertyStatusRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyStatusCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyStatusResource;

final class PropertyStatusController extends Controller
{
    public function index(ListPropertyStatusesRequest $request, ListPropertyStatusesUseCase $useCase): JsonResponse
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

        $collection = new PropertyStatusCollection($result);

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

    public function store(CreatePropertyStatusRequest $request, CreatePropertyStatusUseCase $useCase): JsonResponse
    {
        /** @var string $code */
        $code = $request->validated('code');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var bool|null $allowsResidents */
        $allowsResidents = $request->validated('allows_residents');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new CreatePropertyStatusRequestDto(
            code: $code,
            name: $name,
            description: $description,
            allowsResidents: $allowsResidents ?? true,
            sortOrder: $sortOrder ?? 0,
        );

        $result = $useCase->execute($dto);
        $resource = new PropertyStatusResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function update(UpdatePropertyStatusRequest $request, string $id, UpdatePropertyStatusUseCase $useCase): JsonResponse
    {
        /** @var string|null $code */
        $code = $request->validated('code');
        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $description */
        $description = $request->validated('description');
        /** @var bool|null $allowsResidents */
        $allowsResidents = $request->validated('allows_residents');
        /** @var int|null $sortOrder */
        $sortOrder = $request->validated('sort_order');

        $dto = new UpdatePropertyStatusRequestDto(
            code: $code,
            name: $name,
            description: $description,
            allowsResidents: $allowsResidents,
            sortOrder: $sortOrder,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new PropertyStatusResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeletePropertyStatusUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(null, 204);
    }
}
