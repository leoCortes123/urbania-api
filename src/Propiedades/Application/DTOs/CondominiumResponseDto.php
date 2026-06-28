<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\CondominiumEntity;

final readonly class CondominiumResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $address,
        public ?string $city,
        public ?string $department,
        public string $country,
        public ?string $nit,
        public ?string $phone,
        public ?string $email,
        public ?string $legalRepresentative,
        public string $totalCoefficient,
        public ?string $logoUrl,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(CondominiumEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            name: $entity->name(),
            address: $entity->address(),
            city: $entity->city(),
            department: $entity->department(),
            country: $entity->country(),
            nit: $entity->nit(),
            phone: $entity->phone(),
            email: $entity->email(),
            legalRepresentative: $entity->legalRepresentative(),
            totalCoefficient: $entity->totalCoefficient(),
            logoUrl: $entity->logoUrl(),
            isActive: $entity->isActive(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
