<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\DTOs;

final readonly class CreateApprovalRuleRequestDto
{
    public function __construct(
        public string $resource,
        public string $action,
        public string $organizationId,
        public ?float $threshold,
        public string $approverRoleId,
        public bool $requiresSecondApproval,
    ) {}
}
