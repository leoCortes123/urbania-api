<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;

final readonly class AnnouncementDto
{
    /**
     * @param  array<string>  $canales
     */
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $autorUserId,
        public string $titulo,
        public string $cuerpo,
        public string $segmento,
        public ?string $targetId,
        public string $estado,
        public ?string $programadoPara,
        public bool $fijado,
        public array $canales,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(AnnouncementEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            autorUserId: $entity->autorUserId()->toString(),
            titulo: $entity->titulo(),
            cuerpo: $entity->cuerpo(),
            segmento: $entity->segmento()->value,
            targetId: $entity->targetId()?->toString(),
            estado: $entity->estado()->value,
            programadoPara: $entity->programadoPara()?->format('c'),
            fijado: $entity->fijado(),
            canales: array_map(fn ($c) => $c->value, $entity->canales()),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
