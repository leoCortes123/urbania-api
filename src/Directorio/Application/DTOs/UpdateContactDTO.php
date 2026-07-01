<?php

declare(strict_types=1);

namespace Directorio\Application\DTOs;

final readonly class UpdateContactDTO
{
    public function __construct(
        public ?string $fullName = null,
        public ?string $documentType = null,
        public ?string $documentNumber = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public ?string $notes = null,
        public ?string $userId = null,
        public ?string $organizationId = null,
    ) {}
}
