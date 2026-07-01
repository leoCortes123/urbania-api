<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\DTOs;

final readonly class CreateAssignmentRequestDto
{
    public function __construct(
        public string $userId,
        public string $roleId,
        public string $scopeType,
        public string $scopeId,
        public ?string $startsAt,
        public ?string $endsAt,
        public string $assignedByUserId,
    ) {}
}
