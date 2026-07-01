<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Contacts;

use Directorio\Application\DTOs\CreateContactDTO;
use Directorio\Application\UseCases\Contacts\CreateContactUseCase;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\DuplicateContactDocumentException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Mockery;
use Ramsey\Uuid\Uuid;

function createContactEntity(array $overrides = []): Contact
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
        createdAt: $overrides['createdAt'] ?? null,
        updatedAt: $overrides['updatedAt'] ?? null,
        deletedAt: $overrides['deletedAt'] ?? null,
    );
}

beforeEach(function (): void {
    $this->contactRepository = Mockery::mock(ContactRepository::class);
    $this->useCase = new CreateContactUseCase($this->contactRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('creates a contact and returns it', function (): void {
    $dto = new CreateContactDTO(
        fullName: 'Juan Pérez',
        documentType: 'CC',
        documentNumber: '1234567890',
        email: 'juan@example.com',
        phone: '3001234567',
    );

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CC', '1234567890')
        ->andReturn(null);

    $this->contactRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Contact::class))
        ->andReturnUsing(function (Contact $contact): Contact {
            return $contact;
        });

    $result = $this->useCase->execute($dto);

    expect($result->fullName())->toBe('Juan Pérez')
        ->and($result->documentType()->value())->toBe('CC')
        ->and($result->documentNumber()->value())->toBe('1234567890')
        ->and($result->email())->toBe('juan@example.com')
        ->and($result->phone())->toBe('3001234567');
});

it('throws DuplicateContactDocumentException when document is already in use', function (): void {
    $dto = new CreateContactDTO(
        fullName: 'Juan Pérez',
        documentType: 'CC',
        documentNumber: '1234567890',
    );

    $existing = createContactEntity();

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CC', '1234567890')
        ->andReturn($existing);

    $this->useCase->execute($dto);
})->throws(DuplicateContactDocumentException::class);

it('allows creating contact when existing document belongs to a deleted contact', function (): void {
    $dto = new CreateContactDTO(
        fullName: 'Juan Pérez',
        documentType: 'CC',
        documentNumber: '1234567890',
    );

    $existing = createContactEntity(['deletedAt' => '2026-06-30 00:00:00']);

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CC', '1234567890')
        ->andReturn($existing);

    $this->contactRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Contact::class))
        ->andReturnUsing(function (Contact $contact): Contact {
            return $contact;
        });

    $result = $this->useCase->execute($dto);

    expect($result->fullName())->toBe('Juan Pérez');
});
