<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Domain\Exceptions\PropertyHasDependenciesException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeletePropertyUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyNotFoundException;
        }

        $details = [];

        if ($this->repository->hasActiveResidents($uuid)) {
            $details['active_residents'] = true;
        }

        if ($this->repository->hasPendingFees($uuid)) {
            $details['pending_fees'] = true;
        }

        if ($details !== []) {
            throw new PropertyHasDependenciesException(
                message: 'La unidad tiene dependencias activas',
                details: $details,
            );
        }

        $this->repository->delete($uuid);
    }
}
