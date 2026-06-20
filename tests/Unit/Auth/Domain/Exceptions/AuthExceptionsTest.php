<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Exceptions\DeviceNotRecognizedException;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyExistsException;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\MfaInvalidCodeException;
use Urbania\Auth\Domain\Exceptions\MfaRequiredException;
use Urbania\Auth\Domain\Exceptions\PasswordReusedException;
use Urbania\Auth\Domain\Exceptions\SessionNotFoundException;
use Urbania\Auth\Domain\Exceptions\TokenExpiredException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Exceptions\UserLockedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all auth exceptions extend DomainException', function (): void {
    $exceptions = [
        InvalidCredentialsException::class,
        TokenExpiredException::class,
        TokenInvalidException::class,
        UserNotFoundException::class,
        UserLockedException::class,
        MfaRequiredException::class,
        MfaInvalidCodeException::class,
        DeviceNotRecognizedException::class,
        SessionNotFoundException::class,
        PasswordReusedException::class,
        EmailAlreadyExistsException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    expect(new InvalidCredentialsException)
        ->errorCode->toBe('INVALID_CREDENTIALS')
        ->httpStatusCode->toBe(401);

    expect(new TokenExpiredException)
        ->errorCode->toBe('TOKEN_EXPIRED')
        ->httpStatusCode->toBe(401);

    expect(new TokenInvalidException)
        ->errorCode->toBe('TOKEN_INVALID')
        ->httpStatusCode->toBe(401);

    expect(new UserNotFoundException)
        ->errorCode->toBe('USER_NOT_FOUND')
        ->httpStatusCode->toBe(404);

    expect(new UserLockedException)
        ->errorCode->toBe('USER_LOCKED')
        ->httpStatusCode->toBe(401);

    expect(new MfaRequiredException)
        ->errorCode->toBe('MFA_REQUIRED')
        ->httpStatusCode->toBe(401);

    expect(new MfaInvalidCodeException)
        ->errorCode->toBe('MFA_INVALID_CODE')
        ->httpStatusCode->toBe(401);

    expect(new DeviceNotRecognizedException)
        ->errorCode->toBe('DEVICE_NOT_RECOGNIZED')
        ->httpStatusCode->toBe(403);

    expect(new SessionNotFoundException)
        ->errorCode->toBe('SESSION_NOT_FOUND')
        ->httpStatusCode->toBe(404);

    expect(new PasswordReusedException)
        ->errorCode->toBe('PASSWORD_REUSED')
        ->httpStatusCode->toBe(400);

    expect(new EmailAlreadyExistsException)
        ->errorCode->toBe('EMAIL_ALREADY_EXISTS')
        ->httpStatusCode->toBe(409);
});

it('weak password factory creates expected exception', function (): void {
    $exception = InvalidCredentialsException::weakPassword();

    expect($exception->errorCode)->toBe('INVALID_CREDENTIALS')
        ->and($exception->getMessage())->toBe('Password must be at least 8 characters')
        ->and($exception->httpStatusCode)->toBe(401);
});

it('preserves custom messages', function (): void {
    $exception = new UserNotFoundException('Custom not found message');

    expect($exception->getMessage())->toBe('Custom not found message');
});
