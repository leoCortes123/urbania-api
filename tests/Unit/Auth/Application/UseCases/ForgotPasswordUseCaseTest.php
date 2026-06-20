<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\ForgotPasswordRequestDto;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Application\UseCases\ForgotPasswordUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;

function createForgotPasswordUseCase(
    UserRepositoryInterface $userRepository,
    MailerServiceInterface $mailer,
    PasswordResetTokenRepositoryInterface $resetTokenRepository,
): ForgotPasswordUseCase {
    return new ForgotPasswordUseCase(
        $userRepository,
        $mailer,
        $resetTokenRepository,
        'https://urbania.example.com',
    );
}

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->mailer = Mockery::mock(MailerServiceInterface::class);
    $this->resetTokenRepository = Mockery::mock(PasswordResetTokenRepositoryInterface::class);

    $this->useCase = createForgotPasswordUseCase(
        $this->userRepository,
        $this->mailer,
        $this->resetTokenRepository,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns success when email does not exist', function (): void {
    $request = new ForgotPasswordRequestDto(email: 'missing@example.com');

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->with(Mockery::on(fn (Email $email): bool => $email->toString() === 'missing@example.com'))
        ->andReturn(null);

    $response = $this->useCase->execute($request);

    expect($response['success'])->toBeTrue()
        ->and($response['message'])->toContain('Si el correo existe');
});

it('stores token hash and sends email when user exists', function (): void {
    $request = new ForgotPasswordRequestDto(email: 'user@example.com');
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->resetTokenRepository->shouldReceive('save')
        ->once()
        ->with('user@example.com', Mockery::pattern('/^[a-f0-9]{64}$/'));

    $this->mailer->shouldReceive('sendPasswordResetEmail')
        ->once()
        ->with('user@example.com', Mockery::pattern('/reset-password/'));

    $response = $this->useCase->execute($request);

    expect($response['success'])->toBeTrue();
});
