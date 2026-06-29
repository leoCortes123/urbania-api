<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class UserResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public string $role,
        public string $status,
        public ?string $avatarUrl,
        public ?string $createdAt,
    ) {}
}
