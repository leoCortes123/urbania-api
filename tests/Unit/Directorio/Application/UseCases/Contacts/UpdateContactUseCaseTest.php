<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Application\UseCases\Contacts;

use Directorio\Application\DTOs\UpdateContactDTO;
use Directorio\Application\UseCases\Contacts\UpdateContactUseCase;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Exceptions\DuplicateContactDocumentException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Mockery;
use Ramsey\Uuid\Uuid;

function updateContactEntity(array $overrides = []): Contact
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
    $this->useCase = new UpdateContactUseCase($this->contactRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('updates a contact', function (): void {
    $id = Uuid::uuid7()->toString();
    $contact = updateContactEntity(['id' => $id]);

    $dto = new UpdateContactDTO(
        fullName: 'Pedro Gómez',
        documentType: 'CE',
        documentNumber: '9876543210',
        email: 'pedro@example.com',
        phone: '3101234567',
        emergencyContactName: 'Ana Gómez',
        emergencyContactPhone: '3207654321',
        notes: 'Nota actualizada',
    );

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn($contact);

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CE', '9876543210')
        ->andReturn(null);

    $this->contactRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::type(Contact::class))
        ->andReturnUsing(function (Contact $updated): Contact {
            return $updated;
        });

    $result = $this->useCase->execute($id, $dto);

    expect($result->id())->toBe($id)
        ->and($result->fullName())->toBe('Pedro Gómez')
        ->and($result->documentType()->value())->toBe('CE')
        ->and($result->documentNumber()->value())->toBe('9876543210')
        ->and($result->email())->toBe('pedro@example.com')
        ->and($result->phone())->toBe('3101234567')
        ->and($result->emergencyContactName())->toBe('Ana Gómez')
        ->and($result->emergencyContactPhone())->toBe('3207654321')
        ->and($result->notes())->toBe('Nota actualizada');
});

it('throws ContactNotFoundException when contact does not exist', function (): void {
    $id = Uuid::uuid7()->toString();
    $dto = new UpdateContactDTO(fullName: 'Pedro Gómez');

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn(null);

    $this->useCase->execute($id, $dto);
})->throws(ContactNotFoundException::class);

it('throws DuplicateContactDocumentException when new document is used by another active contact', function (): void {
    $id = Uuid::uuid7()->toString();
    $contact = updateContactEntity(['id' => $id]);

    $otherId = Uuid::uuid7()->toString();
    $otherContact = updateContactEntity([
        'id' => $otherId,
        'documentType' => new DocumentType('CE'),
        'documentNumber' => new DocumentNumber('9876543210'),
    ]);

    $dto = new UpdateContactDTO(
        documentType: 'CE',
        documentNumber: '9876543210',
    );

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn($contact);

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CE', '9876543210')
        ->andReturn($otherContact);

    $this->useCase->execute($id, $dto);
})->throws(DuplicateContactDocumentException::class);

it('does not throw duplicate when document belongs to the same contact', function (): void {
    $id = Uuid::uuid7()->toString();
    $contact = updateContactEntity([
        'id' => $id,
        'documentType' => new DocumentType('CE'),
        'documentNumber' => new DocumentNumber('9876543210'),
    ]);

    $dto = new UpdateContactDTO(
        documentType: 'CE',
        documentNumber: '9876543210',
    );

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn($contact);

    $this->contactRepository->shouldReceive('findByDocument')
        ->once()
        ->with('CE', '9876543210')
        ->andReturn($contact);

    $this->contactRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::type(Contact::class))
        ->andReturnUsing(function (Contact $updated): Contact {
            return $updated;
        });

    $result = $this->useCase->execute($id, $dto);

    expect($result->id())->toBe($id)
        ->and($result->documentType()->value())->toBe('CE')
        ->and($result->documentNumber()->value())->toBe('9876543210');
});

it('preserves existing values on partial update', function (): void {
    $id = Uuid::uuid7()->toString();
    $contact = updateContactEntity([
        'id' => $id,
        'fullName' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'phone' => '3001234567',
        'notes' => 'Nota original',
    ]);

    $dto = new UpdateContactDTO(phone: '3109999999');

    $this->contactRepository->shouldReceive('findById')
        ->once()
        ->with($id)
        ->andReturn($contact);

    $this->contactRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::type(Contact::class))
        ->andReturnUsing(function (Contact $updated): Contact {
            return $updated;
        });

    $result = $this->useCase->execute($id, $dto);

    expect($result->fullName())->toBe('Juan Pérez')
        ->and($result->email())->toBe('juan@example.com')
        ->and($result->phone())->toBe('3109999999')
        ->and($result->notes())->toBe('Nota original');
});
