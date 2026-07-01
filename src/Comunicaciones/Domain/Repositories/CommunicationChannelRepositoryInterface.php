<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Repositories;

use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface CommunicationChannelRepositoryInterface
{
    public function findById(Uuid $id): ?CommunicationChannelEntity;

    public function findByCondominiumAndChannel(Uuid $condominiumId, DeliveryChannel $canal): ?CommunicationChannelEntity;

    /** @return array<CommunicationChannelEntity> */
    public function findByCondominiumId(Uuid $condominiumId): array;

    public function save(CommunicationChannelEntity $entity): void;
}
