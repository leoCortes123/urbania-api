<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Catalogs;

use Directorio\Application\UseCases\Catalogs\ListOccupantTypesUseCase;
use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\ValueObjects\OccupantTypeCode;
use Mockery;
use Ramsey\Uuid\Uuid;

function occupantTypeEntity(array $overrides = []): OccupantType
{
    return new OccupantType(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        code: $overrides['code'] ?? new OccupantTypeCode('propietario'),
        name: $overrides['name'] ?? 'Propietario',
        sortOrder: $overrides['sortOrder'] ?? 1,
        isActive: $overrides['isActive'] ?? true,
    );
}

beforeEach(function (): void {
    $this->occupantTypeRepository = Mockery::mock(OccupantTypeRepository::class);
    $this->useCase = new ListOccupantTypesUseCase($this->occupantTypeRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns a list of occupant types', function (): void {
    $types = [
        occupantTypeEntity(['code' => new OccupantTypeCode('propietario'), 'name' => 'Propietario']),
        occupantTypeEntity(['code' => new OccupantTypeCode('arrendatario'), 'name' => 'Arrendatario']),
    ];

    $this->occupantTypeRepository->shouldReceive('findAll')
        ->once()
        ->andReturn($types);

    $result = $this->useCase->execute();

    expect($result)->toBe($types)
        ->and($result)->toHaveCount(2);
});
