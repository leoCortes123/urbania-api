<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\ChangePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;
use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\SameStatusException;
use Urbania\Propiedades\Domain\Exceptions\StatusHasActiveResidentsException;
use Urbania\Propiedades\Domain\Exceptions\StatusReasonRequiredException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ChangePropertyStatusUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private PropertyStatusRepositoryInterface $propertyStatusRepository,
        private PropertyStatusLogRepositoryInterface $statusLogRepository,
    ) {}

    public function execute(string $id, ChangePropertyStatusRequestDto $request, string $changedByUserId): PropertyResponseDto
    {
        if (trim($request->reason) === '') {
            throw new StatusReasonRequiredException;
        }

        $uuid = Uuid::fromString($id);
        $entity = $this->propertyRepository->findById($uuid);

        if ($entity === null) {
            throw new PropertyNotFoundException;
        }

        $newStatus = $this->propertyStatusRepository->findById($request->propertyStatusId);
        if ($newStatus === null || ! $newStatus->isActive()) {
            throw new PropertyStatusNotFoundException;
        }

        if ($entity->propertyStatusId()->toString() === $request->propertyStatusId->toString()) {
            throw new SameStatusException;
        }

        if (! $newStatus->allowsResidents() && $this->propertyRepository->hasActiveResidents($uuid)) {
            throw new StatusHasActiveResidentsException;
        }

        $previousStatusId = $entity->propertyStatusId();
        $entity->changeStatus($request->propertyStatusId);
        $this->propertyRepository->save($entity);

        $logEntry = PropertyStatusLogEntry::create(
            propertyId: $uuid,
            fromStatusId: $previousStatusId,
            toStatusId: $request->propertyStatusId,
            changedByUserId: Uuid::fromString($changedByUserId),
            reason: $request->reason,
        );
        $this->statusLogRepository->save($logEntry);

        return PropertyResponseDto::fromEntity($entity);
    }
}
