<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class RegisterRequestDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
        public ?string $phone = null,
        public ?string $unit = null,
    ) {}
}
