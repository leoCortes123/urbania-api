<?php

declare(strict_types=1);

namespace Directorio\Application\DTOs;

final readonly class CreateContactDTO
{
    public function __construct(
        public string $fullName,
        public string $documentType,
        public string $documentNumber,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public ?string $notes = null,
        public ?string $userId = null,
        public ?string $organizationId = null,
    ) {}
}
