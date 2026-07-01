<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Announcements;

use Urbania\Comunicaciones\Application\DTOs\AnnouncementDetailDto;
use Urbania\Comunicaciones\Application\DTOs\AnnouncementDto;
use Urbania\Comunicaciones\Domain\Exceptions\AnnouncementNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementDeliveryRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetAnnouncementUseCase
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
        private AnnouncementDeliveryRepositoryInterface $deliveryRepository,
    ) {}

    public function execute(Uuid $id): AnnouncementDetailDto
    {
        $entity = $this->announcementRepository->findById($id);

        if ($entity === null) {
            throw new AnnouncementNotFoundException;
        }

        $breakdown = $this->deliveryRepository->breakdownByAnnouncementId($entity->id());

        return new AnnouncementDetailDto(
            announcement: AnnouncementDto::fromEntity($entity),
            breakdown: $breakdown,
        );
    }
}
