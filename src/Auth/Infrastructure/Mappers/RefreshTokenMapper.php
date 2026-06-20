<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Mappers;

use App\Models\RefreshToken as RefreshTokenModel;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class RefreshTokenMapper
{
    public function toDomain(RefreshTokenModel $model): RefreshTokenEntity
    {
        $deviceFp = $model->device_fingerprint
            ? DeviceFingerprint::fromHash($model->device_fingerprint)
            : null;

        $entity = RefreshTokenEntity::create(
            userId: Uuid::fromString($model->user_id),
            sessionId: SessionId::fromString($model->session_id),
            tokenHash: $model->token_hash,
            tokenFamily: Uuid::fromString($model->token_family),
            expiresAt: $this->toDateTimeImmutable($model->expires_at) ?? throw new \RuntimeException('Expected non-null datetime'),
            previousTokenHash: $model->previous_token_hash,
            deviceFingerprint: $deviceFp,
            deviceName: $model->device_name,
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
        );

        $this->setPrivateProperty($entity, 'id', Uuid::fromString($model->id));
        $this->setPrivateProperty($entity, 'createdAt', $this->toDateTimeImmutable($model->created_at) ?? throw new \RuntimeException('Expected non-null datetime'));
        $this->setPrivateProperty($entity, 'revokedAt', $this->toDateTimeImmutable($model->revoked_at));
        $this->setPrivateProperty($entity, 'revocationReason', $model->revocation_reason);
        $this->setPrivateProperty($entity, 'lastUsedAt', $this->toDateTimeImmutable($model->last_used_at));

        return $entity;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(RefreshTokenEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'user_id' => $entity->userId()->toString(),
            'session_id' => $entity->sessionId()->toString(),
            'token_hash' => $entity->tokenHash(),
            'token_family' => $entity->tokenFamily()->toString(),
            'previous_token_hash' => $entity->previousTokenHash(),
            'device_fingerprint' => $entity->deviceFingerprint()?->toString(),
            'device_name' => $entity->deviceName(),
            'ip_address' => $entity->ipAddress(),
            'user_agent' => $entity->userAgent(),
            'expires_at' => $entity->expiresAt()->format('Y-m-d H:i:s'),
            'revoked_at' => $entity->revokedAt()?->format('Y-m-d H:i:s'),
            'revocation_reason' => $entity->revocationReason(),
            'last_used_at' => $entity->lastUsedAt()?->format('Y-m-d H:i:s'),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    private function toDateTimeImmutable(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        assert($value instanceof \DateTime);

        return \DateTimeImmutable::createFromMutable($value);
    }
}
