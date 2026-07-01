<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\Exceptions;

use Directorio\Domain\Exceptions\ContactHasActiveOccupantsException;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Exceptions\DuplicateContactDocumentException;
use Directorio\Domain\Exceptions\DuplicateOccupantException;
use Directorio\Domain\Exceptions\MustHaveOwnerException;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use ReflectionClass;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all directorio exceptions extend DomainException', function (): void {
    $exceptions = [
        ContactNotFoundException::class,
        DuplicateContactDocumentException::class,
        ContactHasActiveOccupantsException::class,
        DuplicateOccupantException::class,
        OccupantNotFoundException::class,
        MustHaveOwnerException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    expect(new ContactNotFoundException)
        ->errorCode->toBe('CONTACT_NOT_FOUND')
        ->httpStatusCode->toBe(404);

    expect(new DuplicateContactDocumentException)
        ->errorCode->toBe('DUPLICATE_CONTACT_DOCUMENT')
        ->httpStatusCode->toBe(409);

    expect(new ContactHasActiveOccupantsException)
        ->errorCode->toBe('CONTACT_HAS_ACTIVE_OCCUPANTS')
        ->httpStatusCode->toBe(409);

    expect(new DuplicateOccupantException)
        ->errorCode->toBe('DUPLICATE_OCCUPANT')
        ->httpStatusCode->toBe(409);

    expect(new OccupantNotFoundException)
        ->errorCode->toBe('OCCUPANT_NOT_FOUND')
        ->httpStatusCode->toBe(404);

    expect(new MustHaveOwnerException)
        ->errorCode->toBe('MUST_HAVE_OWNER')
        ->httpStatusCode->toBe(409);
});

it('preserves custom messages', function (): void {
    $exception = new ContactNotFoundException('Custom contact not found message');

    expect($exception->getMessage())->toBe('Custom contact not found message');
});
