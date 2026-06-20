<?php

declare(strict_types=1);

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Application\UseCases\ResendVerificationUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyVerifiedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->mailer = Mockery::mock(MailerServiceInterface::class);

    $this->useCase = new ResendVerificationUseCase(
        $this->userRepository,
        $this->jwtService,
        $this->mailer,
        'https://urbania.example.com',
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('sends verification email for unverified user', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $token = JwtToken::fromString('verification-token');

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->andReturn($token);

    $this->mailer->shouldReceive('sendVerificationEmail')
        ->once()
        ->with('user@example.com', 'https://urbania.example.com/verify-email?token=verification-token');

    $response = $this->useCase->execute($user->id()->toString());

    expect($response['success'])->toBeTrue();
});

it('throws UserNotFoundException when user does not exist', function (): void {
    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute((string) Uuid::v7());
})->throws(UserNotFoundException::class);

it('throws EmailAlreadyVerifiedException when email is already verified', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
    $user->markEmailAsVerified();

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($user->id()->toString());
})->throws(EmailAlreadyVerifiedException::class);
