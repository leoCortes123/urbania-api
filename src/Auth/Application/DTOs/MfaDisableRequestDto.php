<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class MfaDisableRequestDto
{
    public function __construct(
        public string $password,
        public string $code,
    ) {}
}
