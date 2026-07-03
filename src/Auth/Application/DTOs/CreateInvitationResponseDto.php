<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class CreateInvitationResponseDto
{
    public function __construct(
        public string $id,
        public string $token,
        public string $expiresAt,
    ) {}
}
