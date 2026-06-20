<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\ChangePasswordRequestDto;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Auth\Application\UseCases\ChangePasswordUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\PasswordChanged;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\PasswordReusedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->passwordHistoryService = Mockery::mock(PasswordHistoryServiceInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new ChangePasswordUseCase(
        $this->userRepository,
        $this->refreshTokenRepository,
        $this->passwordHistoryService,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('changes password successfully', function (): void {
    $user = UserEntity::create(
        email: Email::fromString('user@example.com'),
        name: 'John Doe',
        password: Password::fromPlainText('CurrentP@ss123'),
        role: UserRole::USER,
    );

    $request = new ChangePasswordRequestDto(
        currentPassword: 'CurrentP@ss123',
        newPassword: 'NewSecureP@ss123',
        newPasswordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->passwordHistoryService->shouldReceive('getRecent')
        ->once()
        ->andReturn([]);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with($user);

    $this->passwordHistoryService->shouldReceive('save')
        ->once();

    $this->refreshTokenRepository->shouldReceive('revokeAllByUser')
        ->once()
        ->with($user->id());

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(PasswordChanged::class));

    $response = $this->useCase->execute($request, $user->id()->toString());

    expect($response['success'])->toBeTrue()
        ->and($user->passwordHash()->verify('NewSecureP@ss123'))->toBeTrue();
});

it('throws UserNotFoundException when user does not exist', function (): void {
    $request = new ChangePasswordRequestDto(
        currentPassword: 'CurrentP@ss123',
        newPassword: 'NewSecureP@ss123',
        newPasswordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request, (string) Uuid::v7());
})->throws(UserNotFoundException::class);

it('throws InvalidCredentialsException when current password is wrong', function (): void {
    $user = UserEntity::create(
        email: Email::fromString('user@example.com'),
        name: 'John Doe',
        password: Password::fromPlainText('CurrentP@ss123'),
        role: UserRole::USER,
    );

    $request = new ChangePasswordRequestDto(
        currentPassword: 'WrongP@ss123',
        newPassword: 'NewSecureP@ss123',
        newPasswordConfirmation: 'NewSecureP@ss123',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($request, $user->id()->toString());
})->throws(InvalidCredentialsException::class);

it('throws PasswordReusedException when new password matches history', function (): void {
    $user = UserEntity::create(
        email: Email::fromString('user@example.com'),
        name: 'John Doe',
        password: Password::fromPlainText('CurrentP@ss123'),
        role: UserRole::USER,
    );

    $reusedHash = Password::fromPlainText('OldP@ss123')->toString();

    $request = new ChangePasswordRequestDto(
        currentPassword: 'CurrentP@ss123',
        newPassword: 'OldP@ss123',
        newPasswordConfirmation: 'OldP@ss123',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->passwordHistoryService->shouldReceive('getRecent')
        ->once()
        ->andReturn([$reusedHash]);

    $this->useCase->execute($request, $user->id()->toString());
})->throws(PasswordReusedException::class);

it('throws PasswordReusedException when new password equals current password', function (): void {
    $user = UserEntity::create(
        email: Email::fromString('user@example.com'),
        name: 'John Doe',
        password: Password::fromPlainText('CurrentP@ss123'),
        role: UserRole::USER,
    );

    $request = new ChangePasswordRequestDto(
        currentPassword: 'CurrentP@ss123',
        newPassword: 'CurrentP@ss123',
        newPasswordConfirmation: 'CurrentP@ss123',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->passwordHistoryService->shouldReceive('getRecent')
        ->once()
        ->andReturn([]);

    $this->useCase->execute($request, $user->id()->toString());
})->throws(PasswordReusedException::class);
