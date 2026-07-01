<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\CreatePropertyTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyTypeRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\CreatePropertyTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\DeletePropertyTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\ListPropertyTypesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyTypes\UpdatePropertyTypeUseCase;
use Urbania\Propiedades\Domain\Entities\PropertyTypeEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyTypeEntity(array $overrides = []): PropertyTypeEntity
{
    return PropertyTypeEntity::create(
        code: $overrides['code'] ?? 'apto',
        name: $overrides['name'] ?? 'Apartamento',
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

beforeEach(function (): void {
    $this->propertyTypeRepository = Mockery::mock(PropertyTypeRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreatePropertyTypeUseCase', function (): void {
    it('creates a property type when code is unique', function (): void {
        $useCase = new CreatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new CreatePropertyTypeRequestDto(
            code: 'oficina',
            name: 'Oficina',
            description: 'Descripción',
            sortOrder: 1,
        );

        $this->propertyTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->with('oficina')
            ->andReturn(false);

        $this->propertyTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyTypeEntity::class));

        $result = $useCase->execute($request);

        expect($result->code)->toBe('oficina')
            ->and($result->name)->toBe('Oficina')
            ->and($result->description)->toBe('Descripción')
            ->and($result->sortOrder)->toBe(1)
            ->and($result->isActive)->toBeTrue();
    });

    it('throws PropertyTypeCodeAlreadyExistsException when code is duplicated', function (): void {
        $useCase = new CreatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new CreatePropertyTypeRequestDto(
            code: 'apto',
            name: 'Apartamento',
            description: null,
            sortOrder: 0,
        );

        $this->propertyTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->with('apto')
            ->andReturn(true);

        $useCase->execute($request);
    })->throws(PropertyTypeCodeAlreadyExistsException::class);
});

describe('ListPropertyTypesUseCase', function (): void {
    it('returns a paginated list of property types', function (): void {
        $type = createPropertyTypeEntity();
        $useCase = new ListPropertyTypesUseCase($this->propertyTypeRepository);

        $this->propertyTypeRepository->shouldReceive('findAll')
            ->once()
            ->with([], 1, 20)
            ->andReturn([
                'items' => [$type],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute();

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->id)->toBe($type->id()->toString());
    });
});

describe('UpdatePropertyTypeUseCase', function (): void {
    it('updates a property type when it exists', function (): void {
        $type = createPropertyTypeEntity();
        $useCase = new UpdatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new UpdatePropertyTypeRequestDto(
            code: null,
            name: 'Oficina Actualizada',
            description: 'Nueva descripción',
            sortOrder: 5,
        );

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($type);

        $this->propertyTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyTypeEntity::class));

        $result = $useCase->execute($type->id()->toString(), $request);

        expect($result->name)->toBe('Oficina Actualizada')
            ->and($result->description)->toBe('Nueva descripción')
            ->and($result->sortOrder)->toBe(5);
    });

    it('throws PropertyTypeNotFoundException when it does not exist', function (): void {
        $useCase = new UpdatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new UpdatePropertyTypeRequestDto(
            code: null,
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(PropertyTypeNotFoundException::class);

    it('throws PropertyTypeCodeAlreadyExistsException when changing to an existing code', function (): void {
        $type = createPropertyTypeEntity();
        $useCase = new UpdatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new UpdatePropertyTypeRequestDto(
            code: 'dup',
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyTypeRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(false);

        $this->propertyTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString(), $request);
    })->throws(PropertyTypeCodeAlreadyExistsException::class);

    it('throws PropertyTypeInUseException when changing code while in use', function (): void {
        $type = createPropertyTypeEntity();
        $useCase = new UpdatePropertyTypeUseCase($this->propertyTypeRepository);
        $request = new UpdatePropertyTypeRequestDto(
            code: 'dup',
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyTypeRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString(), $request);
    })->throws(PropertyTypeInUseException::class);
});

describe('DeletePropertyTypeUseCase', function (): void {
    it('deactivates a property type when it exists and is not in use', function (): void {
        $type = createPropertyTypeEntity(['code' => 'oficina']);
        $useCase = new DeletePropertyTypeUseCase($this->propertyTypeRepository);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($type);

        $this->propertyTypeRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyTypeEntity::class));

        $useCase->execute($type->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws PropertyTypeNotFoundException when it does not exist', function (): void {
        $useCase = new DeletePropertyTypeUseCase($this->propertyTypeRepository);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyTypeNotFoundException::class);

    it('throws PropertyTypeInUseException when type is seeded', function (): void {
        $type = createPropertyTypeEntity(['code' => 'apartamento']);
        $useCase = new DeletePropertyTypeUseCase($this->propertyTypeRepository);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $useCase->execute($type->id()->toString());
    })->throws(PropertyTypeInUseException::class);

    it('throws PropertyTypeInUseException when type has active properties', function (): void {
        $type = createPropertyTypeEntity(['code' => 'oficina']);
        $useCase = new DeletePropertyTypeUseCase($this->propertyTypeRepository);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyTypeRepository->shouldReceive('hasActiveProperties')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString());
    })->throws(PropertyTypeInUseException::class);
});
