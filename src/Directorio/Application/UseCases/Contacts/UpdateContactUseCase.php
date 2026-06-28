<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Application\DTOs\UpdateContactDTO;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Exceptions\DuplicateContactDocumentException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;

readonly class UpdateContactUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
    ) {}

    public function execute(string $id, UpdateContactDTO $dto): Contact
    {
        $contact = $this->contactRepository->findById($id);
        if ($contact === null) {
            throw new ContactNotFoundException($id);
        }

        $documentType = $dto->documentType ?? $contact->documentType()->value();
        $documentNumber = $dto->documentNumber ?? $contact->documentNumber()->value();

        // Si cambió documento, validar unicidad
        if ($dto->documentType !== null || $dto->documentNumber !== null) {
            $existing = $this->contactRepository->findByDocument($documentType, $documentNumber);
            if ($existing !== null && $existing->id() !== $id && ! $existing->isDeleted()) {
                throw new DuplicateContactDocumentException($documentType, $documentNumber);
            }
        }

        $updated = new Contact(
            id: $id,
            documentType: new DocumentType($documentType),
            documentNumber: new DocumentNumber($documentNumber),
            fullName: $dto->fullName ?? $contact->fullName(),
            email: $dto->email ?? $contact->email(),
            phone: $dto->phone ?? $contact->phone(),
            emergencyContactName: $dto->emergencyContactName ?? $contact->emergencyContactName(),
            emergencyContactPhone: $dto->emergencyContactPhone ?? $contact->emergencyContactPhone(),
            notes: $dto->notes ?? $contact->notes(),
            userId: $dto->userId ?? $contact->userId(),
        );

        return $this->contactRepository->update($updated);
    }
}
