<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Application\UseCases;

use DateTimeImmutable;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;
use Urbania\Comunicaciones\Application\DTOs\AnnouncementDto;
use Urbania\Comunicaciones\Application\DTOs\CreateAnnouncementDto;
use Urbania\Comunicaciones\Application\UseCases\Announcements\CreateAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\DeleteAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\GetAnnouncementUseCase;
use Urbania\Comunicaciones\Application\UseCases\Announcements\ListAnnouncementsUseCase;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\Exceptions\AnnouncementNotFoundException;
use Urbania\Comunicaciones\Domain\Exceptions\ChannelNotConfiguredException;
use Urbania\Comunicaciones\Domain\Exceptions\SegmentNotAvailableException;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementDeliveryRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Comunicaciones\Infrastructure\Jobs\SendAnnouncementDeliveriesJob;
use Urbania\Shared\Domain\ValueObjects\Uuid;

uses(TestCase::class);

function createAnnouncementEntity(array $overrides = []): AnnouncementEntity
{
    return AnnouncementEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        autorUserId: $overrides['autorUserId'] ?? Uuid::v7(),
        titulo: $overrides['titulo'] ?? 'Título del comunicado',
        cuerpo: $overrides['cuerpo'] ?? 'Cuerpo del comunicado',
        segmento: $overrides['segmento'] ?? Segment::TODOS,
        targetId: $overrides['targetId'] ?? null,
        estado: $overrides['estado'] ?? AnnouncementStatus::BORRADOR,
        programadoPara: $overrides['programadoPara'] ?? null,
        fijado: $overrides['fijado'] ?? false,
        canales: $overrides['canales'] ?? [DeliveryChannel::EMAIL],
    );
}

function createTestChannelEntity(array $overrides = []): CommunicationChannelEntity
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
    $this->announcementRepository = Mockery::mock(AnnouncementRepositoryInterface::class);
    $this->deliveryRepository = Mockery::mock(AnnouncementDeliveryRepositoryInterface::class);
    $this->channelRepository = Mockery::mock(CommunicationChannelRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreateAnnouncementUseCase', function (): void {
    it('creates announcement as BORRADOR when programadoPara is null', function (): void {
        $condominiumId = Uuid::v7();
        $autorUserId = Uuid::v7();
        $channel = createTestChannelEntity();

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($channel);

        $this->announcementRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(AnnouncementEntity::class));

        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: $condominiumId,
            autorUserId: $autorUserId,
            titulo: 'Título test',
            cuerpo: 'Cuerpo test',
            segmento: Segment::TODOS,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $result = $useCase->execute($dto);

        expect($result->titulo)->toBe('Título test')
            ->and($result->cuerpo)->toBe('Cuerpo test')
            ->and($result->estado)->toBe(AnnouncementStatus::BORRADOR->value)
            ->and($result->segmento)->toBe(Segment::TODOS->value)
            ->and($result->programadoPara)->toBeNull()
            ->and($result->fijado)->toBeFalse();
    });

    it('creates announcement as PROGRAMADO when programadoPara is set to a future date', function (): void {
        $condominiumId = Uuid::v7();
        $autorUserId = Uuid::v7();
        $channel = createTestChannelEntity();
        $futureDate = new DateTimeImmutable('+2 days');

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($channel);

        $this->announcementRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(AnnouncementEntity::class));

        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: $condominiumId,
            autorUserId: $autorUserId,
            titulo: 'Título programado',
            cuerpo: 'Cuerpo programado',
            segmento: Segment::TODOS,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: $futureDate,
            fijado: true,
        );

        $result = $useCase->execute($dto);

        expect($result->estado)->toBe(AnnouncementStatus::PROGRAMADO->value)
            ->and($result->programadoPara)->toBe($futureDate->format('c'))
            ->and($result->fijado)->toBeTrue();
    });

    it('throws SegmentNotAvailableException when segmento is MOROSOS', function (): void {
        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: Uuid::v7(),
            autorUserId: Uuid::v7(),
            titulo: 'Test',
            cuerpo: 'Test',
            segmento: Segment::MOROSOS,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);
    })->throws(SegmentNotAvailableException::class);

    it('throws InvalidArgumentException when segmento is TORRE and targetId is null', function (): void {
        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: Uuid::v7(),
            autorUserId: Uuid::v7(),
            titulo: 'Test',
            cuerpo: 'Test',
            segmento: Segment::TORRE,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);
    })->throws(\InvalidArgumentException::class, 'El target_id es obligatorio para el segmento seleccionado');

    it('throws InvalidArgumentException when segmento is UNIDAD and targetId is null', function (): void {
        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: Uuid::v7(),
            autorUserId: Uuid::v7(),
            titulo: 'Test',
            cuerpo: 'Test',
            segmento: Segment::UNIDAD,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);
    })->throws(\InvalidArgumentException::class, 'El target_id es obligatorio para el segmento seleccionado');

    it('throws ChannelNotConfiguredException when a channel is not configured', function (): void {
        $condominiumId = Uuid::v7();

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn(null);

        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: $condominiumId,
            autorUserId: Uuid::v7(),
            titulo: 'Test',
            cuerpo: 'Test',
            segmento: Segment::TODOS,
            targetId: null,
            canales: [DeliveryChannel::WHATSAPP],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);
    })->throws(ChannelNotConfiguredException::class);

    it('throws ChannelNotConfiguredException when a channel exists but is inactive', function (): void {
        $condominiumId = Uuid::v7();
        $inactiveChannel = createTestChannelEntity(['activo' => false]);

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($inactiveChannel);

        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: $condominiumId,
            autorUserId: Uuid::v7(),
            titulo: 'Test',
            cuerpo: 'Test',
            segmento: Segment::TODOS,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);
    })->throws(ChannelNotConfiguredException::class);

    it('dispatches SendAnnouncementDeliveriesJob after saving', function (): void {
        Bus::fake();

        $condominiumId = Uuid::v7();
        $channel = createTestChannelEntity();

        $this->channelRepository->shouldReceive('findByCondominiumAndChannel')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(DeliveryChannel::class))
            ->andReturn($channel);

        $this->announcementRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(AnnouncementEntity::class));

        $useCase = new CreateAnnouncementUseCase(
            $this->announcementRepository,
            $this->channelRepository,
        );

        $dto = new CreateAnnouncementDto(
            condominiumId: $condominiumId,
            autorUserId: Uuid::v7(),
            titulo: 'Test dispatch',
            cuerpo: 'Cuerpo dispatch',
            segmento: Segment::TODOS,
            targetId: null,
            canales: [DeliveryChannel::EMAIL],
            programadoPara: null,
            fijado: false,
        );

        $useCase->execute($dto);

        Bus::assertDispatched(SendAnnouncementDeliveriesJob::class);
    });
});

