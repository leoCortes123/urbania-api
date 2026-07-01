<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Comunicaciones\Application\DTOs\UpdateChannelDto;
use Urbania\Comunicaciones\Application\UseCases\Channels\ListChannelsUseCase;
use Urbania\Comunicaciones\Application\UseCases\Channels\UpdateChannelUseCase;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\UpdateChannelRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\ChannelResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class ChannelController extends Controller
{
    public function index(Request $request, ListChannelsUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');

        $channels = $useCase->execute(Uuid::fromString($condominiumIdRaw));

        return response()->json([
            'data' => array_map(
                fn ($dto) => (new ChannelResource($dto))->resolve($request),
                $channels,
            ),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function update(UpdateChannelRequest $request, UpdateChannelUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        /** @var string $canal */
        $canal = $validated['canal'];
        /** @var string|null $provider */
        $provider = $validated['provider'] ?? null;
        /** @var bool $activo */
        $activo = $validated['activo'];

        $config = null;
        if (isset($validated['config']) && is_array($validated['config'])) {
            /** @var array<string, mixed> $config */
            $config = $validated['config'];
        }

        $dto = new UpdateChannelDto(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            canal: DeliveryChannel::fromString($canal),
            provider: $provider,
            config: $config,
            activo: $activo,
        );

        $result = $useCase->execute($dto);
        $resource = new ChannelResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }
}
