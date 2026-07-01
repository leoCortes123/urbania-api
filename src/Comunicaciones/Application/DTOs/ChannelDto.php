<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;

final readonly class ChannelDto
{
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $canal,
        public ?string $provider,
        public bool $activo,
        public ?string $configMask,
    ) {}

    public static function fromEntity(CommunicationChannelEntity $entity): self
    {
        $config = $entity->config();

        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            canal: $entity->canal()->value,
            provider: $entity->provider(),
            activo: $entity->activo(),
            configMask: $config !== null ? '***' : null,
        );
    }
}
