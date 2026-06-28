<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocuments;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Urbania\Propiedades\Application\DTOs\PropertyDocumentResponseDto;
use Urbania\Propiedades\Application\DTOs\UploadPropertyDocumentRequestDto;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Propiedades\Domain\Exceptions\DocumentInvalidTypeException;
use Urbania\Propiedades\Domain\Exceptions\DocumentTooLargeException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UploadPropertyDocumentUseCase
{
    private const MAX_SIZE_BYTES = 20 * 1024 * 1024;

    private const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    public function __construct(
        private PropertyDocumentRepositoryInterface $documentRepository,
        private PropertyRepositoryInterface $propertyRepository,
        private PropertyDocumentTypeRepositoryInterface $documentTypeRepository,
    ) {}

    public function execute(UploadPropertyDocumentRequestDto $request, UploadedFile $file): PropertyDocumentResponseDto
    {
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new DocumentTooLargeException;
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new DocumentInvalidTypeException;
        }

        $propertyId = $request->propertyId;
        if ($this->propertyRepository->findById($propertyId) === null) {
            throw new PropertyNotFoundException;
        }

        $documentType = $this->documentTypeRepository->findById($request->propertyDocumentTypeId);
        if ($documentType === null || ! $documentType->isActive()) {
            throw new PropertyDocumentTypeNotFoundException;
        }

        $filename = Uuid::v7()->toString().'-'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $directory = "properties/{$propertyId->toString()}";
        $path = $file->storeAs($directory, $filename, 'public');

        $entity = PropertyDocumentEntity::create(
            propertyId: $propertyId,
            propertyDocumentTypeId: $request->propertyDocumentTypeId,
            name: $request->name,
            fileUrl: "/storage/{$path}",
            uploadedByUserId: $request->uploadedByUserId,
            fileSizeBytes: $file->getSize(),
            mimeType: $file->getMimeType(),
            notes: $request->notes,
        );

        $this->documentRepository->save($entity);

        return PropertyDocumentResponseDto::fromEntity($entity);
    }
}
