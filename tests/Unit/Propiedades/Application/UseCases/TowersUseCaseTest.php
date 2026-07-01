<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\CreateTowerRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdateTowerRequestDto;
use Urbania\Propiedades\Application\UseCases\Towers\CreateTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\DeleteTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\GetTowerUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\ListTowersUseCase;
use Urbania\Propiedades\Application\UseCases\Towers\UpdateTowerUseCase;
use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\TowerHasPropertiesException;
use Urbania\Propiedades\Domain\Exceptions\TowerNameAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createTowerEntity(array $overrides = []): TowerEntity
{
    return TowerEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        name: $overrides['name'] ?? 'Torre A',
        code: $overrides['code'] ?? null,
        floorCount: $overrides['floorCount'] ?? 10,
        hasElevator: $overrides['hasElevator'] ?? false,
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

function createCondominiumForTowers(array $overrides = []): CondominiumEntity
{
    return CondominiumEntity::create(
        name: $overrides['name'] ?? 'Condominio Test',
        totalCoefficient: $overrides['totalCoefficient'] ?? '1.000000',
    );
}

function createPropertyEntity(array $overrides = []): PropertyEntity
{
    return PropertyEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        towerId: $overrides['towerId'] ?? Uuid::v7(),
        propertyTypeId: $overrides['propertyTypeId'] ?? Uuid::v7(),
        propertyStatusId: $overrides['propertyStatusId'] ?? Uuid::v7(),
        floor: $overrides['floor'] ?? 1,
        unitNumber: $overrides['unitNumber'] ?? '101',
        areaM2: $overrides['areaM2'] ?? '50.00',
        coefficient: $overrides['coefficient'] ?? '0.500000',
    );
}

beforeEach(function (): void {
    $this->towerRepository = Mockery::mock(TowerRepositoryInterface::class);
    $this->condominiumRepository = Mockery::mock(CondominiumRepositoryInterface::class);
    $this->propertyRepository = Mockery::mock(PropertyRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreateTowerUseCase', function (): void {
    it('creates a tower when condominium exists and name is unique', function (): void {
        $condominium = createCondominiumForTowers();
        $useCase = new CreateTowerUseCase($this->towerRepository, $this->condominiumRepository);
        $request = new CreateTowerRequestDto(
            condominiumId: $condominium->id(),
            name: 'Torre A',
            code: 'TA',
            floorCount: 10,
            hasElevator: true,
            description: 'Torre principal',
            sortOrder: 1,
        );

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with($condominium->id())
            ->andReturn($condominium);

        $this->towerRepository->shouldReceive('existsByNameInCondominium')
            ->once()
            ->with('Torre A', $condominium->id())
            ->andReturn(false);

        $this->towerRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(TowerEntity::class));

        $result = $useCase->execute($request);

        expect($result->name)->toBe('Torre A')
            ->and($result->code)->toBe('TA')
            ->and($result->floorCount)->toBe(10)
            ->and($result->hasElevator)->toBeTrue()
            ->and($result->description)->toBe('Torre principal')
            ->and($result->sortOrder)->toBe(1)
            ->and($result->condominiumId)->toBe($condominium->id()->toString());
    });

    it('throws CondominiumNotFoundException when condominium does not exist', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new CreateTowerUseCase($this->towerRepository, $this->condominiumRepository);
        $request = new CreateTowerRequestDto(
            condominiumId: $condominiumId,
            name: 'Torre A',
            code: null,
            floorCount: 10,
            hasElevator: false,
            description: null,
            sortOrder: 0,
        );

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with($condominiumId)
            ->andReturn(null);

        $useCase->execute($request);
    })->throws(CondominiumNotFoundException::class);

    it('throws TowerNameAlreadyExistsException when name is duplicated', function (): void {
        $condominium = createCondominiumForTowers();
        $useCase = new CreateTowerUseCase($this->towerRepository, $this->condominiumRepository);
        $request = new CreateTowerRequestDto(
            condominiumId: $condominium->id(),
            name: 'Torre A',
            code: null,
            floorCount: 10,
            hasElevator: false,
            description: null,
            sortOrder: 0,
        );

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with($condominium->id())
            ->andReturn($condominium);

        $this->towerRepository->shouldReceive('existsByNameInCondominium')
            ->once()
            ->with('Torre A', $condominium->id())
            ->andReturn(true);

        $useCase->execute($request);
    })->throws(TowerNameAlreadyExistsException::class);
});

