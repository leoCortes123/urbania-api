<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class EnablePortalResponseDto
{
    public function __construct(
        public string $activationCode,
        public string $expiresAt,
    ) {}
}
