<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Channels;

use Urbania\Comunicaciones\Application\DTOs\ChannelDto;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListChannelsUseCase
{
    public function __construct(
        private CommunicationChannelRepositoryInterface $channelRepository,
    ) {}

    /**
     * @return array<ChannelDto>
     */
    public function execute(Uuid $condominiumId): array
    {
        $channels = $this->channelRepository->findByCondominiumId($condominiumId);

        return array_map(
            fn ($entity) => ChannelDto::fromEntity($entity),
            $channels,
        );
    }
}
