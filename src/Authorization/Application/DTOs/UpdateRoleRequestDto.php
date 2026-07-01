<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\DTOs;

final readonly class UpdateRoleRequestDto
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $level,
    ) {}
}
