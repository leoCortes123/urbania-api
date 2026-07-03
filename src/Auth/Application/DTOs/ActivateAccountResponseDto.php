<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class ActivateAccountResponseDto
{
    public function __construct(
        public string $message,
        public string $forcePasswordChangeToken,
    ) {}
}
