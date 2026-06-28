<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Application\DTOs\CreateContactDTO;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\DuplicateContactDocumentException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;
use Ramsey\Uuid\Uuid;

readonly class CreateContactUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
    ) {}

    public function execute(CreateContactDTO $dto): Contact
    {
        // Validar que no exista un contacto con el mismo documento
        $existing = $this->contactRepository->findByDocument($dto->documentType, $dto->documentNumber);
        if ($existing !== null && ! $existing->isDeleted()) {
            throw new DuplicateContactDocumentException($dto->documentType, $dto->documentNumber);
        }

        $contact = new Contact(
            id: Uuid::uuid7()->toString(),
            documentType: new DocumentType($dto->documentType),
            documentNumber: new DocumentNumber($dto->documentNumber),
            fullName: $dto->fullName,
            email: $dto->email,
            phone: $dto->phone,
            emergencyContactName: $dto->emergencyContactName,
            emergencyContactPhone: $dto->emergencyContactPhone,
            notes: $dto->notes,
            userId: $dto->userId,
        );

        return $this->contactRepository->save($contact);
    }
}
