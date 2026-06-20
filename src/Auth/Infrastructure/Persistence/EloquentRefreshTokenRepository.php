<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use App\Models\RefreshToken as RefreshTokenModel;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Infrastructure\Mappers\RefreshTokenMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private RefreshTokenMapper $mapper,
    ) {}

    public function findByHash(string $hash): ?RefreshTokenEntity
    {
        $model = RefreshTokenModel::where('token_hash', $hash)->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function save(RefreshTokenEntity $token): void
    {
        $data = $this->mapper->toPersistence($token);
        RefreshTokenModel::create($data);
    }

    public function revoke(string $hash, string $reason): void
    {
        RefreshTokenModel::where('token_hash', $hash)->update([
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    public function revokeAllByUser(Uuid $userId): void
    {
        RefreshTokenModel::where('user_id', $userId->toString())
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revocation_reason' => 'suspicious_activity',
            ]);
    }

    public function findActiveByUser(Uuid $userId): array
    {
        $models = RefreshTokenModel::where('user_id', $userId->toString())
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->get();

        return $models->map(fn ($model) => $this->mapper->toDomain($model))->all();
    }

    public function existsByHash(string $hash): bool
    {
        return RefreshTokenModel::where('token_hash', $hash)->exists();
    }
}
