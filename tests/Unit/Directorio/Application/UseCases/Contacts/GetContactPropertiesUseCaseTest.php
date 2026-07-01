<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Contacts;

use Directorio\Application\UseCases\Contacts\GetContactPropertiesUseCase;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Mockery;
use Ramsey\Uuid\Uuid;

function propertiesContactEntity(array $overrides = []): Contact
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

function propertiesOccupantEntity(array $overrides = []): PropertyOccupant
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
    $this->contactRepository = Mockery::mock(ContactRepository::class);
    $this->occupantRepository = Mockery::mock(PropertyOccupantRepository::class);
    $this->useCase = new GetContactPropertiesUseCase($this->contactRepository, $this->occupantRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns occupant records for a contact', function (): void {
    $contact = propertiesContactEntity();
    $occupants = [
        propertiesOccupantEntity(['contactId' => $contact->id()]),
        propertiesOccupantEntity(['contactId' => $contact->id()]),
    ];

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($contact->id())
        ->andReturn($contact);

    $this->occupantRepository->shouldReceive('findByContact')
        ->once()
        ->with($contact->id())
        ->andReturn($occupants);

    $result = $this->useCase->execute($contact->id());

    expect($result)->toBe($occupants)
        ->and($result)->toHaveCount(2);
});

it('throws ContactNotFoundException when contact does not exist', function (): void {
    $id = Uuid::uuid7()->toString();

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn(null);

    $this->useCase->execute($id);
})->throws(ContactNotFoundException::class);
