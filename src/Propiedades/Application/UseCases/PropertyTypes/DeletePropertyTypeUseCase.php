<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyTypes;

use Urbania\Propiedades\Domain\Exceptions\PropertyTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeletePropertyTypeUseCase
{
    /** @var list<string> */
    private const SEEDED_CODES = ['apartamento', 'local', 'parqueadero', 'deposito'];

    public function __construct(
        private PropertyTypeRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyTypeNotFoundException;
        }

        if (in_array($entity->code(), self::SEEDED_CODES, true)) {
            throw new PropertyTypeInUseException('Los tipos de propiedad predefinidos no pueden ser desactivados');
        }

        if ($this->repository->hasActiveProperties($uuid)) {
            throw new PropertyTypeInUseException;
        }

        $entity->deactivate();
        $this->repository->save($entity);
    }
}
