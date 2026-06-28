<?php

declare(strict_types=1);

namespace Urbania\Directorio\Application\DTOs;

final readonly class ContactResponseDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $properties
     */
    public function __construct(
        public string $id,
        public string $fullName,
        public string $documentType,
        public string $documentNumber,
        public ?string $email,
        public ?string $phone,
        public ?string $emergencyContactName,
        public ?string $emergencyContactPhone,
        public ?string $notes,
        public ?string $userId,
        public array $properties = [],
        public string $createdAt = '',
        public string $updatedAt = '',
    ) {}
}