describe('DeleteAnnouncementUseCase', function (): void {
    it('deletes an existing announcement', function (): void {
        $announcement = createAnnouncementEntity();
        $useCase = new DeleteAnnouncementUseCase($this->announcementRepository);

        $this->announcementRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($announcement);

        $this->announcementRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Uuid::class));

        $useCase->execute($announcement->id());

        // If no exception is thrown, the test passes
        expect(true)->toBeTrue();
    });

    it('throws AnnouncementNotFoundException when announcement does not exist', function (): void {
        $useCase = new DeleteAnnouncementUseCase($this->announcementRepository);

        $this->announcementRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7());
    })->throws(AnnouncementNotFoundException::class);
});

describe('GetAnnouncementUseCase', function (): void {
    it('returns AnnouncementDetailDto with delivery breakdown', function (): void {
        $announcement = createAnnouncementEntity();
        $useCase = new GetAnnouncementUseCase(
            $this->announcementRepository,
            $this->deliveryRepository,
        );

        $breakdown = [
            'byStatus' => ['enviado' => 5, 'entregado' => 3, 'leido' => 2],
            'byChannel' => ['email' => ['enviado' => 3, 'entregado' => 2]],
        ];

        $this->announcementRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($announcement);

        $this->deliveryRepository->shouldReceive('breakdownByAnnouncementId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($breakdown);

        $result = $useCase->execute($announcement->id());

        expect($result->announcement)->toBeInstanceOf(AnnouncementDto::class)
            ->and($result->announcement->id)->toBe($announcement->id()->toString())
            ->and($result->announcement->titulo)->toBe($announcement->titulo())
            ->and($result->breakdown)->toBe($breakdown);
    });

    it('throws AnnouncementNotFoundException when announcement does not exist', function (): void {
        $useCase = new GetAnnouncementUseCase(
            $this->announcementRepository,
            $this->deliveryRepository,
        );

        $this->announcementRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7());
    })->throws(AnnouncementNotFoundException::class);
});

describe('ListAnnouncementsUseCase', function (): void {
    it('returns a paginated list of announcements', function (): void {
        $condominiumId = Uuid::v7();
        $announcement = createAnnouncementEntity(['condominiumId' => $condominiumId]);
        $useCase = new ListAnnouncementsUseCase(
            $this->announcementRepository,
            $this->deliveryRepository,
        );

        $metrics = ['enviados' => 5, 'entregados' => 3, 'leidos' => 2];

        $this->announcementRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), [], 1, 20)
            ->andReturn([
                'items' => [$announcement],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $this->deliveryRepository->shouldReceive('metricsByAnnouncementId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($metrics);

        $result = $useCase->execute($condominiumId, [], 1, 20);

        expect($result['items'])->toHaveCount(1)
            ->and($result['total'])->toBe(1)
            ->and($result['page'])->toBe(1)
            ->and($result['perPage'])->toBe(20)
            ->and($result['lastPage'])->toBe(1)
            ->and($result['items'][0]->id)->toBe($announcement->id()->toString())
            ->and($result['items'][0]->titulo)->toBe($announcement->titulo())
            ->and($result['items'][0]->metrics)->toBe($metrics);
    });

    it('filters by condominiumId', function (): void {
        $condominiumId = Uuid::v7();
        $anotherCondominiumId = Uuid::v7();
        $useCase = new ListAnnouncementsUseCase(
            $this->announcementRepository,
            $this->deliveryRepository,
        );

        $this->announcementRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), [], 1, 20)
            ->andReturn([
                'items' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominiumId, [], 1, 20);

        expect($result['items'])->toBeEmpty()
            ->and($result['total'])->toBe(0);
    });

    it('supports pagination parameters', function (): void {
        $condominiumId = Uuid::v7();
        $announcement = createAnnouncementEntity(['condominiumId' => $condominiumId]);
        $useCase = new ListAnnouncementsUseCase(
            $this->announcementRepository,
            $this->deliveryRepository,
        );

        $this->announcementRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), ['estado' => 'borrador'], 2, 10)
            ->andReturn([
                'items' => [$announcement],
                'total' => 15,
                'page' => 2,
                'perPage' => 10,
                'lastPage' => 2,
            ]);

        $this->deliveryRepository->shouldReceive('metricsByAnnouncementId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(['enviados' => 0, 'entregados' => 0, 'leidos' => 0]);

        $result = $useCase->execute($condominiumId, ['estado' => 'borrador'], 2, 10);

        expect($result['page'])->toBe(2)
            ->and($result['perPage'])->toBe(10);
    });
});
