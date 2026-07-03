<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\StartImpersonationRequestDto;
use Urbania\Auth\Application\DTOs\StartImpersonationResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\Impersonation\StartImpersonationUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\SecurityEventRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\ImpersonationSession;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;

function createImpersonationUser(string $email, UserRole $role): UserEntity
{
    return UserEntity::create(
        Email::fromString($email),
        'Test User',
        Password::fromPlainText('SecureP@ss123'),
        $role,
    );
}

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->securityEventRepository = Mockery::mock(SecurityEventRepositoryInterface::class);

    $this->useCase = new StartImpersonationUseCase(
        $this->userRepository,
        $this->jwtService,
        $this->securityEventRepository,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('starts impersonation successfully', function (): void {
    $admin = createImpersonationUser('admin@test.com', UserRole::ADMIN);
    $targetUser = createImpersonationUser('resident@test.com', UserRole::USER);

    $request = new StartImpersonationRequestDto(
        userId: $targetUser->id()->toString(),
        reason: 'Soporte técnico',
        adminUserId: $admin->id()->toString(),
        adminIp: '192.168.1.100',
    );

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === $admin->id()->toString()))
        ->once()
        ->andReturn($admin);

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === $targetUser->id()->toString()))
        ->once()
        ->andReturn($targetUser);

    $this->jwtService->shouldReceive('generateImpersonationToken')
        ->once()
        ->with(
            Mockery::on(fn (string $userId) => $userId === $targetUser->id()->toString()),
            Mockery::on(fn (string $role) => $role === $targetUser->role()->value),
            Mockery::on(fn (string $orgId) => true), // organizationId
            Mockery::on(fn (string $adminId) => $adminId === $admin->id()->toString()),
            Mockery::type(ImpersonationSession::class),
            'Soporte técnico',
            900,
        )
        ->andReturn(JwtToken::fromString('impersonation-jwt-token'));

    $this->securityEventRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Urbania\Auth\Domain\Entities\SecurityEvent::class));

    $result = $this->useCase->execute($request);

    expect($result)->toBeInstanceOf(StartImpersonationResponseDto::class);
    expect($result->token)->toBe('impersonation-jwt-token');
    expect($result->expiresIn)->toBe(900);
    expect($result->sessionId)->not->toBeEmpty();
});

it('throws UserNotFoundException when admin does not exist', function (): void {
    $targetUser = createImpersonationUser('resident@test.com', UserRole::USER);

    $request = new StartImpersonationRequestDto(
        userId: $targetUser->id()->toString(),
        reason: 'Test',
        adminUserId: '018f4c80-0000-7000-0000-000000000000',
        adminIp: '127.0.0.1',
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request);
})->throws(UserNotFoundException::class);

it('throws UserNotFoundException when target user does not exist', function (): void {
    $admin = createImpersonationUser('admin@test.com', UserRole::ADMIN);

    $request = new StartImpersonationRequestDto(
        userId: '018f4c80-0000-7000-0000-000000000000',
        reason: 'Test',
        adminUserId: $admin->id()->toString(),
        adminIp: '127.0.0.1',
    );

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === $admin->id()->toString()))
        ->once()
        ->andReturn($admin);

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === '018f4c80-0000-7000-0000-000000000000'))
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request);
})->throws(UserNotFoundException::class);

it('throws UserNotFoundException when target user is not active', function (): void {
    $admin = createImpersonationUser('admin@test.com', UserRole::ADMIN);
    $targetUser = createImpersonationUser('resident@test.com', UserRole::USER);
    // Suspend the target user
    $targetUser->suspend();

    $request = new StartImpersonationRequestDto(
        userId: $targetUser->id()->toString(),
        reason: 'Test',
        adminUserId: $admin->id()->toString(),
        adminIp: '127.0.0.1',
    );

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === $admin->id()->toString()))
        ->once()
        ->andReturn($admin);

    $this->userRepository->shouldReceive('findById')
        ->with(Mockery::on(fn ($id) => $id->toString() === $targetUser->id()->toString()))
        ->once()
        ->andReturn($targetUser);

    $this->useCase->execute($request);
})->throws(UserNotFoundException::class);
