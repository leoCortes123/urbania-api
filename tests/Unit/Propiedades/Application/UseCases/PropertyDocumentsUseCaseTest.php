<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Application\UseCases;

use Illuminate\Http\UploadedFile;
use Mockery;
use Urbania\Propiedades\Application\DTOs\UploadPropertyDocumentRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\DeletePropertyDocumentUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\ListPropertyDocumentsUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\UploadPropertyDocumentUseCase;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Propiedades\Domain\Exceptions\DocumentInvalidTypeException;
use Urbania\Propiedades\Domain\Exceptions\DocumentTooLargeException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPropertyDocumentEntity(array $overrides = []): PropertyDocumentEntity
{
    return PropertyDocumentEntity::create(
        propertyId: $overrides['propertyId'] ?? Uuid::v7(),
        propertyDocumentTypeId: $overrides['propertyDocumentTypeId'] ?? Uuid::v7(),
        name: $overrides['name'] ?? 'documento.pdf',
        fileUrl: $overrides['fileUrl'] ?? '/storage/properties/test/documento.pdf',
        uploadedByUserId: $overrides['uploadedByUserId'] ?? Uuid::v7(),
        fileSizeBytes: $overrides['fileSizeBytes'] ?? 1024,
        mimeType: $overrides['mimeType'] ?? 'application/pdf',
        notes: $overrides['notes'] ?? null,
    );
}

function createPropertyDocumentTypeEntityForDocuments(array $overrides = []): PropertyDocumentTypeEntity
{
    return PropertyDocumentTypeEntity::create(
        code: $overrides['code'] ?? 'acta',
        name: $overrides['name'] ?? 'Acta',
        description: $overrides['description'] ?? null,
        sortOrder: $overrides['sortOrder'] ?? 0,
    );
}

function createPropertyEntityForDocuments(array $overrides = []): PropertyEntity
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
    $this->documentRepository = Mockery::mock(PropertyDocumentRepositoryInterface::class);
    $this->propertyRepository = Mockery::mock(PropertyRepositoryInterface::class);
    $this->documentTypeRepository = Mockery::mock(PropertyDocumentTypeRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('UploadPropertyDocumentUseCase', function (): void {
    it('uploads a document when all validations pass', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $uploadedByUserId = Uuid::v7();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.pdf',
            uploadedByUserId: $uploadedByUserId,
            notes: 'Nota',
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->twice()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->twice()
            ->andReturn('application/pdf');
        $file->shouldReceive('getClientOriginalName')
            ->once()
            ->andReturn('documento.pdf');
        $file->shouldReceive('storeAs')
            ->once()
            ->with("properties/{$property->id()->toString()}", Mockery::type('string'), 'public')
            ->andReturn("properties/{$property->id()->toString()}/documento.pdf");

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with($property->id())
            ->andReturn($property);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->with($documentType->id())
            ->andReturn($documentType);

        $this->documentRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PropertyDocumentEntity::class));

        $result = $useCase->execute($request, $file);

        expect($result->propertyId)->toBe($property->id()->toString())
            ->and($result->name)->toBe('Documento')
            ->and($result->mimeType)->toBe('application/pdf')
            ->and($result->fileSizeBytes)->toBe(1024)
            ->and($result->notes)->toBe('Nota')
            ->and($result->documentType['id'])->toBe($documentType->id()->toString())
            ->and($result->uploadedBy['id'])->toBe($uploadedByUserId->toString());
    });

    it('throws DocumentTooLargeException when file exceeds 20MB', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.pdf',
            uploadedByUserId: Uuid::v7(),
            notes: null,
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(20 * 1024 * 1024 + 1);

        $useCase->execute($request, $file);
    })->throws(DocumentTooLargeException::class);

    it('throws DocumentInvalidTypeException when mime type is not allowed', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.txt',
            uploadedByUserId: Uuid::v7(),
            notes: null,
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('text/plain');

        $useCase->execute($request, $file);
    })->throws(DocumentInvalidTypeException::class);

    it('throws PropertyNotFoundException when property does not exist', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.pdf',
            uploadedByUserId: Uuid::v7(),
            notes: null,
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('application/pdf');

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with($property->id())
            ->andReturn(null);

        $useCase->execute($request, $file);
    })->throws(PropertyNotFoundException::class);

    it('throws PropertyDocumentTypeNotFoundException when document type does not exist', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.pdf',
            uploadedByUserId: Uuid::v7(),
            notes: null,
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('application/pdf');

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $useCase->execute($request, $file);
    })->throws(PropertyDocumentTypeNotFoundException::class);

    it('throws PropertyDocumentTypeNotFoundException when document type is inactive', function (): void {
        $property = createPropertyEntityForDocuments();
        $documentType = createPropertyDocumentTypeEntityForDocuments();
        $documentType->deactivate();
        $useCase = new UploadPropertyDocumentUseCase(
            $this->documentRepository,
            $this->propertyRepository,
            $this->documentTypeRepository,
        );
        $request = new UploadPropertyDocumentRequestDto(
            propertyId: $property->id(),
            propertyDocumentTypeId: $documentType->id(),
            name: 'Documento',
            filePath: '/tmp/documento.pdf',
            uploadedByUserId: Uuid::v7(),
            notes: null,
        );

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('application/pdf');

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->documentTypeRepository->shouldReceive('findById')
            ->once()
            ->andReturn($documentType);

        $useCase->execute($request, $file);
    })->throws(PropertyDocumentTypeNotFoundException::class);
});

