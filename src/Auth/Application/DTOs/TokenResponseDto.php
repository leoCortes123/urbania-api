<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class TokenResponseDto
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType,
        public int $expiresIn,
    ) {}
}
