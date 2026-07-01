<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Occupants;

use Directorio\Application\Services\PropertyExistsCheckerInterface;
use Directorio\Application\UseCases\Occupants\ListUnitOccupantsUseCase;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Mockery;
use Ramsey\Uuid\Uuid;

function listUnitOccupantEntity(array $overrides = []): PropertyOccupant
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
    $this->propertyExistsChecker = Mockery::mock(PropertyExistsCheckerInterface::class);
    $this->useCase = new ListUnitOccupantsUseCase($this->occupantRepository, $this->propertyExistsChecker);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns occupants for a unit', function (): void {
    $propertyId = Uuid::uuid7()->toString();
    $occupants = [
        listUnitOccupantEntity(['propertyId' => $propertyId]),
        listUnitOccupantEntity(['propertyId' => $propertyId]),
    ];

    $this->propertyExistsChecker->shouldReceive('exists')
        ->once()
        ->with($propertyId)
        ->andReturn(true);

    $this->occupantRepository->shouldReceive('findByProperty')
        ->once()
        ->with($propertyId)
        ->andReturn($occupants);

    $result = $this->useCase->execute($propertyId);

    expect($result)->toBe($occupants)
        ->and($result)->toHaveCount(2);
});

it('throws OccupantNotFoundException when property does not exist', function (): void {
    $propertyId = Uuid::uuid7()->toString();

    $this->propertyExistsChecker->shouldReceive('exists')
        ->once()
        ->with($propertyId)
        ->andReturn(false);

    $this->occupantRepository->shouldReceive('findByProperty')->never();

    $this->useCase->execute($propertyId);
})->throws(OccupantNotFoundException::class);
