<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\ResetPasswordRequestDto;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Auth\Application\UseCases\ResetPasswordUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\PasswordChanged;
use Urbania\Auth\Domain\Exceptions\InvalidResetTokenException;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->resetTokenRepository = Mockery::mock(PasswordResetTokenRepositoryInterface::class);
    $this->passwordHistoryService = Mockery::mock(PasswordHistoryServiceInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new ResetPasswordUseCase(
        $this->userRepository,
        $this->refreshTokenRepository,
        $this->resetTokenRepository,
        $this->passwordHistoryService,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('resets password with valid token', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('OldSecureP@ss123'),
        UserRole::USER,
    );

    $token = bin2hex(random_bytes(64));
    $tokenHash = hash('sha256', $token);

    $request = new ResetPasswordRequestDto(
        email: 'user@example.com',
        token: $token,
        password: 'NewSecureP@ss123',
        passwordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->resetTokenRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn([
            'token' => $tokenHash,
            'created_at' => new DateTimeImmutable('-30 minutes'),
        ]);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::type(UserEntity::class));

    $this->passwordHistoryService->shouldReceive('save')
        ->once();

    $this->refreshTokenRepository->shouldReceive('revokeAllByUser')
        ->once()
        ->with($user->id());

    $this->resetTokenRepository->shouldReceive('delete')
        ->once()
        ->with('user@example.com');

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PasswordChanged::class));

    $response = $this->useCase->execute($request);

    expect($response['success'])->toBeTrue()
        ->and($user->passwordHash()->verify('NewSecureP@ss123'))->toBeTrue();
});

it('throws InvalidResetTokenException when user does not exist', function (): void {
    $request = new ResetPasswordRequestDto(
        email: 'missing@example.com',
        token: 'some-token',
        password: 'NewSecureP@ss123',
        passwordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request);
})->throws(InvalidResetTokenException::class);

it('throws InvalidResetTokenException when token is invalid', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('OldSecureP@ss123'),
        UserRole::USER,
    );

    $request = new ResetPasswordRequestDto(
        email: 'user@example.com',
        token: 'invalid-token',
        password: 'NewSecureP@ss123',
        passwordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->resetTokenRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn([
            'token' => hash('sha256', 'valid-token'),
            'created_at' => new DateTimeImmutable('-30 minutes'),
        ]);

    $this->useCase->execute($request);
})->throws(InvalidResetTokenException::class);

it('throws InvalidResetTokenException when token has expired', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('OldSecureP@ss123'),
        UserRole::USER,
    );

    $token = bin2hex(random_bytes(64));
    $tokenHash = hash('sha256', $token);

    $request = new ResetPasswordRequestDto(
        email: 'user@example.com',
        token: $token,
        password: 'NewSecureP@ss123',
        passwordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->resetTokenRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn([
            'token' => $tokenHash,
            'created_at' => new DateTimeImmutable('-90 minutes'),
        ]);

    $this->useCase->execute($request);
})->throws(InvalidResetTokenException::class);
