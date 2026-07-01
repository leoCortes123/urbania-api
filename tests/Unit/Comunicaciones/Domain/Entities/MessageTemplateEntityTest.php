<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createMessageTemplate(array $overrides = []): MessageTemplateEntity
{
    return MessageTemplateEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['nombre'] ?? 'Bienvenida',
        $overrides['tipo'] ?? 'email',
        $overrides['cuerpo'] ?? '¡Hola! Bienvenido al condominio.',
    );
}

it('create() creates template with UUID generated and fechas now', function (): void {
    $condominiumId = Uuid::v7();
    $template = createMessageTemplate([
        'condominiumId' => $condominiumId,
    ]);

    expect($template->id())->toBeInstanceOf(Uuid::class)
        ->and($template->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($template->nombre())->toBe('Bienvenida')
        ->and($template->tipo())->toBe('email')
        ->and($template->cuerpo())->toBe('¡Hola! Bienvenido al condominio.')
        ->and($template->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($template->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($template->deletedAt())->toBeNull();
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $condominiumId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $template = MessageTemplateEntity::reconstitute(
        $id,
        $condominiumId,
        'Recordatorio',
        'sms',
        'No olvide la reunión.',
        $createdAt,
        $updatedAt,
        null,
    );

    expect($template->id()->toString())->toBe($id->toString())
        ->and($template->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($template->nombre())->toBe('Recordatorio')
        ->and($template->tipo())->toBe('sms')
        ->and($template->cuerpo())->toBe('No olvide la reunión.')
        ->and($template->createdAt())->toBe($createdAt)
        ->and($template->updatedAt())->toBe($updatedAt)
        ->and($template->deletedAt())->toBeNull();
});

it('update() changes all fields when all parameters provided', function (): void {
    $template = createMessageTemplate();
    $previousUpdatedAt = $template->updatedAt();

    usleep(1000);

    $updated = $template->update(
        nombre: 'Nuevo nombre',
        tipo: 'push',
        cuerpo: 'Nuevo cuerpo',
    );

    expect($updated->nombre())->toBe('Nuevo nombre')
        ->and($updated->tipo())->toBe('push')
        ->and($updated->cuerpo())->toBe('Nuevo cuerpo')
        ->and($updated->updatedAt())->toBeGreaterThan($previousUpdatedAt)
        ->and($updated->id()->toString())->toBe($template->id()->toString());
});

it('update() with partial merge preserves existing values', function (): void {
    $template = createMessageTemplate([
        'nombre' => 'Original',
        'tipo' => 'sms',
        'cuerpo' => 'Cuerpo original',
    ]);

    $updated = $template->update(nombre: 'Actualizado');

    expect($updated->nombre())->toBe('Actualizado')
        ->and($updated->tipo())->toBe('sms')
        ->and($updated->cuerpo())->toBe('Cuerpo original');
});

it('exposes all getters', function (): void {
    $template = createMessageTemplate();

    expect($template->id())->toBeInstanceOf(Uuid::class)
        ->and($template->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($template->nombre())->toBeString()
        ->and($template->tipo())->toBeString()
        ->and($template->cuerpo())->toBeString()
        ->and($template->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($template->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($template->deletedAt())->toBeNull();
});