describe('ListTowersUseCase', function (): void {
    it('returns a paginated list of towers for a condominium', function (): void {
        $condominium = createCondominiumForTowers();
        $tower = createTowerEntity(['condominiumId' => $condominium->id()]);
        $useCase = new ListTowersUseCase($this->towerRepository, $this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($condominium);

        $this->towerRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), [], 1, 20)
            ->andReturn([
                'items' => [$tower],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominium->id()->toString());

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->id)->toBe($tower->id()->toString());
    });

    it('throws CondominiumNotFoundException when condominium does not exist', function (): void {
        $useCase = new ListTowersUseCase($this->towerRepository, $this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(CondominiumNotFoundException::class);
});

describe('GetTowerUseCase', function (): void {
    it('returns a tower when it exists', function (): void {
        $tower = createTowerEntity();
        $useCase = new GetTowerUseCase($this->towerRepository);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($tower);

        $result = $useCase->execute($tower->id()->toString());

        expect($result->id)->toBe($tower->id()->toString())
            ->and($result->name)->toBe($tower->name());
    });

    it('throws TowerNotFoundException when it does not exist', function (): void {
        $useCase = new GetTowerUseCase($this->towerRepository);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(TowerNotFoundException::class);
});

describe('UpdateTowerUseCase', function (): void {
    it('updates a tower when it exists', function (): void {
        $tower = createTowerEntity();
        $useCase = new UpdateTowerUseCase($this->towerRepository, $this->propertyRepository);
        $request = new UpdateTowerRequestDto(
            name: 'Torre B',
            code: 'TB',
            floorCount: 15,
            hasElevator: true,
            description: 'Updated',
            sortOrder: 2,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($tower);

        $this->towerRepository->shouldReceive('existsByNameInCondominium')
            ->once()
            ->with('Torre B', Mockery::type(Uuid::class), Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('findByCondominiumAndTower')
            ->once()
            ->andReturn([]);

        $this->towerRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(TowerEntity::class));

        $result = $useCase->execute($tower->id()->toString(), $request);

        expect($result->name)->toBe('Torre B')
            ->and($result->code)->toBe('TB')
            ->and($result->floorCount)->toBe(15)
            ->and($result->hasElevator)->toBeTrue()
            ->and($result->description)->toBe('Updated')
            ->and($result->sortOrder)->toBe(2);
    });

    it('throws TowerNotFoundException when it does not exist', function (): void {
        $useCase = new UpdateTowerUseCase($this->towerRepository, $this->propertyRepository);
        $request = new UpdateTowerRequestDto(
            name: null,
            code: null,
            floorCount: null,
            hasElevator: null,
            description: null,
            sortOrder: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(TowerNotFoundException::class);

    it('throws TowerNameAlreadyExistsException when name is duplicated', function (): void {
        $tower = createTowerEntity();
        $useCase = new UpdateTowerUseCase($this->towerRepository, $this->propertyRepository);
        $request = new UpdateTowerRequestDto(
            name: 'Torre B',
            code: null,
            floorCount: null,
            hasElevator: null,
            description: null,
            sortOrder: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->towerRepository->shouldReceive('existsByNameInCondominium')
            ->once()
            ->andReturn(true);

        $useCase->execute($tower->id()->toString(), $request);
    })->throws(TowerNameAlreadyExistsException::class);

    it('throws FloorExceedsTowerLimitException when reducing floor count below existing properties', function (): void {
        $tower = createTowerEntity(['floorCount' => 10]);
        $property = createPropertyEntity(['floor' => 8]);
        $useCase = new UpdateTowerUseCase($this->towerRepository, $this->propertyRepository);
        $request = new UpdateTowerRequestDto(
            name: null,
            code: null,
            floorCount: 5,
            hasElevator: null,
            description: null,
            sortOrder: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyRepository->shouldReceive('findByCondominiumAndTower')
            ->once()
            ->andReturn([$property]);

        $useCase->execute($tower->id()->toString(), $request);
    })->throws(FloorExceedsTowerLimitException::class);
});

describe('DeleteTowerUseCase', function (): void {
    it('deletes a tower when it exists and has no properties', function (): void {
        $tower = createTowerEntity();
        $useCase = new DeleteTowerUseCase($this->towerRepository, $this->propertyRepository);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($tower);

        $this->propertyRepository->shouldReceive('countByTower')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(0);

        $this->towerRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Uuid::class));

        $useCase->execute($tower->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws TowerNotFoundException when it does not exist', function (): void {
        $useCase = new DeleteTowerUseCase($this->towerRepository, $this->propertyRepository);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(TowerNotFoundException::class);

    it('throws TowerHasPropertiesException when tower has associated properties', function (): void {
        $tower = createTowerEntity();
        $useCase = new DeleteTowerUseCase($this->towerRepository, $this->propertyRepository);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($tower);

        $this->propertyRepository->shouldReceive('countByTower')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(3);

        $useCase->execute($tower->id()->toString());
    })->throws(TowerHasPropertiesException::class);
});
