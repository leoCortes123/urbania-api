<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\CreatePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyStatusRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\CreatePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\DeletePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\ListPropertyStatusesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyStatuses\UpdatePropertyStatusUseCase;
use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyStatusEntity(array $overrides = []): PropertyStatusEntity
{
    return PropertyStatusEntity::create(
        code: $overrides['code'] ?? 'disponible',
        name: $overrides['name'] ?? 'Disponible',
        allowsResidents: $overrides['allowsResidents'] ?? true,
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

beforeEach(function (): void {
    $this->propertyStatusRepository = Mockery::mock(PropertyStatusRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreatePropertyStatusUseCase', function (): void {
    it('creates a property status when code is unique', function (): void {
        $useCase = new CreatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new CreatePropertyStatusRequestDto(
            code: 'alquilada',
            name: 'Alquilada',
            description: 'Descripción',
            allowsResidents: true,
            sortOrder: 1,
        );

        $this->propertyStatusRepository->shouldReceive('existsByCode')
            ->once()
            ->with('alquilada')
            ->andReturn(false);

        $this->propertyStatusRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyStatusEntity::class));

        $result = $useCase->execute($request);

        expect($result->code)->toBe('alquilada')
            ->and($result->name)->toBe('Alquilada')
            ->and($result->description)->toBe('Descripción')
            ->and($result->allowsResidents)->toBeTrue()
            ->and($result->sortOrder)->toBe(1)
            ->and($result->isActive)->toBeTrue();
    });

    it('throws PropertyStatusCodeAlreadyExistsException when code is duplicated', function (): void {
        $useCase = new CreatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new CreatePropertyStatusRequestDto(
            code: 'ocupada',
            name: 'Ocupada',
            description: null,
            allowsResidents: true,
            sortOrder: 0,
        );

        $this->propertyStatusRepository->shouldReceive('existsByCode')
            ->once()
            ->with('ocupada')
            ->andReturn(true);

        $useCase->execute($request);
    })->throws(PropertyStatusCodeAlreadyExistsException::class);
});

describe('ListPropertyStatusesUseCase', function (): void {
    it('returns a paginated list of property statuses', function (): void {
        $status = createPropertyStatusEntity();
        $useCase = new ListPropertyStatusesUseCase($this->propertyStatusRepository);

        $this->propertyStatusRepository->shouldReceive('findAll')
            ->once()
            ->with([], 1, 20)
            ->andReturn([
                'items' => [$status],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute();

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->id)->toBe($status->id()->toString());
    });
});

describe('UpdatePropertyStatusUseCase', function (): void {
    it('updates a property status when it exists', function (): void {
        $status = createPropertyStatusEntity();
        $useCase = new UpdatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new UpdatePropertyStatusRequestDto(
            code: null,
            name: 'Estado Actualizado',
            description: 'Nueva descripción',
            allowsResidents: false,
            sortOrder: 5,
        );

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($status);

        $this->propertyStatusRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyStatusEntity::class));

        $result = $useCase->execute($status->id()->toString(), $request);

        expect($result->name)->toBe('Estado Actualizado')
            ->and($result->description)->toBe('Nueva descripción')
            ->and($result->allowsResidents)->toBeFalse()
            ->and($result->sortOrder)->toBe(5);
    });

    it('throws PropertyStatusNotFoundException when it does not exist', function (): void {
        $useCase = new UpdatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new UpdatePropertyStatusRequestDto(
            code: null,
            name: null,
            description: null,
            allowsResidents: null,
            sortOrder: null,
        );

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(PropertyStatusNotFoundException::class);

    it('throws PropertyStatusCodeAlreadyExistsException when changing to an existing code', function (): void {
        $status = createPropertyStatusEntity();
        $useCase = new UpdatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new UpdatePropertyStatusRequestDto(
            code: 'dup',
            name: null,
            description: null,
            allowsResidents: null,
            sortOrder: null,
        );

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $this->propertyStatusRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(false);

        $this->propertyStatusRepository->shouldReceive('existsByCode')
            ->once()
            ->andReturn(true);

        $useCase->execute($status->id()->toString(), $request);
    })->throws(PropertyStatusCodeAlreadyExistsException::class);

    it('throws PropertyStatusInUseException when changing code while in use', function (): void {
        $status = createPropertyStatusEntity();
        $useCase = new UpdatePropertyStatusUseCase($this->propertyStatusRepository);
        $request = new UpdatePropertyStatusRequestDto(
            code: 'dup',
            name: null,
            description: null,
            allowsResidents: null,
            sortOrder: null,
        );

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $this->propertyStatusRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(true);

        $useCase->execute($status->id()->toString(), $request);
    })->throws(PropertyStatusInUseException::class);
});

describe('DeletePropertyStatusUseCase', function (): void {
    it('deactivates a property status when it exists and is not in use', function (): void {
        $status = createPropertyStatusEntity(['code' => 'alquilada']);
        $useCase = new DeletePropertyStatusUseCase($this->propertyStatusRepository);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($status);

        $this->propertyStatusRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyStatusRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyStatusEntity::class));

        $useCase->execute($status->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws PropertyStatusNotFoundException when it does not exist', function (): void {
        $useCase = new DeletePropertyStatusUseCase($this->propertyStatusRepository);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyStatusNotFoundException::class);

    it('throws PropertyStatusInUseException when status is seeded', function (): void {
        $status = createPropertyStatusEntity(['code' => 'ocupada']);
        $useCase = new DeletePropertyStatusUseCase($this->propertyStatusRepository);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $useCase->execute($status->id()->toString());
    })->throws(PropertyStatusInUseException::class);

    it('throws PropertyStatusInUseException when status has active properties', function (): void {
        $status = createPropertyStatusEntity(['code' => 'alquilada']);
        $useCase = new DeletePropertyStatusUseCase($this->propertyStatusRepository);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $this->propertyStatusRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(true);

        $useCase->execute($status->id()->toString());
    })->throws(PropertyStatusInUseException::class);
});
