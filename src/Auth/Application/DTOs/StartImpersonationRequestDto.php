<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class StartImpersonationRequestDto
{
    public function __construct(
        public string $userId,
        public string $reason,
        public string $adminUserId,
        public string $adminIp,
    ) {}
}
