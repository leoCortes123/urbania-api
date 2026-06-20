<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\Services;

use Urbania\Shared\Domain\ValueObjects\Email;

interface PasswordHistoryServiceInterface
{
    /**
     * @return array<int, string>
     */
    public function getRecent(Email $email, int $limit = 12): array;

    public function save(Email $email, string $passwordHash): void;
}
