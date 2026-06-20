<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Repositories;

use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface RefreshTokenRepositoryInterface
{
    public function findByHash(string $hash): ?RefreshTokenEntity;

    public function save(RefreshTokenEntity $token): void;

    public function revoke(string $hash, string $reason): void;

    public function revokeAllByUser(Uuid $userId): void;

    /**
     * @return array<int, RefreshTokenEntity>
     */
    public function findActiveByUser(Uuid $userId): array;

    public function existsByHash(string $hash): bool;
}
