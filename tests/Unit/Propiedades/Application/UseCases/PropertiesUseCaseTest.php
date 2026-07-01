<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\ChangePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\CreatePropertyRequestDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyRequestDto;
use Urbania\Propiedades\Application\Services\GenerateFullDesignationService;
use Urbania\Propiedades\Application\UseCases\Properties\ChangePropertyStatusUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\CreatePropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\DeletePropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\GetPropertyStatusLogUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\GetPropertyUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\ListPropertiesUseCase;
use Urbania\Propiedades\Application\UseCases\Properties\UpdatePropertyUseCase;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Propiedades\Domain\Entities\PropertyTypeEntity;
use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDuplicateUnitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyHasDependenciesException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\SameStatusException;
use Urbania\Propiedades\Domain\Exceptions\StatusHasActiveResidentsException;
use Urbania\Propiedades\Domain\Exceptions\StatusReasonRequiredException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyEntityForProperties(array $overrides = []): PropertyEntity
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

function createTowerEntityForProperties(array $overrides = []): TowerEntity
{
    return TowerEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        name: $overrides['name'] ?? 'Torre A',
        code: $overrides['code'] ?? 'TA',
        floorCount: $overrides['floorCount'] ?? 10,
        hasElevator: $overrides['hasElevator'] ?? false,
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

function createPropertyTypeEntityForProperties(array $overrides = []): PropertyTypeEntity
{
    return PropertyTypeEntity::create(
        code: $overrides['code'] ?? 'apto',
        name: $overrides['name'] ?? 'Apartamento',
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

function createPropertyStatusEntityForProperties(array $overrides = []): PropertyStatusEntity
{
    return PropertyStatusEntity::create(
        code: $overrides['code'] ?? 'vacia',
        name: $overrides['name'] ?? 'Vacía',
        allowsResidents: $overrides['allowsResidents'] ?? true,
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

beforeEach(function (): void {
    $this->propertyRepository = Mockery::mock(PropertyRepositoryInterface::class);
    $this->towerRepository = Mockery::mock(TowerRepositoryInterface::class);
    $this->propertyTypeRepository = Mockery::mock(PropertyTypeRepositoryInterface::class);
    $this->propertyStatusRepository = Mockery::mock(PropertyStatusRepositoryInterface::class);
    $this->statusLogRepository = Mockery::mock(PropertyStatusLogRepositoryInterface::class);
    $this->documentRepository = Mockery::mock(PropertyDocumentRepositoryInterface::class);
    $this->designationService = new GenerateFullDesignationService;
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreatePropertyUseCase', function (): void {
    it('creates a property when all validations pass', function (): void {
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $status = createPropertyStatusEntityForProperties();
        $changedByUserId = Uuid::v7()->toString();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: $status->id(),
            floor: 5,
            unitNumber: '501',
            areaM2: '80.00',
            coefficient: '0.800000',
            bedrooms: 2,
            bathrooms: 2,
            hasParking: true,
            parkingLot: 'P-501',
            notes: 'Nota',
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with($tower->id())
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with($type->id())
            ->andReturn($type);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with($status->id())
            ->andReturn($status);

        $this->propertyRepository->shouldReceive('existsByUnitNumber')
            ->once()
            ->with($tower->id(), 5, '501')
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyEntity::class));

        $this->statusLogRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyStatusLogEntry::class));

        $result = $useCase->execute($request, $changedByUserId);

        expect($result->towerId)->toBe($tower->id()->toString())
            ->and($result->propertyTypeId)->toBe($type->id()->toString())
            ->and($result->propertyStatusId)->toBe($status->id()->toString())
            ->and($result->floor)->toBe(5)
            ->and($result->unitNumber)->toBe('501')
            ->and($result->areaM2)->toBe('80.00')
            ->and($result->coefficient)->toBe('0.800000')
            ->and($result->bedrooms)->toBe(2)
            ->and($result->bathrooms)->toBe(2)
            ->and($result->hasParking)->toBeTrue()
            ->and($result->parkingLot)->toBe('P-501')
            ->and($result->notes)->toBe('Nota');
    });

    it('uses default status when propertyStatusId is null', function (): void {
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $defaultStatus = createPropertyStatusEntityForProperties(['code' => 'vacia']);
        $changedByUserId = Uuid::v7()->toString();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: null,
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyStatusRepository->shouldReceive('findByCode')
            ->once()
            ->with('vacia')
            ->andReturn($defaultStatus);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with($defaultStatus->id())
            ->andReturn($defaultStatus);

        $this->propertyRepository->shouldReceive('existsByUnitNumber')
            ->once()
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('save')
            ->once();

        $this->statusLogRepository->shouldReceive('save')
            ->once();

        $result = $useCase->execute($request, $changedByUserId);

        expect($result->propertyStatusId)->toBe($defaultStatus->id()->toString());
    });

    it('throws TowerNotFoundException when tower does not exist', function (): void {
        $towerId = Uuid::v7();
        $typeId = Uuid::v7();
        $statusId = Uuid::v7();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $towerId,
            propertyTypeId: $typeId,
            propertyStatusId: $statusId,
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(TowerNotFoundException::class);

    it('throws FloorExceedsTowerLimitException when floor is greater than tower floor count', function (): void {
        $tower = createTowerEntityForProperties(['floorCount' => 5]);
        $type = createPropertyTypeEntityForProperties();
        $status = createPropertyStatusEntityForProperties();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: $status->id(),
            floor: 6,
            unitNumber: '601',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(FloorExceedsTowerLimitException::class);

    it('throws PropertyTypeNotFoundException when type does not exist', function (): void {
        $tower = createTowerEntityForProperties();
        $typeId = Uuid::v7();
        $statusId = Uuid::v7();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $typeId,
            propertyStatusId: $statusId,
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(PropertyTypeNotFoundException::class);

    it('throws PropertyTypeNotFoundException when type is inactive', function (): void {
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $type->deactivate();
        $statusId = Uuid::v7();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: $statusId,
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(PropertyTypeNotFoundException::class);

    it('throws PropertyStatusNotFoundException when default status code is not found', function (): void {
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: null,
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyStatusRepository->shouldReceive('findByCode')
            ->once()
            ->with('vacia')
            ->andReturn(null);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(PropertyStatusNotFoundException::class);

    it('throws PropertyDuplicateUnitException when unit already exists', function (): void {
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $status = createPropertyStatusEntityForProperties();
        $useCase = new CreatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new CreatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            propertyStatusId: $status->id(),
            floor: 1,
            unitNumber: '101',
            areaM2: '50.00',
            coefficient: '0.500000',
            bedrooms: null,
            bathrooms: null,
            hasParking: false,
            parkingLot: null,
            notes: null,
        );

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $this->propertyRepository->shouldReceive('existsByUnitNumber')
            ->once()
            ->andReturn(true);

        $useCase->execute($request, Uuid::v7()->toString());
    })->throws(PropertyDuplicateUnitException::class);
});

describe('ListPropertiesUseCase', function (): void {
    it('returns a paginated list of properties', function (): void {
        $property = createPropertyEntityForProperties();
        $useCase = new ListPropertiesUseCase($this->propertyRepository);

        $this->propertyRepository->shouldReceive('findAll')
            ->once()
            ->with([], 1, 20)
            ->andReturn([
                'items' => [$property],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute();

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->id)->toBe($property->id()->toString());
    });
});

describe('GetPropertyUseCase', function (): void {
    it('returns a property with related data', function (): void {
        $property = createPropertyEntityForProperties();
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $status = createPropertyStatusEntityForProperties();
        $useCase = new GetPropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
            $this->documentRepository,
            $this->designationService,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($type);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($status);

        $this->statusLogRepository->shouldReceive('findByPropertyId')
            ->once()
            ->andReturn([
                'items' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => 10,
                'lastPage' => 1,
            ]);

        $this->documentRepository->shouldReceive('countByPropertyId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(2);

        $result = $useCase->execute($property->id()->toString());

        expect($result->id)->toBe($property->id()->toString())
            ->and($result->tower)->toBeArray()
            ->and($result->type)->toBeArray()
            ->and($result->status)->toBeArray()
            ->and($result->documentsCount)->toBe(2)
            ->and($result->fullDesignation)->toBe("{$tower->code()} - {$property->unitNumber()}");
    });

    it('throws PropertyNotFoundException when it does not exist', function (): void {
        $useCase = new GetPropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
            $this->documentRepository,
            $this->designationService,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);
});

describe('UpdatePropertyUseCase', function (): void {
    it('updates a property when all validations pass', function (): void {
        $property = createPropertyEntityForProperties();
        $tower = createTowerEntityForProperties();
        $type = createPropertyTypeEntityForProperties();
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: $type->id(),
            floor: 3,
            unitNumber: '301',
            areaM2: '90.00',
            coefficient: '0.900000',
            bedrooms: 3,
            bathrooms: 2,
            hasParking: true,
            parkingLot: 'P-301',
            notes: 'Updated',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with($tower->id())
            ->andReturn($tower);

        $this->propertyRepository->shouldReceive('existsByUnitNumber')
            ->once()
            ->with($tower->id(), 3, '301', Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->with($type->id())
            ->andReturn($type);

        $this->propertyRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyEntity::class));

        $result = $useCase->execute($property->id()->toString(), $request);

        expect($result->towerId)->toBe($tower->id()->toString())
            ->and($result->propertyTypeId)->toBe($type->id()->toString())
            ->and($result->floor)->toBe(3)
            ->and($result->unitNumber)->toBe('301')
            ->and($result->areaM2)->toBe('90.00')
            ->and($result->coefficient)->toBe('0.900000')
            ->and($result->bedrooms)->toBe(3)
            ->and($result->bathrooms)->toBe(2)
            ->and($result->hasParking)->toBeTrue()
            ->and($result->parkingLot)->toBe('P-301')
            ->and($result->notes)->toBe('Updated');
    });

    it('throws PropertyNotFoundException when it does not exist', function (): void {
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: null,
            propertyTypeId: null,
            floor: null,
            unitNumber: null,
            areaM2: null,
            coefficient: null,
            bedrooms: null,
            bathrooms: null,
            hasParking: null,
            parkingLot: null,
            notes: null,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(PropertyNotFoundException::class);

    it('throws TowerNotFoundException when new tower does not exist', function (): void {
        $property = createPropertyEntityForProperties();
        $newTowerId = Uuid::v7();
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: $newTowerId,
            propertyTypeId: null,
            floor: null,
            unitNumber: null,
            areaM2: null,
            coefficient: null,
            bedrooms: null,
            bathrooms: null,
            hasParking: null,
            parkingLot: null,
            notes: null,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->with($newTowerId)
            ->andReturn(null);

        $useCase->execute($property->id()->toString(), $request);
    })->throws(TowerNotFoundException::class);

    it('throws FloorExceedsTowerLimitException when new floor exceeds tower limit', function (): void {
        $property = createPropertyEntityForProperties();
        $tower = createTowerEntityForProperties(['floorCount' => 5]);
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: null,
            floor: 6,
            unitNumber: null,
            areaM2: null,
            coefficient: null,
            bedrooms: null,
            bathrooms: null,
            hasParking: null,
            parkingLot: null,
            notes: null,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $useCase->execute($property->id()->toString(), $request);
    })->throws(FloorExceedsTowerLimitException::class);

    it('throws PropertyDuplicateUnitException when unit already exists', function (): void {
        $property = createPropertyEntityForProperties();
        $tower = createTowerEntityForProperties();
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: $tower->id(),
            propertyTypeId: null,
            floor: 3,
            unitNumber: '301',
            areaM2: null,
            coefficient: null,
            bedrooms: null,
            bathrooms: null,
            hasParking: null,
            parkingLot: null,
            notes: null,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyRepository->shouldReceive('existsByUnitNumber')
            ->once()
            ->andReturn(true);

        $useCase->execute($property->id()->toString(), $request);
    })->throws(PropertyDuplicateUnitException::class);

    it('throws PropertyTypeNotFoundException when new type is inactive', function (): void {
        $tower = createTowerEntityForProperties();
        $property = createPropertyEntityForProperties(['towerId' => $tower->id()]);
        $type = createPropertyTypeEntityForProperties();
        $type->deactivate();
        $useCase = new UpdatePropertyUseCase(
            $this->propertyRepository,
            $this->towerRepository,
            $this->propertyTypeRepository,
        );
        $request = new UpdatePropertyRequestDto(
            towerId: null,
            propertyTypeId: $type->id(),
            floor: null,
            unitNumber: null,
            areaM2: null,
            coefficient: null,
            bedrooms: null,
            bathrooms: null,
            hasParking: null,
            parkingLot: null,
            notes: null,
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->towerRepository->shouldReceive('findById')
            ->once()
            ->andReturn($tower);

        $this->propertyTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($type);

        $useCase->execute($property->id()->toString(), $request);
    })->throws(PropertyTypeNotFoundException::class);
});

describe('DeletePropertyUseCase', function (): void {
    it('deletes a property when it has no dependencies', function (): void {
        $property = createPropertyEntityForProperties();
        $useCase = new DeletePropertyUseCase($this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->propertyRepository->shouldReceive('hasActiveResidents')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('hasPendingFees')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Uuid::class));

        $useCase->execute($property->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws PropertyNotFoundException when it does not exist', function (): void {
        $useCase = new DeletePropertyUseCase($this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);

    it('throws PropertyHasDependenciesException when property has active residents', function (): void {
        $property = createPropertyEntityForProperties();
        $useCase = new DeletePropertyUseCase($this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyRepository->shouldReceive('hasActiveResidents')
            ->once()
            ->andReturn(true);

        $this->propertyRepository->shouldReceive('hasPendingFees')
            ->once()
            ->andReturn(false);

        $useCase->execute($property->id()->toString());
    })->throws(PropertyHasDependenciesException::class);

    it('throws PropertyHasDependenciesException when property has pending fees', function (): void {
        $property = createPropertyEntityForProperties();
        $useCase = new DeletePropertyUseCase($this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyRepository->shouldReceive('hasActiveResidents')
            ->once()
            ->andReturn(false);

        $this->propertyRepository->shouldReceive('hasPendingFees')
            ->once()
            ->andReturn(true);

        $useCase->execute($property->id()->toString());
    })->throws(PropertyHasDependenciesException::class);
});

describe('ChangePropertyStatusUseCase', function (): void {
    it('changes property status and creates log entry', function (): void {
        $property = createPropertyEntityForProperties();
        $newStatus = createPropertyStatusEntityForProperties(['code' => 'ocupada']);
        $changedByUserId = Uuid::v7()->toString();
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatus->id(),
            reason: 'Cambio de estado',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with($newStatus->id())
            ->andReturn($newStatus);

        $this->propertyRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyEntity::class));

        $this->statusLogRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyStatusLogEntry::class));

        $result = $useCase->execute($property->id()->toString(), $request, $changedByUserId);

        expect($result->propertyStatusId)->toBe($newStatus->id()->toString());
    });

    it('throws StatusReasonRequiredException when reason is empty', function (): void {
        $property = createPropertyEntityForProperties();
        $newStatus = createPropertyStatusEntityForProperties();
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatus->id(),
            reason: '   ',
        );

        $useCase->execute($property->id()->toString(), $request, Uuid::v7()->toString());
    })->throws(StatusReasonRequiredException::class);

    it('throws PropertyNotFoundException when property does not exist', function (): void {
        $newStatus = createPropertyStatusEntityForProperties();
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatus->id(),
            reason: 'Cambio',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request, Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);

    it('throws PropertyStatusNotFoundException when new status does not exist', function (): void {
        $property = createPropertyEntityForProperties();
        $newStatusId = Uuid::v7();
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatusId,
            reason: 'Cambio',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->with($newStatusId)
            ->andReturn(null);

        $useCase->execute($property->id()->toString(), $request, Uuid::v7()->toString());
    })->throws(PropertyStatusNotFoundException::class);

    it('throws PropertyStatusNotFoundException when new status is inactive', function (): void {
        $property = createPropertyEntityForProperties();
        $newStatus = createPropertyStatusEntityForProperties();
        $newStatus->deactivate();
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatus->id(),
            reason: 'Cambio',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($newStatus);

        $useCase->execute($property->id()->toString(), $request, Uuid::v7()->toString());
    })->throws(PropertyStatusNotFoundException::class);

    it('throws SameStatusException when new status equals current status', function (): void {
        $status = createPropertyStatusEntityForProperties();
        $property = createPropertyEntityForProperties(['propertyStatusId' => $status->id()]);
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $status->id(),
            reason: 'Cambio',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($status);

        $useCase->execute($property->id()->toString(), $request, Uuid::v7()->toString());
    })->throws(SameStatusException::class);

    it('throws StatusHasActiveResidentsException when new status does not allow residents', function (): void {
        $property = createPropertyEntityForProperties();
        $newStatus = createPropertyStatusEntityForProperties(['code' => 'vacia', 'allowsResidents' => false]);
        $useCase = new ChangePropertyStatusUseCase(
            $this->propertyRepository,
            $this->propertyStatusRepository,
            $this->statusLogRepository,
        );
        $request = new ChangePropertyStatusRequestDto(
            propertyStatusId: $newStatus->id(),
            reason: 'Cambio',
        );

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->propertyStatusRepository->shouldReceive('findById')
            ->once()
            ->andReturn($newStatus);

        $this->propertyRepository->shouldReceive('hasActiveResidents')
            ->once()
            ->andReturn(true);

        $useCase->execute($property->id()->toString(), $request, Uuid::v7()->toString());
    })->throws(StatusHasActiveResidentsException::class);
});

describe('GetPropertyStatusLogUseCase', function (): void {
    it('returns paginated status log for a property', function (): void {
        $property = createPropertyEntityForProperties();
        $status = createPropertyStatusEntityForProperties();
        $logEntry = PropertyStatusLogEntry::create(
            propertyId: $property->id(),
            fromStatusId: null,
            toStatusId: $status->id(),
            changedByUserId: Uuid::v7(),
            reason: 'Creación',
        );
        $useCase = new GetPropertyStatusLogUseCase($this->propertyRepository, $this->statusLogRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->statusLogRepository->shouldReceive('findByPropertyId')
            ->once()
            ->with(Mockery::type(Uuid::class), 1, 20)
            ->andReturn([
                'items' => [$logEntry],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($property->id()->toString());

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->propertyId)->toBe($property->id()->toString());
    });

    it('throws PropertyNotFoundException when property does not exist', function (): void {
        $useCase = new GetPropertyStatusLogUseCase($this->propertyRepository, $this->statusLogRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);
});
