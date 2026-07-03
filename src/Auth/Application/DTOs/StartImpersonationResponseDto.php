<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class StartImpersonationResponseDto
{
    public function __construct(
        public string $token,
        public int $expiresIn,
        public string $sessionId,
    ) {}
}
