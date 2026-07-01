<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Comunicaciones\Application\DTOs\CreateTemplateDto;
use Urbania\Comunicaciones\Application\DTOs\UpdateTemplateDto;
use Urbania\Comunicaciones\Application\UseCases\Templates\CreateTemplateUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\DeleteTemplateUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\ListTemplatesUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\UpdateTemplateUseCase;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\CreateTemplateRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\UpdateTemplateRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\TemplateResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class TemplateController extends Controller
{
    public function index(Request $request, ListTemplatesUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');

        $page = $request->query('page');
        $perPage = $request->query('per_page');
        $page = is_numeric($page) ? (int) $page : 1;
        $perPage = is_numeric($perPage) ? (int) $perPage : 20;

        $result = $useCase->execute(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            filters: [],
            page: $page,
            perPage: $perPage,
        );

        return response()->json([
            'data' => array_map(
                fn ($dto) => (new TemplateResource($dto))->resolve($request),
                $result['items'],
            ),
            'meta' => [
                'total' => $result['total'],
                'current_page' => $result['page'],
                'per_page' => $result['perPage'],
                'last_page' => $result['lastPage'],
                'trace_id' => $request->attributes->get('trace_id'),
            ],
        ]);
    }

    public function store(CreateTemplateRequest $request, CreateTemplateUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        /** @var string $nombre */
        $nombre = $validated['nombre'];
        /** @var string|null $tipo */
        $tipo = $validated['tipo'] ?? null;
        /** @var string $cuerpo */
        $cuerpo = $validated['cuerpo'];

        $dto = new CreateTemplateDto(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            nombre: $nombre,
            tipo: $tipo,
            cuerpo: $cuerpo,
        );

        $result = $useCase->execute($dto);
        $resource = new TemplateResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function update(UpdateTemplateRequest $request, string $id, UpdateTemplateUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        /** @var string|null $nombre */
        $nombre = $validated['nombre'] ?? null;
        /** @var string|null $tipo */
        $tipo = $validated['tipo'] ?? null;
        /** @var string|null $cuerpo */
        $cuerpo = $validated['cuerpo'] ?? null;

        $dto = new UpdateTemplateDto(
            nombre: $nombre,
            tipo: $tipo,
            cuerpo: $cuerpo,
        );

        $result = $useCase->execute(Uuid::fromString($id), $dto);
        $resource = new TemplateResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeleteTemplateUseCase $useCase): JsonResponse
    {
        $useCase->execute(Uuid::fromString($id));

        return response()->json(null, 204);
    }
}
