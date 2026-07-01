<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\AnnouncementDeliveryEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryStatus;
use Urbania\Shared\Domain\ValueObjects\Uuid;

/**
 * @param  array<string, mixed>|null  $metadata
 */
function createAnnouncementDeliveryEntity(array $overrides = []): AnnouncementDeliveryEntity
{
    return AnnouncementDeliveryEntity::create(
        $overrides['announcementId'] ?? Uuid::v7(),
        $overrides['contactId'] ?? Uuid::v7(),
        $overrides['canal'] ?? DeliveryChannel::EMAIL,
        $overrides['estado'] ?? DeliveryStatus::ENVIADO,
        $overrides['externalId'] ?? null,
        $overrides['metadata'] ?? null,
    );
}

it('create() creates delivery with UUID generated and fechas now', function (): void {
    $announcementId = Uuid::v7();
    $contactId = Uuid::v7();
    $delivery = createAnnouncementDeliveryEntity([
        'announcementId' => $announcementId,
        'contactId' => $contactId,
    ]);

    expect($delivery->id())->toBeInstanceOf(Uuid::class)
        ->and($delivery->announcementId()->toString())->toBe($announcementId->toString())
        ->and($delivery->contactId()->toString())->toBe($contactId->toString())
        ->and($delivery->canal())->toBe(DeliveryChannel::EMAIL)
        ->and($delivery->estado())->toBe(DeliveryStatus::ENVIADO)
        ->and($delivery->externalId())->toBeNull()
        ->and($delivery->metadata())->toBeNull()
        ->and($delivery->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($delivery->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $announcementId = Uuid::v7();
    $contactId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $delivery = AnnouncementDeliveryEntity::reconstitute(
        $id,
        $announcementId,
        $contactId,
        DeliveryChannel::WHATSAPP,
        DeliveryStatus::ENTREGADO,
        'ext-123',
        ['key' => 'value'],
        $createdAt,
        $updatedAt,
    );

    expect($delivery->id()->toString())->toBe($id->toString())
        ->and($delivery->announcementId()->toString())->toBe($announcementId->toString())
        ->and($delivery->contactId()->toString())->toBe($contactId->toString())
        ->and($delivery->canal())->toBe(DeliveryChannel::WHATSAPP)
        ->and($delivery->estado())->toBe(DeliveryStatus::ENTREGADO)
        ->and($delivery->externalId())->toBe('ext-123')
        ->and($delivery->metadata())->toBe(['key' => 'value'])
        ->and($delivery->createdAt())->toBe($createdAt)
        ->and($delivery->updatedAt())->toBe($updatedAt);
});

it('withStatus() changes estado preserving externalId/metadata if not passed', function (): void {
    $delivery = createAnnouncementDeliveryEntity([
        'externalId' => 'ext-456',
        'metadata' => ['old' => 'data'],
    ]);
    $previousUpdatedAt = $delivery->updatedAt();

    usleep(1000);

    $updated = $delivery->withStatus(DeliveryStatus::LEIDO);

    expect($updated->estado())->toBe(DeliveryStatus::LEIDO)
        ->and($updated->externalId())->toBe('ext-456')
        ->and($updated->metadata())->toBe(['old' => 'data'])
        ->and($updated->updatedAt())->toBeGreaterThan($previousUpdatedAt)
        ->and($updated->id()->toString())->toBe($delivery->id()->toString());
});

it('withStatus() overrides externalId and metadata when provided', function (): void {
    $delivery = createAnnouncementDeliveryEntity([
        'externalId' => 'ext-000',
        'metadata' => null,
    ]);

    $updated = $delivery->withStatus(DeliveryStatus::FALLIDO, 'ext-999', ['reason' => 'timeout']);

    expect($updated->estado())->toBe(DeliveryStatus::FALLIDO)
        ->and($updated->externalId())->toBe('ext-999')
        ->and($updated->metadata())->toBe(['reason' => 'timeout']);
});

it('exposes all getters', function (): void {
    $delivery = createAnnouncementDeliveryEntity([
        'externalId' => 'ext-001',
        'metadata' => ['track' => 'abc'],
    ]);

    expect($delivery->id())->toBeInstanceOf(Uuid::class)
        ->and($delivery->announcementId())->toBeInstanceOf(Uuid::class)
        ->and($delivery->contactId())->toBeInstanceOf(Uuid::class)
        ->and($delivery->canal())->toBeInstanceOf(DeliveryChannel::class)
        ->and($delivery->estado())->toBeInstanceOf(DeliveryStatus::class)
        ->and($delivery->externalId())->toBeString()
        ->and($delivery->metadata())->toBeArray()
        ->and($delivery->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($delivery->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
