<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class ChangePasswordRequestDto
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
        public string $newPasswordConfirmation,
    ) {}
}
