<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Occupants;

use Directorio\Application\UseCases\Occupants\UnlinkOccupantUseCase;
use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\MustHaveOwnerException;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Domain\ValueObjects\OccupantTypeCode;
use Mockery;
use Ramsey\Uuid\Uuid;

function unlinkOccupantTypeEntity(array $overrides = []): OccupantType
{
    return new OccupantType(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        code: $overrides['code'] ?? new OccupantTypeCode('propietario'),
        name: $overrides['name'] ?? 'Propietario',
        sortOrder: $overrides['sortOrder'] ?? 1,
        isActive: $overrides['isActive'] ?? true,
    );
}

function unlinkOccupantEntity(array $overrides = []): PropertyOccupant
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
    $this->useCase = new UnlinkOccupantUseCase($this->occupantRepository, $this->occupantTypeRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('unlinks an occupant', function (): void {
    $occupant = unlinkOccupantEntity();

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($occupant->id())
        ->andReturn($occupant);

    $this->occupantTypeRepository->shouldReceive('findByCode')
        ->once()
        ->with('propietario')
        ->andReturn(null);

    $this->occupantRepository->shouldReceive('delete')
        ->once()
        ->with($occupant->id());

    $this->useCase->execute($occupant->id());
});

it('throws OccupantNotFoundException when occupant does not exist', function (): void {
    $id = Uuid::uuid7()->toString();

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn(null);

    $this->useCase->execute($id);
})->throws(OccupantNotFoundException::class);

it('throws MustHaveOwnerException when removing the last active owner', function (): void {
    $ownerTypeId = Uuid::uuid7()->toString();
    $ownerType = unlinkOccupantTypeEntity(['id' => $ownerTypeId, 'code' => new OccupantTypeCode('propietario')]);
    $occupant = unlinkOccupantEntity(['occupantTypeId' => $ownerTypeId]);

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($occupant->id())
        ->andReturn($occupant);

    $this->occupantTypeRepository->shouldReceive('findByCode')
        ->once()
        ->with('propietario')
        ->andReturn($ownerType);

    $this->occupantRepository->shouldReceive('countActiveOwnersByProperty')
        ->once()
        ->with($occupant->propertyId())
        ->andReturn(1);

    $this->occupantRepository->shouldReceive('delete')->never();

    $this->useCase->execute($occupant->id());
})->throws(MustHaveOwnerException::class);

it('allows unlinking when more than one active owner remains', function (): void {
    $ownerTypeId = Uuid::uuid7()->toString();
    $ownerType = unlinkOccupantTypeEntity(['id' => $ownerTypeId, 'code' => new OccupantTypeCode('propietario')]);
    $occupant = unlinkOccupantEntity(['occupantTypeId' => $ownerTypeId]);

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($occupant->id())
        ->andReturn($occupant);

    $this->occupantTypeRepository->shouldReceive('findByCode')
        ->once()
        ->with('propietario')
        ->andReturn($ownerType);

    $this->occupantRepository->shouldReceive('countActiveOwnersByProperty')
        ->once()
        ->with($occupant->propertyId())
        ->andReturn(2);

    $this->occupantRepository->shouldReceive('delete')
        ->once()
        ->with($occupant->id());

    $this->useCase->execute($occupant->id());
});
