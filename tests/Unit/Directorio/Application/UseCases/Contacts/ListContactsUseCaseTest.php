<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Contacts;

use Directorio\Application\UseCases\Contacts\ListContactsUseCase;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Mockery;
use Ramsey\Uuid\Uuid;

function listContactEntity(array $overrides = []): Contact
{
    return new Contact(
        id: $overrides['id'] ?? Uuid::uuid7()->toString(),
        documentType: $overrides['documentType'] ?? new DocumentType('CC'),
        documentNumber: $overrides['documentNumber'] ?? new DocumentNumber('1234567890'),
        fullName: $overrides['fullName'] ?? 'Juan Pérez',
        email: $overrides['email'] ?? null,
        phone: $overrides['phone'] ?? null,
        emergencyContactName: $overrides['emergencyContactName'] ?? null,
        emergencyContactPhone: $overrides['emergencyContactPhone'] ?? null,
        notes: $overrides['notes'] ?? null,
        userId: $overrides['userId'] ?? null,
        organizationId: $overrides['organizationId'] ?? null,
    );
}

beforeEach(function (): void {
    $this->contactRepository = Mockery::mock(ContactRepository::class);
    $this->useCase = new ListContactsUseCase($this->contactRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns a list of contacts', function (): void {
    $contacts = [
        listContactEntity(['fullName' => 'Juan Pérez']),
        listContactEntity(['fullName' => 'María López']),
    ];

    $this->contactRepository->shouldReceive('findAll')
        ->once()
        ->with([])
        ->andReturn($contacts);

    $result = $this->useCase->execute();

    expect($result)->toBe($contacts)
        ->and($result)->toHaveCount(2);
});

it('passes filters to the repository', function (): void {
    $filters = ['organization_id' => Uuid::uuid7()->toString()];
    $contacts = [listContactEntity()];

    $this->contactRepository->shouldReceive('findAll')
        ->once()
        ->with($filters)
        ->andReturn($contacts);

    $result = $this->useCase->execute($filters);

    expect($result)->toBe($contacts);
});
