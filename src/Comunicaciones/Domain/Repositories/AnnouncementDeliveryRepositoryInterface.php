<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Repositories;

use Urbania\Comunicaciones\Domain\Entities\AnnouncementDeliveryEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface AnnouncementDeliveryRepositoryInterface
{
    public function findById(Uuid $id): ?AnnouncementDeliveryEntity;

    /** @return array<AnnouncementDeliveryEntity> */
    public function findByAnnouncementId(Uuid $announcementId): array;

    public function save(AnnouncementDeliveryEntity $entity): void;

    /**
     * @return array{enviados: int, entregados: int, leidos: int}
     */
    public function metricsByAnnouncementId(Uuid $announcementId): array;

    /**
     * @return array{byStatus: array<string, int>, byChannel: array<string, array<string, int>>}
     */
    public function breakdownByAnnouncementId(Uuid $announcementId): array;
}
