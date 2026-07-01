<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyDocument(array $overrides = []): PropertyDocumentEntity
{
    return PropertyDocumentEntity::create(
        $overrides['propertyId'] ?? Uuid::v7(),
        $overrides['propertyDocumentTypeId'] ?? Uuid::v7(),
        $overrides['name'] ?? 'Contrato.pdf',
        $overrides['fileUrl'] ?? 'https://storage.example.com/documents/contrato.pdf',
        $overrides['uploadedByUserId'] ?? Uuid::v7(),
        $overrides['fileSizeBytes'] ?? null,
        $overrides['mimeType'] ?? null,
        $overrides['notes'] ?? null,
    );
}

it('creates a property document with required values', function (): void {
    $propertyId = Uuid::v7();
    $propertyDocumentTypeId = Uuid::v7();
    $uploadedByUserId = Uuid::v7();

    $document = createPropertyDocument([
        'propertyId' => $propertyId,
        'propertyDocumentTypeId' => $propertyDocumentTypeId,
        'uploadedByUserId' => $uploadedByUserId,
    ]);

    expect($document->propertyId()->toString())->toBe($propertyId->toString())
        ->and($document->propertyDocumentTypeId()->toString())->toBe($propertyDocumentTypeId->toString())
        ->and($document->name())->toBe('Contrato.pdf')
        ->and($document->fileUrl())->toBe('https://storage.example.com/documents/contrato.pdf')
        ->and($document->uploadedByUserId()->toString())->toBe($uploadedByUserId->toString())
        ->and($document->fileSizeBytes())->toBeNull()
        ->and($document->mimeType())->toBeNull()
        ->and($document->notes())->toBeNull()
        ->and($document->deletedAt())->toBeNull();
});

it('creates a property document with all optional fields', function (): void {
    $document = createPropertyDocument([
        'fileSizeBytes' => 2_048_000,
        'mimeType' => 'application/pdf',
        'notes' => 'Documento firmado',
    ]);

    expect($document->fileSizeBytes())->toBe(2_048_000)
        ->and($document->mimeType())->toBe('application/pdf')
        ->and($document->notes())->toBe('Documento firmado');
});

it('exposes all getters', function (): void {
    $document = createPropertyDocument([
        'fileSizeBytes' => 1_024,
        'mimeType' => 'image/png',
        'notes' => 'Nota',
    ]);

    expect($document->id())->toBeInstanceOf(Uuid::class)
        ->and($document->propertyId())->toBeInstanceOf(Uuid::class)
        ->and($document->propertyDocumentTypeId())->toBeInstanceOf(Uuid::class)
        ->and($document->name())->toBeString()
        ->and($document->fileUrl())->toBeString()
        ->and($document->fileSizeBytes())->toBeInt()
        ->and($document->mimeType())->toBeString()
        ->and($document->notes())->toBeString()
        ->and($document->uploadedByUserId())->toBeInstanceOf(Uuid::class)
        ->and($document->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($document->deletedAt())->toBeNull();
});

it('deletedAt is null by default', function (): void {
    $document = createPropertyDocument();

    expect($document->deletedAt())->toBeNull();
});
