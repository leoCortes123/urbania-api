<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\LoginRequestDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\LoginUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserLoggedIn;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\UserLockedException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

function createLoginUser(): UserEntity
{
    return UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
}

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new LoginUseCase(
        $this->userRepository,
        $this->refreshTokenRepository,
        $this->jwtService,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns tokens and user on successful login', function (): void {
    $user = createLoginUser();
    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'SecureP@ss123',
        userAgent: 'Mozilla/5.0',
        ipAddress: '192.168.1.1',
        acceptLanguage: 'en-US',
        deviceName: 'Test Device',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::on(fn (UserEntity $u): bool => $u->lastLoginIp() === '192.168.1.1'));

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->andReturn(JwtToken::fromString('access-token'));

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->once()
        ->andReturn('raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(RefreshTokenEntity::class));

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(UserLoggedIn::class));

    $response = $this->useCase->execute($request);

    expect($response->accessToken)->toBe('access-token')
        ->and($response->refreshToken)->toBe('raw-refresh-token')
        ->and($response->tokenType)->toBe('bearer')
        ->and($response->expiresIn)->toBe(900)
        ->and($response->user->email)->toBe('user@example.com')
        ->and($response->status)->toBeNull()
        ->and($response->limitedToken)->toBeNull();
});

it('throws InvalidCredentialsException when email does not exist', function (): void {
    $request = new LoginRequestDto(
        email: 'missing@example.com',
        password: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request);
})->throws(InvalidCredentialsException::class);

it('throws InvalidCredentialsException and increments failed attempts on wrong password', function (): void {
    $user = createLoginUser();
    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'WrongPassword123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::on(fn (UserEntity $u): bool => $u->failedLoginAttempts() === 1));

    try {
        $this->useCase->execute($request);
    } catch (InvalidCredentialsException) {
        expect($user->failedLoginAttempts())->toBe(1);

        return;
    }

    $this->fail('Expected InvalidCredentialsException was not thrown');
});

it('throws UserLockedException when account is locked', function (): void {
    $user = createLoginUser();
    for ($i = 0; $i < 5; $i++) {
        $user->recordFailedLogin();
    }

    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($request);
})->throws(UserLockedException::class);

it('returns FORCE_PASSWORD_CHANGE status with limited token', function (): void {
    $user = createLoginUser();
    $reflection = new ReflectionClass($user);
    $mustChangePasswordProperty = $reflection->getProperty('mustChangePassword');
    $mustChangePasswordProperty->setValue($user, true);

    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->with(
            Mockery::on(fn (string $userId): bool => $userId === $user->id()->toString()),
            'user',
            false,
            Mockery::type(SessionId::class),
            '',
            'change-password',
            300,
        )
        ->andReturn(JwtToken::fromString('limited-token'));

    $response = $this->useCase->execute($request);

    expect($response->status)->toBe('FORCE_PASSWORD_CHANGE')
        ->and($response->limitedToken)->toBe('limited-token')
        ->and($response->accessToken)->toBe('')
        ->and($response->refreshToken)->toBe('')
        ->and($response->expiresIn)->toBe(300);
});

it('returns MFA_REQUIRED status with limited token when MFA is enabled', function (): void {
    $user = createLoginUser();
    $user->enableMfa('secret', ['code1', 'code2']);

    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'SecureP@ss123',
        userAgent: 'Mozilla/5.0',
        ipAddress: '192.168.1.1',
        acceptLanguage: 'en-US',
        deviceName: 'Test Device',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $expectedFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: 'en-US',
        deviceName: 'Test Device',
    );

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->with(
            Mockery::on(fn (string $userId): bool => $userId === $user->id()->toString()),
            'user',
            false,
            Mockery::type(SessionId::class),
            $expectedFingerprint->toString(),
            'mfa-verify',
            300,
        )
        ->andReturn(JwtToken::fromString('mfa-token'));

    $response = $this->useCase->execute($request);

    expect($response->status)->toBe('MFA_REQUIRED')
        ->and($response->limitedToken)->toBe('mfa-token')
        ->and($response->accessToken)->toBe('')
        ->and($response->refreshToken)->toBe('')
        ->and($response->expiresIn)->toBe(300);
});

it('generates correct device fingerprint from metadata', function (): void {
    $user = createLoginUser();
    $request = new LoginRequestDto(
        email: 'user@example.com',
        password: 'SecureP@ss123',
        userAgent: 'Mozilla/5.0',
        ipAddress: '192.168.1.1',
        acceptLanguage: 'en-US',
        deviceName: 'Test Device',
    );

    $expectedFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: 'en-US',
        deviceName: 'Test Device',
    );

    $this->userRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once();

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::any(),
            Mockery::any(),
            Mockery::type(SessionId::class),
            $expectedFingerprint->toString(),
        )
        ->andReturn(JwtToken::fromString('access-token'));

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->once()
        ->andReturn('raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('save')
        ->once();

    $this->eventBus->shouldReceive('dispatch')
        ->once();

    $this->useCase->execute($request);
});
