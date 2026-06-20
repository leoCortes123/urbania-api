<?php

declare(strict_types=1);

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\VerifyEmailUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyVerifiedException;
use Urbania\Auth\Domain\Exceptions\EmailVerificationInvalidException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);

    $this->useCase = new VerifyEmailUseCase(
        $this->userRepository,
        $this->jwtService,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('verifies email with valid token', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->with('valid-token')
        ->andReturn([
            'sub' => $user->id()->toString(),
            'scope' => 'email-verification',
            'exp' => time() + 3600,
        ]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with($user);

    $response = $this->useCase->execute('valid-token');

    expect($response['success'])->toBeTrue()
        ->and($user->emailVerifiedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('throws EmailVerificationInvalidException for invalid token', function (): void {
    $this->jwtService->shouldReceive('decode')
        ->once()
        ->with('invalid-token')
        ->andThrow(new RuntimeException('Invalid token'));

    $this->useCase->execute('invalid-token');
})->throws(EmailVerificationInvalidException::class);

it('throws EmailVerificationInvalidException for wrong scope', function (): void {
    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn([
            'sub' => (string) Uuid::v7(),
            'scope' => 'mfa-verify',
            'exp' => time() + 3600,
        ]);

    $this->useCase->execute('wrong-scope-token');
})->throws(EmailVerificationInvalidException::class);

it('throws UserNotFoundException when user does not exist', function (): void {
    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn([
            'sub' => (string) Uuid::v7(),
            'scope' => 'email-verification',
            'exp' => time() + 3600,
        ]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute('token');
})->throws(UserNotFoundException::class);

it('throws EmailAlreadyVerifiedException when email is already verified', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
    $user->markEmailAsVerified();

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn([
            'sub' => $user->id()->toString(),
            'scope' => 'email-verification',
            'exp' => time() + 3600,
        ]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute('token');
})->throws(EmailAlreadyVerifiedException::class);
