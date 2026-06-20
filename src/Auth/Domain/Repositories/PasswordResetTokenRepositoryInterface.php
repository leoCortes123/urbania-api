<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Repositories;

interface PasswordResetTokenRepositoryInterface
{
    public function save(string $email, string $tokenHash): void;

    /**
     * @return array{token: string, created_at: \DateTimeImmutable}|null
     */
    public function findByEmail(string $email): ?array;

    public function delete(string $email): void;
}
