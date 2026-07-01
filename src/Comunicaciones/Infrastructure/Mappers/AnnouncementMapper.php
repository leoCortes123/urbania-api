<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Mappers;

use App\Models\Announcement;
use Carbon\Carbon;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class AnnouncementMapper
{
    public function toDomain(Announcement $model): AnnouncementEntity
    {
        /** @var array<string> $canalesRaw */
        $canalesRaw = $model->canales ?? [];
        $canales = array_map(
            fn (string $c) => DeliveryChannel::fromString($c),
            $canalesRaw,
        );

        return AnnouncementEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            autorUserId: Uuid::fromString($model->autor_user_id),
            titulo: $model->titulo,
            cuerpo: $model->cuerpo,
            segmento: Segment::fromString($model->segmento),
            targetId: $model->target_id !== null ? Uuid::fromString($model->target_id) : null,
            estado: AnnouncementStatus::fromString($model->estado),
            programadoPara: $model->programado_para?->toDateTimeImmutable(),
            fijado: $model->fijado,
            canales: $canales,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at?->toDateTimeImmutable(),
        );
    }

    private function toDateTimeImmutable(?Carbon $carbon): \DateTimeImmutable
    {
        if ($carbon === null) {
            return new \DateTimeImmutable;
        }

        return $carbon->toDateTimeImmutable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(AnnouncementEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'autor_user_id' => $entity->autorUserId()->toString(),
            'titulo' => $entity->titulo(),
            'cuerpo' => $entity->cuerpo(),
            'segmento' => $entity->segmento()->value,
            'target_id' => $entity->targetId()?->toString(),
            'estado' => $entity->estado()->value,
            'programado_para' => $entity->programadoPara()?->format('Y-m-d H:i:s'),
            'fijado' => $entity->fijado(),
            'canales' => array_map(fn (DeliveryChannel $c) => $c->value, $entity->canales()),
        ];
    }
}
