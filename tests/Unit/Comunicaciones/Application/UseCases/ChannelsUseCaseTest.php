<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Application\UseCases;

use Mockery;
use Tests\TestCase;
use Urbania\Comunicaciones\Application\DTOs\UpdateChannelDto;
use Urbania\Comunicaciones\Application\UseCases\Channels\ListChannelsUseCase;
use Urbania\Comunicaciones\Application\UseCases\Channels\UpdateChannelUseCase;
use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

uses(TestCase::class);

function createChannelForTest(array $overrides = []): CommunicationChannelEntity
{
    return CommunicationChannelEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        canal: $overrides['canal'] ?? DeliveryChannel::EMAIL,
        provider: $overrides['provider'] ?? 'mailgun',
        config: $overrides['config'] ?? ['api_key' => 'test'],
        activo: $overrides['activo'] ?? true,
    );
}

beforeEach(function (): void {
    $this->channelRepository = Mockery::mock(CommunicationChannelRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('ListChannelsUseCase', function (): void {
    it('returns a list of channels for a condominium', function (): void {
        $condominiumId = Uuid::v7();
        $channel1 = createChannelForTest(['condominiumId' => $condominiumId]);
        $channel2 = createChannelForTest([
            'condominiumId' => $condominiumId,
            'canal' => DeliveryChannel::WHATSAPP,
            'provider' => 'twilio',
        ]);

        $useCase = new ListChannelsUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn([$channel1, $channel2]);

        $result = $useCase->execute($condominiumId);

        expect($result)->toHaveCount(2)
            ->and($result[0]->id)->toBe($channel1->id()->toString())
            ->and($result[0]->canal)->toBe(DeliveryChannel::EMAIL->value)
            ->and($result[0]->provider)->toBe('mailgun')
            ->and($result[0]->activo)->toBeTrue()
            ->and($result[1]->canal)->toBe(DeliveryChannel::WHATSAPP->value);
    });

    it('returns an empty array when there are no channels', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new ListChannelsUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn([]);

        $result = $useCase->execute($condominiumId);

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('UpdateChannelUseCase', function (): void {
    it('creates a new channel when it does not exist', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new UpdateChannelUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn(null);

        $this->channelRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(CommunicationChannelEntity::class));

        $dto = new UpdateChannelDto(
            condominiumId: $condominiumId,
            canal: DeliveryChannel::EMAIL,
            provider: 'mailgun',
            config: ['api_key' => 'test'],
            activo: true,
        );

        $result = $useCase->execute($dto);

        expect($result->canal)->toBe(DeliveryChannel::EMAIL->value)
            ->and($result->provider)->toBe('mailgun')
            ->and($result->activo)->toBeTrue()
            ->and($result->configMask)->toBe('***');
    });

    it('updates an existing channel', function (): void {
        $condominiumId = Uuid::v7();
        $existingChannel = createChannelForTest([
            'condominiumId' => $condominiumId,
            'provider' => 'old-provider',
            'config' => ['old_key' => 'old_value'],
            'activo' => false,
        ]);

        $useCase = new UpdateChannelUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($existingChannel);

        $this->channelRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(CommunicationChannelEntity::class));

        $dto = new UpdateChannelDto(
            condominiumId: $condominiumId,
            canal: DeliveryChannel::EMAIL,
            provider: 'new-provider',
            config: ['new_key' => 'new_value'],
            activo: true,
        );

        $result = $useCase->execute($dto);

        expect($result->provider)->toBe('new-provider')
            ->and($result->activo)->toBeTrue()
            ->and($result->configMask)->toBe('***');
    });

    it('preserves existing provider when provider is null on update', function (): void {
        $condominiumId = Uuid::v7();
        $existingChannel = createChannelForTest([
            'condominiumId' => $condominiumId,
            'provider' => 'original-provider',
            'config' => ['key' => 'original'],
            'activo' => true,
        ]);

        $useCase = new UpdateChannelUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($existingChannel);

        $this->channelRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(CommunicationChannelEntity::class));

        $dto = new UpdateChannelDto(
            condominiumId: $condominiumId,
            canal: DeliveryChannel::EMAIL,
            provider: null,
            config: ['new_key' => 'new_value'],
            activo: false,
        );

        $result = $useCase->execute($dto);

        expect($result->provider)->toBe('original-provider')
            ->and($result->activo)->toBeFalse()
            ->and($result->configMask)->toBe('***');
    });

    it('preserves existing config when config is null on update', function (): void {
        $condominiumId = Uuid::v7();
        $existingChannel = createChannelForTest([
            'condominiumId' => $condominiumId,
            'provider' => 'my-provider',
            'config' => ['original_key' => 'original_value'],
            'activo' => true,
        ]);

        $useCase = new UpdateChannelUseCase($this->channelRepository);

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($existingChannel);

        $this->channelRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(CommunicationChannelEntity::class));

        $dto = new UpdateChannelDto(
            condominiumId: $condominiumId,
            canal: DeliveryChannel::EMAIL,
            provider: 'new-provider',
            config: null,
            activo: false,
        );

        $result = $useCase->execute($dto);

        // Config is masked with '***' when present; provider updated, activo updated
        expect($result->provider)->toBe('new-provider')
            ->and($result->activo)->toBeFalse()
            ->and($result->configMask)->toBe('***');
    });
});
