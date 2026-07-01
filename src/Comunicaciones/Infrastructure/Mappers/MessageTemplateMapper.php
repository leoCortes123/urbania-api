<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Mappers;

use App\Models\MessageTemplate;
use Carbon\Carbon;
use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MessageTemplateMapper
{
    public function toDomain(MessageTemplate $model): MessageTemplateEntity
    {
        return MessageTemplateEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            nombre: $model->nombre,
            tipo: $model->tipo,
            cuerpo: $model->cuerpo,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at?->toDateTimeImmutable(),
        );
    }

    private function toDateTimeImmutable(?Carbon $carbon): \DateTimeImmutable
    {
        return $carbon?->toDateTimeImmutable() ?? new \DateTimeImmutable;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(MessageTemplateEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'nombre' => $entity->nombre(),
            'tipo' => $entity->tipo(),
            'cuerpo' => $entity->cuerpo(),
        ];
    }
}
