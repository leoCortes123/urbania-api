<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\Entities;

use Directorio\Domain\Entities\Contact;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

function createContact(array $overrides = []): Contact
{
    return new Contact(
        $overrides['id'] ?? Uuid::uuid7()->toString(),
        $overrides['documentType'] ?? new DocumentType('CC'),
        $overrides['documentNumber'] ?? new DocumentNumber('1234567890'),
        $overrides['fullName'] ?? 'Juan Pérez',
        $overrides['email'] ?? null,
        $overrides['phone'] ?? null,
        $overrides['emergencyContactName'] ?? null,
        $overrides['emergencyContactPhone'] ?? null,
        $overrides['notes'] ?? null,
        $overrides['userId'] ?? null,
        $overrides['organizationId'] ?? null,
        $overrides['createdAt'] ?? null,
        $overrides['updatedAt'] ?? null,
        $overrides['deletedAt'] ?? null,
    );
}

it('creates a contact with default values', function (): void {
    $contact = createContact();

    expect($contact->id())
        ->toBeString()
        ->and($contact->documentType()->value())->toBe('CC')
        ->and($contact->documentNumber()->value())->toBe('1234567890')
        ->and($contact->fullName())->toBe('Juan Pérez')
        ->and($contact->email())->toBeNull()
        ->and($contact->phone())->toBeNull()
        ->and($contact->emergencyContactName())->toBeNull()
        ->and($contact->emergencyContactPhone())->toBeNull()
        ->and($contact->notes())->toBeNull()
        ->and($contact->userId())->toBeNull()
        ->and($contact->organizationId())->toBeNull()
        ->and($contact->createdAt())->toBeNull()
        ->and($contact->updatedAt())->toBeNull()
        ->and($contact->deletedAt())->toBeNull()
        ->and($contact->isDeleted())->toBeFalse();
});

it('creates a contact with optional fields', function (): void {
    $contact = createContact([
        'email' => 'juan@example.com',
        'phone' => '3001234567',
        'emergencyContactName' => 'María Pérez',
        'emergencyContactPhone' => '3007654321',
        'notes' => 'Nota de prueba',
        'userId' => Uuid::uuid7()->toString(),
        'organizationId' => Uuid::uuid7()->toString(),
    ]);

    expect($contact->email())->toBe('juan@example.com')
        ->and($contact->phone())->toBe('3001234567')
        ->and($contact->emergencyContactName())->toBe('María Pérez')
        ->and($contact->emergencyContactPhone())->toBe('3007654321')
        ->and($contact->notes())->toBe('Nota de prueba')
        ->and($contact->userId())->toBeString()
        ->and($contact->organizationId())->toBeString();
});

it('throws exception when full name is empty', function (): void {
    createContact(['fullName' => '   ']);
})->throws(InvalidArgumentException::class, 'El nombre completo es requerido');

it('returns false for isDeleted by default and true when deletedAt is set', function (): void {
    $active = createContact();
    $deleted = createContact(['deletedAt' => '2026-06-30 00:00:00']);

    expect($active->isDeleted())->toBeFalse()
        ->and($deleted->isDeleted())->toBeTrue();
});
