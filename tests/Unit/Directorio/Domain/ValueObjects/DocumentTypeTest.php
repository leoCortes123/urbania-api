<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\ValueObjects;

use Directorio\Domain\ValueObjects\DocumentType;
use InvalidArgumentException;

it('creates document type with valid values', function (): void {
    $cc = new DocumentType('CC');
    $nit = new DocumentType('NIT');
    $ce = new DocumentType('CE');
    $passport = new DocumentType('Pasaporte');
    $other = new DocumentType('Otro');

    expect($cc->value())->toBe('CC')
        ->and($nit->value())->toBe('NIT')
        ->and($ce->value())->toBe('CE')
        ->and($passport->value())->toBe('Pasaporte')
        ->and($other->value())->toBe('Otro');
});

it('throws InvalidArgumentException for invalid value', function (): void {
    new DocumentType('XX');
})->throws(InvalidArgumentException::class, 'Tipo de documento inválido: XX');

it('returns true for equals when values match and false otherwise', function (): void {
    $cc = new DocumentType('CC');
    $anotherCc = new DocumentType('CC');
    $nit = new DocumentType('NIT');

    expect($cc->equals($anotherCc))->toBeTrue()
        ->and($cc->equals($nit))->toBeFalse();
});

it('casts to string returning the value', function (): void {
    $documentType = new DocumentType('CE');

    expect((string) $documentType)->toBe('CE');
});
