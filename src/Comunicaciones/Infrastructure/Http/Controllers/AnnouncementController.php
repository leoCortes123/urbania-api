<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Comunicaciones\Application\DTOs\CreateAnnouncementDto;
use Urbania\Comunicaciones\Application\UseCases\Announcements\CreateAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\DeleteAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\GetAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\ListAnnouncementsUseCase;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\CreateAnnouncementRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\ListAnnouncementsRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\AnnouncementListResource;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\AnnouncementResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class AnnouncementController extends Controller
{
    public function index(ListAnnouncementsRequest $request, ListAnnouncementsUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            filters: [
                'estado' => $validated['estado'] ?? null,
                'segmento' => $validated['segmento'] ?? null,
            ],
            page: $page,
            perPage: $perPage,
        );

        $resource = new AnnouncementListResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function store(CreateAnnouncementRequest $request, CreateAnnouncementUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var string $autorUserIdRaw */
        $autorUserIdRaw = $request->attributes->get('auth_user_id');
        /** @var string $titulo */
        $titulo = $validated['titulo'];
        /** @var string $cuerpo */
        $cuerpo = $validated['cuerpo'];
        /** @var string $segmento */
        $segmento = $validated['segmento'];
        /** @var array<int, string> $canalesRaw */
        $canalesRaw = $validated['canales'];

        $canales = array_map(
            fn (string $c) => DeliveryChannel::fromString($c),
            $canalesRaw,
        );

        $targetId = isset($validated['target_id']) && is_string($validated['target_id'])
            ? Uuid::fromString($validated['target_id'])
            : null;

        $programadoPara = isset($validated['programado_para']) && is_string($validated['programado_para'])
            ? new \DateTimeImmutable($validated['programado_para'])
            : null;

        $dto = new CreateAnnouncementDto(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            autorUserId: Uuid::fromString($autorUserIdRaw),
            titulo: $titulo,
            cuerpo: $cuerpo,
            segmento: Segment::fromString($segmento),
            targetId: $targetId,
            canales: $canales,
            programadoPara: $programadoPara,
            fijado: (bool) ($validated['fijado'] ?? false),
        );

        $result = $useCase->execute($dto);
        $resource = new AnnouncementResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function show(string $id, GetAnnouncementUseCase $useCase, ListAnnouncementsRequest $request): JsonResponse
    {
        $result = $useCase->execute(Uuid::fromString($id));
        $resource = new AnnouncementResource($result->announcement);

        return response()->json([
            'data' => array_merge(
                $resource->resolve($request),
                ['breakdown' => $result->breakdown],
            ),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function destroy(string $id, DeleteAnnouncementUseCase $useCase, ListAnnouncementsRequest $request): JsonResponse
    {
        $useCase->execute(Uuid::fromString($id));

        return response()->json(null, 204);
    }
}
