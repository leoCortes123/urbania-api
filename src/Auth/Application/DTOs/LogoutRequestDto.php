<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class LogoutRequestDto
{
    public function __construct(
        public string $refreshToken,
    ) {}
}
