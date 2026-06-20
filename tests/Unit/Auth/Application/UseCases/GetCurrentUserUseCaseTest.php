<?php

declare(strict_types=1);

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\GetCurrentUserUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);

    $this->useCase = new GetCurrentUserUseCase(
        $this->userRepository,
        $this->jwtService,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns user response for valid token', function (): void {
    $userId = Uuid::v7();
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->with('valid-token')
        ->andReturn(['sub' => $userId->toString()]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->with(Mockery::on(fn (Uuid $id): bool => $id->toString() === $userId->toString()))
        ->andReturn($user);

    $response = $this->useCase->execute('valid-token');

    expect($response->id)->toBe($user->id()->toString())
        ->and($response->email)->toBe('user@example.com')
        ->and($response->name)->toBe('John Doe')
        ->and($response->role)->toBe('user');
});

it('throws UserNotFoundException when user does not exist', function (): void {
    $userId = Uuid::v7();

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn(['sub' => $userId->toString()]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute('valid-token');
})->throws(UserNotFoundException::class);

it('throws UserNotFoundException when user is soft deleted', function (): void {
    $userId = Uuid::v7();
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
    $user->softDelete();

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn(['sub' => $userId->toString()]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute('valid-token');
})->throws(UserNotFoundException::class);

it('decodes sub claim correctly', function (): void {
    $userId = Uuid::v7();
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->with('bearer-token')
        ->andReturn([
            'sub' => $userId->toString(),
            'role' => 'user',
        ]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $response = $this->useCase->execute('bearer-token');

    expect($response)->not->toBeNull();
});
