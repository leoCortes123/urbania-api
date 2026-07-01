<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;

final readonly class TemplateDto
{
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $nombre,
        public ?string $tipo,
        public string $cuerpo,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(MessageTemplateEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            nombre: $entity->nombre(),
            tipo: $entity->tipo(),
            cuerpo: $entity->cuerpo(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
