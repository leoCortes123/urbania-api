<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class MfaVerifyRequestDto
{
    public function __construct(
        public string $code,
    ) {}
}
