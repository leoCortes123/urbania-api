<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class RefreshTokenRequestDto
{
    public function __construct(
        public string $refreshToken,
        public ?string $userAgent = null,
        public ?string $ipAddress = null,
    ) {}
}
