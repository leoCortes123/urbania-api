<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\CreatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\CreatePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\DeletePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\ListPropertyDocumentTypesUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes\UpdatePropertyDocumentTypeUseCase;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyDocumentTypeEntity(array $overrides = []): PropertyDocumentTypeEntity
{
    return PropertyDocumentTypeEntity::create(
        code: $overrides['code'] ?? 'acta',
        name: $overrides['name'] ?? 'Acta',
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

beforeEach(function (): void {
    $this->documentTypeRepository = Mockery::mock(PropertyDocumentTypeRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreatePropertyDocumentTypeUseCase', function (): void {
    it('creates a property document type when code is unique', function (): void {
        $useCase = new CreatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new CreatePropertyDocumentTypeRequestDto(
            code: 'contrato',
            name: 'Contrato',
            description: 'Descripción',
            sortOrder: 1,
        );

        $this->documentTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->with('contrato')
            ->andReturn(false);

        $this->documentTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyDocumentTypeEntity::class));

        $result = $useCase->execute($request);

        expect($result->code)->toBe('contrato')
            ->and($result->name)->toBe('Contrato')
            ->and($result->description)->toBe('Descripción')
            ->and($result->sortOrder)->toBe(1)
            ->and($result->isActive)->toBeTrue();
    });

    it('throws PropertyDocumentTypeCodeAlreadyExistsException when code is duplicated', function (): void {
        $useCase = new CreatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new CreatePropertyDocumentTypeRequestDto(
            code: 'acta',
            name: 'Acta',
            description: null,
            sortOrder: 0,
        );

        $this->documentTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->with('acta')
            ->andReturn(true);

        $useCase->execute($request);
    })->throws(PropertyDocumentTypeCodeAlreadyExistsException::class);
});

describe('ListPropertyDocumentTypesUseCase', function (): void {
    it('returns a paginated list of property document types', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new ListPropertyDocumentTypesUseCase($this->documentTypeRepository);

        $this->documentTypeRepository->shouldReceive('findAll')
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

describe('UpdatePropertyDocumentTypeUseCase', function (): void {
    it('updates a property document type when it exists', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new UpdatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new UpdatePropertyDocumentTypeRequestDto(
            code: null,
            name: 'Acta Actualizada',
            description: 'Nueva descripción',
            sortOrder: 5,
        );

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($type);

        $this->documentTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyDocumentTypeEntity::class));

        $result = $useCase->execute($type->id()->toString(), $request);

        expect($result->name)->toBe('Acta Actualizada')
            ->and($result->description)->toBe('Nueva descripción')
            ->and($result->sortOrder)->toBe(5);
    });

    it('throws PropertyDocumentTypeNotFoundException when it does not exist', function (): void {
        $useCase = new UpdatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new UpdatePropertyDocumentTypeRequestDto(
            code: null,
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(PropertyDocumentTypeNotFoundException::class);

    it('throws PropertyDocumentTypeCodeAlreadyExistsException when changing to an existing code', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new UpdatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new UpdatePropertyDocumentTypeRequestDto(
            code: 'dup',
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->documentTypeRepository->shouldReceive('hasActiveDocuments')
            ->once()
            ->andReturn(false);

        $this->documentTypeRepository->shouldReceive('existsByCode')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString(), $request);
    })->throws(PropertyDocumentTypeCodeAlreadyExistsException::class);

    it('throws PropertyDocumentTypeInUseException when changing code while in use', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new UpdatePropertyDocumentTypeUseCase($this->documentTypeRepository);
        $request = new UpdatePropertyDocumentTypeRequestDto(
            code: 'dup',
            name: null,
            description: null,
            sortOrder: null,
        );

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->documentTypeRepository->shouldReceive('hasActiveDocuments')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString(), $request);
    })->throws(PropertyDocumentTypeInUseException::class);
});

describe('DeletePropertyDocumentTypeUseCase', function (): void {
    it('deactivates a property document type when it exists and is not in use', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new DeletePropertyDocumentTypeUseCase($this->documentTypeRepository);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($type);

        $this->documentTypeRepository->shouldReceive('hasActiveDocuments')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->documentTypeRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyDocumentTypeEntity::class));

        $useCase->execute($type->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws PropertyDocumentTypeNotFoundException when it does not exist', function (): void {
        $useCase = new DeletePropertyDocumentTypeUseCase($this->documentTypeRepository);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyDocumentTypeNotFoundException::class);

    it('throws PropertyDocumentTypeInUseException when type has active documents', function (): void {
        $type = createPropertyDocumentTypeEntity();
        $useCase = new DeletePropertyDocumentTypeUseCase($this->documentTypeRepository);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->documentTypeRepository->shouldReceive('hasActiveDocuments')
            ->once()
            ->andReturn(true);

        $useCase->execute($type->id()->toString());
    })->throws(PropertyDocumentTypeInUseException::class);
});
