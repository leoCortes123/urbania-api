<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\ValueObjects;

use Directorio\Domain\ValueObjects\DocumentNumber;
use InvalidArgumentException;

it('creates document number with valid value', function (): void {
    $documentNumber = new DocumentNumber('1234567890');

    expect($documentNumber->value())->toBe('1234567890');
});

it('throws InvalidArgumentException for empty value', function (): void {
    new DocumentNumber('   ');
})->throws(InvalidArgumentException::class, 'El número de documento no puede estar vacío');

it('normalizes whitespace', function (): void {
    $documentNumber = new DocumentNumber(' 123 456 7890 ');

    expect($documentNumber->value())->toBe('1234567890');
});

it('compares equality correctly', function (): void {
    $first = new DocumentNumber('1234567890');
    $second = new DocumentNumber('1234567890');
    $different = new DocumentNumber('0987654321');

    expect($first->equals($second))->toBeTrue()
        ->and($first->equals($different))->toBeFalse();
});

it('casts to string returning the value', function (): void {
    $documentNumber = new DocumentNumber('1234567890');

    expect((string) $documentNumber)->toBe('1234567890');
});
