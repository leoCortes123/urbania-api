<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

/**
 * @param  array<string, mixed>|null  $config
 */
function createCommunicationChannel(array $overrides = []): CommunicationChannelEntity
{
    return CommunicationChannelEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['canal'] ?? DeliveryChannel::EMAIL,
        $overrides['provider'] ?? 'mailgun',
        $overrides['config'] ?? ['api_key' => 'test-key'],
        $overrides['activo'] ?? true,
    );
}

it('create() creates channel with canal, provider, config, activo', function (): void {
    $condominiumId = Uuid::v7();
    $channel = createCommunicationChannel([
        'condominiumId' => $condominiumId,
    ]);

    expect($channel->id())->toBeInstanceOf(Uuid::class)
        ->and($channel->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($channel->canal())->toBe(DeliveryChannel::EMAIL)
        ->and($channel->provider())->toBe('mailgun')
        ->and($channel->config())->toBe(['api_key' => 'test-key'])
        ->and($channel->activo())->toBeTrue()
        ->and($channel->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($channel->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $condominiumId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $channel = CommunicationChannelEntity::reconstitute(
        $id,
        $condominiumId,
        DeliveryChannel::WHATSAPP,
        'twilio',
        ['sid' => 'abc'],
        false,
        $createdAt,
        $updatedAt,
    );

    expect($channel->id()->toString())->toBe($id->toString())
        ->and($channel->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($channel->canal())->toBe(DeliveryChannel::WHATSAPP)
        ->and($channel->provider())->toBe('twilio')
        ->and($channel->config())->toBe(['sid' => 'abc'])
        ->and($channel->activo())->toBeFalse()
        ->and($channel->createdAt())->toBe($createdAt)
        ->and($channel->updatedAt())->toBe($updatedAt);
});

it('update() changes provider, config, activo', function (): void {
    $channel = createCommunicationChannel();
    $previousUpdatedAt = $channel->updatedAt();

    usleep(1000);

    $updated = $channel->update(
        provider: 'sendgrid',
        config: ['token' => 'new-token'],
        activo: false,
    );

    expect($updated->provider())->toBe('sendgrid')
        ->and($updated->config())->toBe(['token' => 'new-token'])
        ->and($updated->activo())->toBeFalse()
        ->and($updated->updatedAt())->toBeGreaterThan($previousUpdatedAt)
        ->and($updated->id()->toString())->toBe($channel->id()->toString())
        ->and($updated->canal())->toBe(DeliveryChannel::EMAIL);
});

it('exposes all getters', function (): void {
    $channel = createCommunicationChannel();

    expect($channel->id())->toBeInstanceOf(Uuid::class)
        ->and($channel->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($channel->canal())->toBeInstanceOf(DeliveryChannel::class)
        ->and($channel->provider())->toBeString()
        ->and($channel->config())->toBeArray()
        ->and($channel->activo())->toBeBool()
        ->and($channel->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($channel->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
