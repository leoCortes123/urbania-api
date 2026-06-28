<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;
use Urbania\Propiedades\Application\Services\GenerateFullDesignationService;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetPropertyUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private TowerRepositoryInterface $towerRepository,
        private PropertyTypeRepositoryInterface $propertyTypeRepository,
        private PropertyStatusRepositoryInterface $propertyStatusRepository,
        private PropertyStatusLogRepositoryInterface $statusLogRepository,
        private PropertyDocumentRepositoryInterface $documentRepository,
        private GenerateFullDesignationService $designationService,
    ) {}

    public function execute(string $id): PropertyResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->propertyRepository->findById($uuid);

        if ($entity === null) {
            throw new PropertyNotFoundException;
        }

        $tower = $this->towerRepository->findById($entity->towerId());
        $type = $this->propertyTypeRepository->findById($entity->propertyTypeId());
        $status = $this->propertyStatusRepository->findById($entity->propertyStatusId());

        $statusHistory = $this->statusLogRepository->findByPropertyId($uuid, 1, 10);
        $documentsCount = $this->documentRepository->countByPropertyId($uuid);

        return new PropertyResponseDto(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            towerId: $entity->towerId()->toString(),
            propertyTypeId: $entity->propertyTypeId()->toString(),
            propertyStatusId: $entity->propertyStatusId()->toString(),
            floor: $entity->floor(),
            unitNumber: $entity->unitNumber(),
            areaM2: $entity->areaM2(),
            coefficient: $entity->coefficient(),
            bedrooms: $entity->bedrooms(),
            bathrooms: $entity->bathrooms(),
            hasParking: $entity->hasParking(),
            parkingLot: $entity->parkingLot(),
            notes: $entity->notes(),
            tower: $tower === null ? null : [
                'id' => $tower->id()->toString(),
                'name' => $tower->name(),
                'code' => $tower->code(),
            ],
            type: $type === null ? null : [
                'id' => $type->id()->toString(),
                'code' => $type->code(),
                'name' => $type->name(),
            ],
            status: $status === null ? null : [
                'id' => $status->id()->toString(),
                'code' => $status->code(),
                'name' => $status->name(),
            ],
            fullDesignation: $this->designationService->execute($tower?->code(), $entity->unitNumber()),
            residentsCount: 0,
            documentsCount: $documentsCount,
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
