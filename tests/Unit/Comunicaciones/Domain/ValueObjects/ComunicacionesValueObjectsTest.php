<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\ValueObjects;

use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;

it('AnnouncementStatus has expected cases and values', function (): void {
    expect(AnnouncementStatus::cases())->toHaveCount(3);

    expect(AnnouncementStatus::BORRADOR->value)->toBe('borrador')
        ->and(AnnouncementStatus::PROGRAMADO->value)->toBe('programado')
        ->and(AnnouncementStatus::ENVIADO->value)->toBe('enviado');
});

it('AnnouncementStatus tryFrom returns expected values', function (): void {
    expect(AnnouncementStatus::tryFrom('borrador'))->toBe(AnnouncementStatus::BORRADOR)
        ->and(AnnouncementStatus::tryFrom('programado'))->toBe(AnnouncementStatus::PROGRAMADO)
        ->and(AnnouncementStatus::tryFrom('enviado'))->toBe(AnnouncementStatus::ENVIADO)
        ->and(AnnouncementStatus::tryFrom('inexistente'))->toBeNull();
});

it('AnnouncementStatus from throws for invalid value', function (): void {
    AnnouncementStatus::from('inexistente');
})->throws(\ValueError::class);

it('AnnouncementStatus fromString works as expected', function (): void {
    expect(AnnouncementStatus::fromString('borrador'))->toBe(AnnouncementStatus::BORRADOR);
});

it('AnnouncementStatus fromString throws for invalid value', function (): void {
    AnnouncementStatus::fromString('inexistente');
})->throws(\InvalidArgumentException::class, 'Estado de comunicado inválido: inexistente');

it('DeliveryChannel has expected cases and values', function (): void {
    expect(DeliveryChannel::cases())->toHaveCount(3);

    expect(DeliveryChannel::WHATSAPP->value)->toBe('whatsapp')
        ->and(DeliveryChannel::EMAIL->value)->toBe('email')
        ->and(DeliveryChannel::PUSH->value)->toBe('push');
});

it('DeliveryChannel tryFrom returns expected values', function (): void {
    expect(DeliveryChannel::tryFrom('whatsapp'))->toBe(DeliveryChannel::WHATSAPP)
        ->and(DeliveryChannel::tryFrom('email'))->toBe(DeliveryChannel::EMAIL)
        ->and(DeliveryChannel::tryFrom('push'))->toBe(DeliveryChannel::PUSH)
        ->and(DeliveryChannel::tryFrom('sms'))->toBeNull();
});

it('DeliveryChannel from throws for invalid value', function (): void {
    DeliveryChannel::from('sms');
})->throws(\ValueError::class);

it('DeliveryStatus has expected cases and values', function (): void {
    expect(DeliveryStatus::cases())->toHaveCount(4);

    expect(DeliveryStatus::ENVIADO->value)->toBe('enviado')
        ->and(DeliveryStatus::ENTREGADO->value)->toBe('entregado')
        ->and(DeliveryStatus::LEIDO->value)->toBe('leido')
        ->and(DeliveryStatus::FALLIDO->value)->toBe('fallido');
});

it('DeliveryStatus tryFrom returns expected values', function (): void {
    expect(DeliveryStatus::tryFrom('enviado'))->toBe(DeliveryStatus::ENVIADO)
        ->and(DeliveryStatus::tryFrom('entregado'))->toBe(DeliveryStatus::ENTREGADO)
        ->and(DeliveryStatus::tryFrom('leido'))->toBe(DeliveryStatus::LEIDO)
        ->and(DeliveryStatus::tryFrom('fallido'))->toBe(DeliveryStatus::FALLIDO)
        ->and(DeliveryStatus::tryFrom('cancelado'))->toBeNull();
});

it('DeliveryStatus from throws for invalid value', function (): void {
    DeliveryStatus::from('cancelado');
})->throws(\ValueError::class);

it('Segment has expected cases and values', function (): void {
    expect(Segment::cases())->toHaveCount(4);

    expect(Segment::TODOS->value)->toBe('todos')
        ->and(Segment::TORRE->value)->toBe('torre')
        ->and(Segment::MOROSOS->value)->toBe('morosos')
        ->and(Segment::UNIDAD->value)->toBe('unidad');
});

it('Segment tryFrom returns expected values', function (): void {
    expect(Segment::tryFrom('todos'))->toBe(Segment::TODOS)
        ->and(Segment::tryFrom('torre'))->toBe(Segment::TORRE)
        ->and(Segment::tryFrom('morosos'))->toBe(Segment::MOROSOS)
        ->and(Segment::tryFrom('unidad'))->toBe(Segment::UNIDAD)
        ->and(Segment::tryFrom('segmento_invalido'))->toBeNull();
});

it('Segment from throws for invalid value', function (): void {
    Segment::from('segmento_invalido');
})->throws(\ValueError::class);

it('SurveyType has expected cases and values', function (): void {
    expect(SurveyType::cases())->toHaveCount(2);

    expect(SurveyType::SIMPLE->value)->toBe('simple')
        ->and(SurveyType::MULTIPLE->value)->toBe('multiple');
});

it('SurveyType tryFrom returns expected values', function (): void {
    expect(SurveyType::tryFrom('simple'))->toBe(SurveyType::SIMPLE)
        ->and(SurveyType::tryFrom('multiple'))->toBe(SurveyType::MULTIPLE)
        ->and(SurveyType::tryFrom('invalido'))->toBeNull();
});

it('SurveyType from throws for invalid value', function (): void {
    SurveyType::from('invalido');
})->throws(\ValueError::class);
