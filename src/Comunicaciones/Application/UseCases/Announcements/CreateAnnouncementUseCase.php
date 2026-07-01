<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Announcements;

use Urbania\Comunicaciones\Application\DTOs\AnnouncementDto;
use Urbania\Comunicaciones\Application\DTOs\CreateAnnouncementDto;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Comunicaciones\Domain\Exceptions\ChannelNotConfiguredException;
use Urbania\Comunicaciones\Domain\Exceptions\SegmentNotAvailableException;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Comunicaciones\Infrastructure\Jobs\SendAnnouncementDeliveriesJob;

final readonly class CreateAnnouncementUseCase
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
        private CommunicationChannelRepositoryInterface $channelRepository,
    ) {}

    public function execute(CreateAnnouncementDto $dto): AnnouncementDto
    {
        if ($dto->segmento === Segment::MOROSOS) {
            throw new SegmentNotAvailableException($dto->segmento->value);
        }

        if (in_array($dto->segmento, [Segment::TORRE, Segment::UNIDAD], true) && $dto->targetId === null) {
            throw new \InvalidArgumentException('El target_id es obligatorio para el segmento seleccionado');
        }

        foreach ($dto->canales as $canal) {
            $channel = $this->channelRepository->findByCondominiumAndChannel($dto->condominiumId, $canal);

            if ($channel === null || ! $channel->activo()) {
                throw new ChannelNotConfiguredException($canal->value);
            }
        }

        $estado = $dto->programadoPara !== null
            ? AnnouncementStatus::PROGRAMADO
            : AnnouncementStatus::BORRADOR;

        $entity = AnnouncementEntity::create(
            condominiumId: $dto->condominiumId,
            autorUserId: $dto->autorUserId,
            titulo: $dto->titulo,
            cuerpo: $dto->cuerpo,
            segmento: $dto->segmento,
            targetId: $dto->targetId,
            estado: $estado,
            programadoPara: $dto->programadoPara,
            fijado: $dto->fijado,
            canales: $dto->canales,
        );

        $this->announcementRepository->save($entity);

        SendAnnouncementDeliveriesJob::dispatch($entity->id()->toString());

        return AnnouncementDto::fromEntity($entity);
    }
}