describe('ListPropertyDocumentsUseCase', function (): void {
    it('returns a paginated list of documents for a property', function (): void {
        $property = createPropertyEntityForDocuments();
        $document = createPropertyDocumentEntity(['propertyId' => $property->id()]);
        $useCase = new ListPropertyDocumentsUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->documentRepository->shouldReceive('findByPropertyId')
            ->once()
            ->with(Mockery::type(Uuid::class), 1, 20)
            ->andReturn([
                'items' => [$document],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($property->id()->toString());

        expect($result->items)->toHaveCount(1)
            ->and($result->items[0]->id)->toBe($document->id()->toString());
    });

    it('throws PropertyNotFoundException when property does not exist', function (): void {
        $useCase = new ListPropertyDocumentsUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);
});

describe('DeletePropertyDocumentUseCase', function (): void {
    it('deletes a document when property and document exist and match', function (): void {
        $property = createPropertyEntityForDocuments();
        $document = createPropertyDocumentEntity(['propertyId' => $property->id()]);
        $useCase = new DeletePropertyDocumentUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($property);

        $this->documentRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($document);

        $this->documentRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Uuid::class));

        $useCase->execute($property->id()->toString(), $document->id()->toString());

        expect(true)->toBeTrue();
    });

    it('throws PropertyNotFoundException when property does not exist', function (): void {
        $useCase = new DeletePropertyDocumentUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7()->toString(), Uuid::v7()->toString());
    })->throws(PropertyNotFoundException::class);

    it('throws PropertyDocumentNotFoundException when document does not exist', function (): void {
        $property = createPropertyEntityForDocuments();
        $useCase = new DeletePropertyDocumentUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->documentRepository->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $useCase->execute($property->id()->toString(), Uuid::v7()->toString());
    })->throws(PropertyDocumentNotFoundException::class);

    it('throws PropertyDocumentNotFoundException when document belongs to another property', function (): void {
        $property = createPropertyEntityForDocuments();
        $otherProperty = createPropertyEntityForDocuments();
        $document = createPropertyDocumentEntity(['propertyId' => $otherProperty->id()]);
        $useCase = new DeletePropertyDocumentUseCase($this->documentRepository, $this->propertyRepository);

        $this->propertyRepository->shouldReceive('findById')
            ->once()
            ->andReturn($property);

        $this->documentRepository->shouldReceive('findById')
            ->once()
            ->andReturn($document);

        $useCase->execute($property->id()->toString(), $document->id()->toString());
    })->throws(PropertyDocumentNotFoundException::class);
});
