<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

/**
 * @param  array<DeliveryChannel>|null  $canales
 */
function createAnnouncementEntity(array $overrides = []): AnnouncementEntity
{
    return AnnouncementEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['autorUserId'] ?? Uuid::v7(),
        $overrides['titulo'] ?? 'Comunicado de prueba',
        $overrides['cuerpo'] ?? 'Este es un comunicado de prueba.',
        $overrides['segmento'] ?? Segment::TODOS,
        $overrides['targetId'] ?? null,
        $overrides['estado'] ?? AnnouncementStatus::BORRADOR,
        $overrides['programadoPara'] ?? null,
        $overrides['fijado'] ?? false,
        $overrides['canales'] ?? [DeliveryChannel::EMAIL],
    );
}

it('create() creates entity with UUID generated, estado BORRADOR, and fechas now', function (): void {
    $condominiumId = Uuid::v7();
    $autorUserId = Uuid::v7();
    $announcement = createAnnouncementEntity([
        'condominiumId' => $condominiumId,
        'autorUserId' => $autorUserId,
    ]);

    expect($announcement->id())->toBeInstanceOf(Uuid::class)
        ->and($announcement->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($announcement->autorUserId()->toString())->toBe($autorUserId->toString())
        ->and($announcement->titulo())->toBe('Comunicado de prueba')
        ->and($announcement->cuerpo())->toBe('Este es un comunicado de prueba.')
        ->and($announcement->segmento())->toBe(Segment::TODOS)
        ->and($announcement->targetId())->toBeNull()
        ->and($announcement->estado())->toBe(AnnouncementStatus::BORRADOR)
        ->and($announcement->programadoPara())->toBeNull()
        ->and($announcement->fijado())->toBeFalse()
        ->and($announcement->canales())->toBe([DeliveryChannel::EMAIL])
        ->and($announcement->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($announcement->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($announcement->deletedAt())->toBeNull();
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $condominiumId = Uuid::v7();
    $autorUserId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $announcement = AnnouncementEntity::reconstitute(
        $id,
        $condominiumId,
        $autorUserId,
        'Título reconstitution',
        'Cuerpo reconstitution',
        Segment::TORRE,
        null,
        AnnouncementStatus::PROGRAMADO,
        null,
        true,
        [DeliveryChannel::WHATSAPP],
        $createdAt,
        $updatedAt,
        null,
    );

    expect($announcement->id()->toString())->toBe($id->toString())
        ->and($announcement->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($announcement->autorUserId()->toString())->toBe($autorUserId->toString())
        ->and($announcement->titulo())->toBe('Título reconstitution')
        ->and($announcement->cuerpo())->toBe('Cuerpo reconstitution')
        ->and($announcement->segmento())->toBe(Segment::TORRE)
        ->and($announcement->targetId())->toBeNull()
        ->and($announcement->estado())->toBe(AnnouncementStatus::PROGRAMADO)
        ->and($announcement->programadoPara())->toBeNull()
        ->and($announcement->fijado())->toBeTrue()
        ->and($announcement->canales())->toBe([DeliveryChannel::WHATSAPP])
        ->and($announcement->createdAt())->toBe($createdAt)
        ->and($announcement->updatedAt())->toBe($updatedAt)
        ->and($announcement->deletedAt())->toBeNull();
});

it('markAsSent() changes estado to ENVIADO and updates updatedAt', function (): void {
    $announcement = createAnnouncementEntity([
        'estado' => AnnouncementStatus::BORRADOR,
    ]);
    $previousUpdatedAt = $announcement->updatedAt();

    usleep(1000);

    $sent = $announcement->markAsSent();

    expect($sent->estado())->toBe(AnnouncementStatus::ENVIADO)
        ->and($sent->updatedAt())->toBeGreaterThan($previousUpdatedAt)
        ->and($sent->id()->toString())->toBe($announcement->id()->toString())
        ->and($sent->titulo())->toBe($announcement->titulo());
});

it('exposes all getters', function (): void {
    $announcement = createAnnouncementEntity([
        'fijado' => true,
        'programadoPara' => new \DateTimeImmutable('+2 days'),
    ]);

    expect($announcement->id())->toBeInstanceOf(Uuid::class)
        ->and($announcement->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($announcement->autorUserId())->toBeInstanceOf(Uuid::class)
        ->and($announcement->titulo())->toBeString()
        ->and($announcement->cuerpo())->toBeString()
        ->and($announcement->segmento())->toBeInstanceOf(Segment::class)
        ->and($announcement->targetId())->toBeNull()
        ->and($announcement->estado())->toBeInstanceOf(AnnouncementStatus::class)
        ->and($announcement->programadoPara())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($announcement->fijado())->toBeBool()
        ->and($announcement->canales())->toBeArray()
        ->and($announcement->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($announcement->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($announcement->deletedAt())->toBeNull();
});
