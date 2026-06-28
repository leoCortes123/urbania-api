<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyStatuses;

use Urbania\Propiedades\Domain\Exceptions\PropertyStatusInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeletePropertyStatusUseCase
{
    /** @var list<string> */
    private const SEEDED_CODES = ['ocupada', 'vacia', 'en_venta', 'en_remodelacion'];

    public function __construct(
        private PropertyStatusRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyStatusNotFoundException;
        }

        if (in_array($entity->code(), self::SEEDED_CODES, true)) {
            throw new PropertyStatusInUseException('Los estados de propiedad predefinidos no pueden ser desactivados');
        }

        if ($this->repository->hasActiveProperties($uuid)) {
            throw new PropertyStatusInUseException;
        }

        $entity->deactivate();
        $this->repository->save($entity);
    }
}
