<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\UpdateCondominiumRequestDto;
use Urbania\Propiedades\Application\UseCases\Condominiums\GetCondominiumUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\ListCondominiumsUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\UpdateCondominiumUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\ValidateCoefficientsUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListCondominiumsRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UpdateCondominiumRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\CondominiumCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\CondominiumResource;

final class CondominiumController extends Controller
{
    public function index(ListCondominiumsRequest $request, ListCondominiumsUseCase $useCase): JsonResponse
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

        $collection = new CondominiumCollection($result);

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

    public function show(string $id, GetCondominiumUseCase $useCase, ListCondominiumsRequest $request): JsonResponse
    {
        $result = $useCase->execute($id);
        $resource = new CondominiumResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function update(UpdateCondominiumRequest $request, string $id, UpdateCondominiumUseCase $useCase): JsonResponse
    {
        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $address */
        $address = $request->validated('address');
        /** @var string|null $city */
        $city = $request->validated('city');
        /** @var string|null $department */
        $department = $request->validated('department');
        /** @var string|null $country */
        $country = $request->validated('country');
        /** @var string|null $nit */
        $nit = $request->validated('nit');
        /** @var string|null $phone */
        $phone = $request->validated('phone');
        /** @var string|null $email */
        $email = $request->validated('email');
        /** @var string|null $legalRepresentative */
        $legalRepresentative = $request->validated('legal_representative');
        /** @var string|null $logoUrl */
        $logoUrl = $request->validated('logo_url');

        $dto = new UpdateCondominiumRequestDto(
            name: $name,
            address: $address,
            city: $city,
            department: $department,
            country: $country,
            nit: $nit,
            phone: $phone,
            email: $email,
            legalRepresentative: $legalRepresentative,
            logoUrl: $logoUrl,
        );

        $result = $useCase->execute($id, $dto);
        $resource = new CondominiumResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function coefficientValidation(string $id, ValidateCoefficientsUseCase $useCase, ListCondominiumsRequest $request): JsonResponse
    {
        $result = $useCase->execute($id);

        return response()->json([
            'data' => $result,
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }
}
