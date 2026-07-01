<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Occupants;

use Directorio\Application\DTOs\CreateOccupantDTO;
use Directorio\Application\UseCases\Occupants\LinkContactToUnitUseCase;
use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\DuplicateOccupantException;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Domain\ValueObjects\OccupantTypeCode;
use InvalidArgumentException;
use Mockery;
use Ramsey\Uuid\Uuid;

function linkOccupantTypeEntity(array $overrides = []): OccupantType
{
    return new OccupantType(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        code: $overrides['code'] ?? new OccupantTypeCode('propietario'),
        name: $overrides['name'] ?? 'Propietario',
        sortOrder: $overrides['sortOrder'] ?? 1,
        isActive: $overrides['isActive'] ?? true,
    );
}

function linkOccupantEntity(array $overrides = []): PropertyOccupant
{
    return new PropertyOccupant(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        propertyId: $overrides['propertyId'] ?? Uuid::uuid7()->toString(),
        contactId: $overrides['contactId'] ?? Uuid::uuid7()->toString(),
        occupantTypeId: $overrides['occupantTypeId'] ?? Uuid::uuid7()->toString(),
        isPrimary: $overrides['isPrimary'] ?? false,
        moveInDate: $overrides['moveInDate'] ?? null,
        moveOutDate: $overrides['moveOutDate'] ?? null,
    );
}

beforeEach(function (): void {
    $this->occupantRepository = Mockery::mock(PropertyOccupantRepository::class);
    $this->occupantTypeRepository = Mockery::mock(OccupantTypeRepository::class);
    $this->useCase = new LinkContactToUnitUseCase($this->occupantRepository, $this->occupantTypeRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('links a contact to a unit', function (): void {
    $propertyId = Uuid::uuid7()->toString();
    $contactId = Uuid::uuid7()->toString();
    $occupantTypeId = Uuid::uuid7()->toString();

    $occupantType = linkOccupantTypeEntity(['id' => $occupantTypeId]);

    $dto = new CreateOccupantDTO(
        contactId: $contactId,
        occupantTypeId: $occupantTypeId,
        isPrimary: true,
        moveInDate: '2026-01-01',
    );

    $this->occupantTypeRepository->shouldReceive('findById')
        ->once()
        ->with($occupantTypeId)
        ->andReturn($occupantType);

    $this->occupantRepository->shouldReceive('findActiveByPropertyAndType')
        ->once()
        ->with($propertyId, $occupantTypeId)
        ->andReturn([]);

    $this->occupantRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(PropertyOccupant::class))
        ->andReturnUsing(function (PropertyOccupant $occupant): PropertyOccupant {
            return $occupant;
        });

    $result = $this->useCase->execute($propertyId, $dto);

    expect($result->propertyId())->toBe($propertyId)
        ->and($result->contactId())->toBe($contactId)
        ->and($result->occupantTypeId())->toBe($occupantTypeId)
        ->and($result->isPrimary())->toBeTrue()
        ->and($result->moveInDate())->toBe('2026-01-01');
});

it('throws InvalidArgumentException when occupant type does not exist', function (): void {
    $propertyId = Uuid::uuid7()->toString();
    $occupantTypeId = Uuid::uuid7()->toString();

    $dto = new CreateOccupantDTO(
        contactId: Uuid::uuid7()->toString(),
        occupantTypeId: $occupantTypeId,
    );

    $this->occupantTypeRepository->shouldReceive('findById')
        ->once()
        ->with($occupantTypeId)
        ->andReturn(null);

    $this->useCase->execute($propertyId, $dto);
})->throws(InvalidArgumentException::class, 'Tipo de ocupante no encontrado');

it('throws DuplicateOccupantException when contact already has the same role in the unit', function (): void {
    $propertyId = Uuid::uuid7()->toString();
    $contactId = Uuid::uuid7()->toString();
    $occupantTypeId = Uuid::uuid7()->toString();

    $occupantType = linkOccupantTypeEntity(['id' => $occupantTypeId]);
    $existingOccupant = linkOccupantEntity([
        'propertyId' => $propertyId,
        'contactId' => $contactId,
        'occupantTypeId' => $occupantTypeId,
    ]);

    $dto = new CreateOccupantDTO(
        contactId: $contactId,
        occupantTypeId: $occupantTypeId,
    );

    $this->occupantTypeRepository->shouldReceive('findById')
        ->once()
        ->with($occupantTypeId)
        ->andReturn($occupantType);

    $this->occupantRepository->shouldReceive('findActiveByPropertyAndType')
        ->once()
        ->with($propertyId, $occupantTypeId)
        ->andReturn([$existingOccupant]);

    $this->useCase->execute($propertyId, $dto);
})->throws(DuplicateOccupantException::class);
