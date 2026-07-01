<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Channels;

use Urbania\Comunicaciones\Application\DTOs\ChannelDto;
use Urbania\Comunicaciones\Application\DTOs\UpdateChannelDto;
use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;

final readonly class UpdateChannelUseCase
{
    public function __construct(
        private CommunicationChannelRepositoryInterface $channelRepository,
    ) {}

    public function execute(UpdateChannelDto $dto): ChannelDto
    {
        $entity = $this->channelRepository->findByCondominiumAndChannel($dto->condominiumId, $dto->canal);

        if ($entity === null) {
            $entity = CommunicationChannelEntity::create(
                condominiumId: $dto->condominiumId,
                canal: $dto->canal,
                provider: $dto->provider,
                config: $dto->config,
                activo: $dto->activo,
            );
        } else {
            $entity = $entity->update(
                provider: $dto->provider ?? $entity->provider(),
                config: $dto->config ?? $entity->config(),
                activo: $dto->activo,
            );
        }

        $this->channelRepository->save($entity);

        return ChannelDto::fromEntity($entity);
    }
}
