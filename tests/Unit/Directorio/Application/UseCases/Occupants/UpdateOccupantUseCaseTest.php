<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Occupants;

use Directorio\Application\DTOs\UpdateOccupantDTO;
use Directorio\Application\UseCases\Occupants\UpdateOccupantUseCase;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Mockery;
use Ramsey\Uuid\Uuid;

function updateOccupantEntity(array $overrides = []): PropertyOccupant
{
    return new PropertyOccupant(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        propertyId: $overrides['propertyId'] ?? Uuid::uuid7()->toString(),
        contactId: $overrides['contactId'] ?? Uuid::uuid7()->toString(),
        occupantTypeId: $overrides['occupantTypeId'] ?? Uuid::uuid7()->toString(),
        isPrimary: $overrides['isPrimary'] ?? false,
        moveInDate: $overrides['moveInDate'] ?? null,
        moveOutDate: $overrides['moveOutDate'] ?? null,
        isActive: $overrides['isActive'] ?? true,
    );
}

beforeEach(function (): void {
    $this->occupantRepository = Mockery::mock(PropertyOccupantRepository::class);
    $this->useCase = new UpdateOccupantUseCase($this->occupantRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('updates an occupant', function (): void {
    $occupant = updateOccupantEntity();

    $dto = new UpdateOccupantDTO(
        isPrimary: true,
        moveInDate: '2026-01-01',
        moveOutDate: '2026-12-31',
        isActive: false,
    );

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($occupant->id())
        ->andReturn($occupant);

    $this->occupantRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::type(PropertyOccupant::class))
        ->andReturnUsing(function (PropertyOccupant $updated): PropertyOccupant {
            return $updated;
        });

    $result = $this->useCase->execute($occupant->id(), $dto);

    expect($result->id())->toBe($occupant->id())
        ->and($result->propertyId())->toBe($occupant->propertyId())
        ->and($result->contactId())->toBe($occupant->contactId())
        ->and($result->occupantTypeId())->toBe($occupant->occupantTypeId())
        ->and($result->isPrimary())->toBeTrue()
        ->and($result->moveInDate())->toBe('2026-01-01')
        ->and($result->moveOutDate())->toBe('2026-12-31')
        ->and($result->isActive())->toBeFalse();
});

it('throws OccupantNotFoundException when occupant does not exist', function (): void {
    $id = Uuid::uuid7()->toString();
    $dto = new UpdateOccupantDTO(isPrimary: true);

    $this->occupantRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn(null);

    $this->useCase->execute($id, $dto);
})->throws(OccupantNotFoundException::class);
