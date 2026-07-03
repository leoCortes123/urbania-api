<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class UnitOccupantDto
{
    public function __construct(
        public string $occupantId,
        public string $contactId,
        public ?string $userId,
        public string $fullName,
        public string $email,
        public ?string $phone,
        public string $occupantType,
        public bool $isLegalOwner,
        public bool $isPortalPrimary,
        public bool $isPrimary,
        public ?string $userStatus,
        public bool $isActive,
    ) {}
}
