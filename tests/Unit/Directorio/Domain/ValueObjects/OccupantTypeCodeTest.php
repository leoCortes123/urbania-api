<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\ValueObjects;

use Directorio\Domain\ValueObjects\OccupantTypeCode;

it('creates occupant type code with any string', function (): void {
    $code = new OccupantTypeCode('OWNER');

    expect($code->value())->toBe('OWNER');
});

it('returns the value', function (): void {
    $code = new OccupantTypeCode('TENANT');

    expect($code->value())->toBe('TENANT');
});

it('compares equality correctly', function (): void {
    $first = new OccupantTypeCode('OWNER');
    $second = new OccupantTypeCode('OWNER');
    $different = new OccupantTypeCode('TENANT');

    expect($first->equals($second))->toBeTrue()
        ->and($first->equals($different))->toBeFalse();
});

it('casts to string returning the value', function (): void {
    $code = new OccupantTypeCode('RESIDENT');

    expect((string) $code)->toBe('RESIDENT');
});
