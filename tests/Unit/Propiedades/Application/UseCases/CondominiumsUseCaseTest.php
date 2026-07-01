<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Mockery;
use Urbania\Propiedades\Application\DTOs\UpdateCondominiumRequestDto;
use Urbania\Propiedades\Application\UseCases\Condominiums\GetCondominiumUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\ListCondominiumsUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\UpdateCondominiumUseCase;
use Urbania\Propiedades\Application\UseCases\Condominiums\ValidateCoefficientsUseCase;
use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createCondominiumEntity(array $overrides = []): CondominiumEntity
{
    return CondominiumEntity::create(
        name: $overrides['name'] ?? 'Condominio Test',
        address: $overrides['address'] ?? null,
        city: $overrides['city'] ?? null,
        department: $overrides['department'] ?? null,
        country: $overrides['country'] ?? null,
        nit: $overrides['nit'] ?? null,
        phone: $overrides['phone'] ?? null,
        email: $overrides['email'] ?? null,
        legalRepresentative: $overrides['legalRepresentative'] ?? null,
        totalCoefficient: $overrides['totalCoefficient'] ?? null,
        logoUrl: $overrides['logoUrl'] ?? null,
    );
}

beforeEach(function (): void {
    $this->condominiumRepository = Mockery::mock(CondominiumRepositoryInterface::class);
    $this->propertyRepository = Mockery::mock(PropertyRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('ListCondominiumsUseCase', function (): void {
    it('returns a paginated list of condominiums', function (): void {
        $condominium = createCondominiumEntity();
        $listUseCase = new ListCondominiumsUseCase($this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findAll')
            ->once()
            ->with([], 1, 20)
            ->andReturn([
                'items' => [$condominium],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $listUseCase->execute();

        expect($result->items)->toHaveCount(1)
            ->and($result->total)->toBe(1)
            ->and($result->page)->toBe(1)
            ->and($result->perPage)->toBe(20)
            ->and($result->lastPage)->toBe(1)
            ->and($result->items[0]->id)->toBe($condominium->id()->toString());
    });

    it('passes filters, page and per page to the repository', function (): void {
        $listUseCase = new ListCondominiumsUseCase($this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findAll')
            ->once()
            ->with(['name' => 'Test'], 2, 10)
            ->andReturn([
                'items' => [],
                'total' => 0,
                'page' => 2,
                'perPage' => 10,
                'lastPage' => 1,
            ]);

        $result = $listUseCase->execute(['name' => 'Test'], 2, 10);

        expect($result->items)->toBeEmpty();
    });
});

describe('GetCondominiumUseCase', function (): void {
    it('returns a condominium when it exists', function (): void {
        $condominium = createCondominiumEntity();
        $useCase = new GetCondominiumUseCase($this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($condominium);

        $result = $useCase->execute($condominium->id()->toString());

        expect($result->id)->toBe($condominium->id()->toString())
            ->and($result->name)->toBe($condominium->name());
    });

    it('throws CondominiumNotFoundException when it does not exist', function (): void {
        $useCase = new GetCondominiumUseCase($this->condominiumRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(CondominiumNotFoundException::class);
});

describe('UpdateCondominiumUseCase', function (): void {
    it('updates a condominium when it exists', function (): void {
        $condominium = createCondominiumEntity();
        $useCase = new UpdateCondominiumUseCase($this->condominiumRepository);
        $request = new UpdateCondominiumRequestDto(
            name: 'Updated Name',
            address: 'New Address',
            city: 'New City',
            department: 'New Department',
            country: 'New Country',
            nit: '123456',
            phone: '3001234567',
            email: 'test@example.com',
            legalRepresentative: 'New Representative',
            logoUrl: 'https://example.com/logo.png',
        );

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($condominium);

        $this->condominiumRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(CondominiumEntity::class));

        $result = $useCase->execute($condominium->id()->toString(), $request);

        expect($result->name)->toBe('Updated Name')
            ->and($result->address)->toBe('New Address')
            ->and($result->city)->toBe('New City')
            ->and($result->department)->toBe('New Department')
            ->and($result->country)->toBe('New Country')
            ->and($result->nit)->toBe('123456')
            ->and($result->phone)->toBe('3001234567')
            ->and($result->email)->toBe('test@example.com')
            ->and($result->legalRepresentative)->toBe('New Representative')
            ->and($result->logoUrl)->toBe('https://example.com/logo.png');
    });

    it('throws CondominiumNotFoundException when it does not exist', function (): void {
        $useCase = new UpdateCondominiumUseCase($this->condominiumRepository);
        $request = new UpdateCondominiumRequestDto(
            name: 'Updated Name',
            address: null,
            city: null,
            department: null,
            country: null,
            nit: null,
            phone: null,
            email: null,
            legalRepresentative: null,
            logoUrl: null,
        );

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), $request);
    })->throws(CondominiumNotFoundException::class);
});

describe('ValidateCoefficientsUseCase', function (): void {
    it('returns coefficient validation data when condominium exists', function (): void {
        $condominium = createCondominiumEntity(['totalCoefficient' => '1.000000']);
        $useCase = new ValidateCoefficientsUseCase($this->condominiumRepository, $this->propertyRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($condominium);

        $this->propertyRepository->shouldReceive('sumCoefficientsByCondominium')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(1.0);

        $this->propertyRepository->shouldReceive('countByCondominium')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(5);

        $result = $useCase->execute($condominium->id()->toString());

        expect($result['total_expected'])->toBe(1.0)
            ->and($result['total_actual'])->toBe(1.0)
            ->and($result['difference'])->toBe(0.0)
            ->and($result['is_balanced'])->toBeTrue()
            ->and($result['unit_count'])->toBe(5);
    });

    it('throws CondominiumNotFoundException when it does not exist', function (): void {
        $useCase = new ValidateCoefficientsUseCase($this->condominiumRepository, $this->propertyRepository);

        $this->condominiumRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(CondominiumNotFoundException::class);
});
