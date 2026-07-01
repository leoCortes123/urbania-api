<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Announcements;

use Urbania\Comunicaciones\Application\DTOs\AnnouncementListItemDto;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementDeliveryRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListAnnouncementsUseCase
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
        private AnnouncementDeliveryRepositoryInterface $deliveryRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<AnnouncementListItemDto>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function execute(Uuid $condominiumId, array $filters, int $page, int $perPage): array
    {
        $result = $this->announcementRepository->findByCondominiumId($condominiumId, $filters, $page, $perPage);

        $items = [];
        foreach ($result['items'] as $entity) {
            $metrics = $this->deliveryRepository->metricsByAnnouncementId($entity->id());

            $items[] = new AnnouncementListItemDto(
                id: $entity->id()->toString(),
                titulo: $entity->titulo(),
                segmento: $entity->segmento()->value,
                estado: $entity->estado()->value,
                programadoPara: $entity->programadoPara()?->format('c'),
                fijado: $entity->fijado(),
                metrics: $metrics,
            );
        }

        return [
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'lastPage' => $result['lastPage'],
        ];
    }
}
