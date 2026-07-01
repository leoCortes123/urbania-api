<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Exceptions;

use ReflectionClass;
use Urbania\Comunicaciones\Domain\Exceptions\AnnouncementNotFoundException;
use Urbania\Comunicaciones\Domain\Exceptions\ChannelNotConfiguredException;
use Urbania\Comunicaciones\Domain\Exceptions\SegmentNotAvailableException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyAlreadyAnsweredException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyClosedException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyNotFoundException;
use Urbania\Comunicaciones\Domain\Exceptions\TemplateNotFoundException;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all comunicaciones exceptions extend DomainException', function (): void {
    $exceptions = [
        AnnouncementNotFoundException::class,
        ChannelNotConfiguredException::class,
        SegmentNotAvailableException::class,
        SurveyAlreadyAnsweredException::class,
        SurveyClosedException::class,
        SurveyNotFoundException::class,
        TemplateNotFoundException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    $cases = [
        [AnnouncementNotFoundException::class, 'ANNOUNCEMENT_NOT_FOUND', 404],
        [SurveyNotFoundException::class, 'SURVEY_NOT_FOUND', 404],
        [TemplateNotFoundException::class, 'TEMPLATE_NOT_FOUND', 404],
        [SurveyAlreadyAnsweredException::class, 'ALREADY_ANSWERED', 409],
        [SurveyClosedException::class, 'SURVEY_CLOSED', 422],
    ];

    foreach ($cases as [$exceptionClass, $expectedCode, $expectedStatus]) {
        $exception = new $exceptionClass;
        expect($exception->errorCode)->toBe($expectedCode)
            ->and($exception->httpStatusCode)->toBe($expectedStatus);
    }
});

it('ChannelNotConfiguredException accepts canal string and formats message', function (): void {
    $exception = new ChannelNotConfiguredException('whatsapp');

    expect($exception->getMessage())->toBe("El canal 'whatsapp' no está configurado o activo")
        ->and($exception->errorCode)->toBe('NO_ACTIVE_CHANNEL')
        ->and($exception->httpStatusCode)->toBe(422);
});

it('SegmentNotAvailableException accepts segmento string and formats message', function (): void {
    $exception = new SegmentNotAvailableException('torre');

    expect($exception->getMessage())->toBe("El segmento 'torre' no está disponible")
        ->and($exception->errorCode)->toBe('SEGMENT_NOT_AVAILABLE')
        ->and($exception->httpStatusCode)->toBe(422);
});

it('ChannelNotConfiguredException extends DomainException', function (): void {
    $reflection = new ReflectionClass(ChannelNotConfiguredException::class);
    expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
});

it('SegmentNotAvailableException extends DomainException', function (): void {
    $reflection = new ReflectionClass(SegmentNotAvailableException::class);
    expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
});

it('has correct default messages for no-arg exceptions', function (): void {
    $expectations = [
        [AnnouncementNotFoundException::class, 'El comunicado no fue encontrado'],
        [SurveyNotFoundException::class, 'La encuesta no fue encontrada'],
        [TemplateNotFoundException::class, 'La plantilla no fue encontrada'],
        [SurveyAlreadyAnsweredException::class, 'El contacto ya respondió esta encuesta'],
        [SurveyClosedException::class, 'La encuesta está cerrada'],
    ];

    foreach ($expectations as [$exceptionClass, $expectedMessage]) {
        $exception = new $exceptionClass;
        expect($exception->getMessage())->toBe($expectedMessage);
    }
});
