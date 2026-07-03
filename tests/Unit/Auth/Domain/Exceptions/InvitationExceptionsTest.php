<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Exceptions\InvitationAlreadyAcceptedException;
use Urbania\Auth\Domain\Exceptions\InvitationAlreadyPendingException;
use Urbania\Auth\Domain\Exceptions\InvitationExpiredException;
use Urbania\Auth\Domain\Exceptions\InvalidOccupantTypeException;
use Urbania\Auth\Domain\Exceptions\NotPortalPrimaryException;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all invitation exceptions extend DomainException', function (): void {
    $exceptions = [
        InvitationExpiredException::class,
        InvitationAlreadyAcceptedException::class,
        InvitationAlreadyPendingException::class,
        NotPortalPrimaryException::class,
        InvalidOccupantTypeException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    expect(new InvitationExpiredException)
        ->errorCode->toBe('INVITATION_EXPIRED')
        ->httpStatusCode->toBe(422);

    expect(new InvitationAlreadyAcceptedException)
        ->errorCode->toBe('INVITATION_ALREADY_ACCEPTED')
        ->httpStatusCode->toBe(422);

    expect(new InvitationAlreadyPendingException)
        ->errorCode->toBe('INVITATION_ALREADY_PENDING')
        ->httpStatusCode->toBe(409);

    expect(new NotPortalPrimaryException)
        ->errorCode->toBe('NOT_PORTAL_PRIMARY')
        ->httpStatusCode->toBe(403);

    expect(new InvalidOccupantTypeException)
        ->errorCode->toBe('INVALID_OCCUPANT_TYPE')
        ->httpStatusCode->toBe(422);
});

it('preserves custom messages', function (): void {
    $exception = new InvitationExpiredException('Mi mensaje personalizado');
    expect($exception->getMessage())->toBe('Mi mensaje personalizado');
    expect($exception->errorCode)->toBe('INVITATION_EXPIRED');
});
