<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Contacts;

use Directorio\Application\UseCases\Contacts\GetContactUseCase;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Mockery;
use Ramsey\Uuid\Uuid;

function getContactEntity(array $overrides = []): Contact
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
    $this->useCase = new GetContactUseCase($this->contactRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns a contact by id', function (): void {
    $contact = getContactEntity();

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($contact->id())
        ->andReturn($contact);

    $result = $this->useCase->execute($contact->id());

    expect($result)->toBe($contact);
});

it('throws ContactNotFoundException when contact does not exist', function (): void {
    $id = Uuid::uuid7()->toString();

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn(null);

    $this->useCase->execute($id);
})->throws(ContactNotFoundException::class);
