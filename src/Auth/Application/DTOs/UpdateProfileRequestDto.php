<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class UpdateProfileRequestDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $avatar = null,
    ) {}
}
