<?php

declare(strict_types=1);

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\MfaVerifyBackupUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserLoggedIn;
use Urbania\Auth\Domain\Exceptions\MfaInvalidCodeException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createMfaBackupUser(array $backupCodes = ['11111111']): UserEntity
{
    $user = UserEntity::create(
        Email::fromString('mfa-backup@example.com'),
        'MFA Backup User',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $backupHashes = array_map(
        fn (string $code): string => password_hash($code, PASSWORD_ARGON2ID),
        $backupCodes,
    );

    $user->enableMfa('JBSWY3DPEHPK3PXP', $backupHashes);

    return $user;
}

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new MfaVerifyBackupUseCase(
        $this->userRepository,
        $this->jwtService,
        $this->refreshTokenRepository,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('issues tokens when backup code is valid', function (): void {
    $backupCode = '11111111';
    $user = createMfaBackupUser([$backupCode, '22222222']);
    $mfaToken = 'valid-mfa-token';

    $this->jwtService->shouldReceive('validate')
        ->once()
        ->with($mfaToken)
        ->andReturn(true);

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->with($mfaToken)
        ->andReturn(['sub' => $user->id()->toString()]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->with(Mockery::on(fn (Uuid $id): bool => $id->toString() === $user->id()->toString()))
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->twice();

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

    $response = $this->useCase->execute($mfaToken, $backupCode, 'Mozilla/5.0', '192.168.1.1');

    expect($response->accessToken)->toBe('access-token')
        ->and($response->refreshToken)->toBe('raw-refresh-token')
        ->and($response->tokenType)->toBe('bearer')
        ->and($response->expiresIn)->toBe(900)
        ->and($response->user->email)->toBe('mfa-backup@example.com');
});

it('throws MfaInvalidCodeException when backup code is invalid', function (): void {
    $user = createMfaBackupUser(['11111111']);
    $mfaToken = 'valid-mfa-token';

    $this->jwtService->shouldReceive('validate')
        ->once()
        ->andReturn(true);

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn(['sub' => $user->id()->toString()]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($mfaToken, '00000000', 'Mozilla/5.0', '192.168.1.1');
})->throws(MfaInvalidCodeException::class);

it('throws TokenInvalidException when MFA token is invalid', function (): void {
    $this->jwtService->shouldReceive('validate')
        ->once()
        ->with('invalid-token')
        ->andReturn(false);

    $this->useCase->execute('invalid-token', '11111111', 'Mozilla/5.0', '192.168.1.1');
})->throws(TokenInvalidException::class);

it('throws UserNotFoundException when user does not exist', function (): void {
    $mfaToken = 'valid-mfa-token';
    $userId = Uuid::v7()->toString();

    $this->jwtService->shouldReceive('validate')
        ->once()
        ->andReturn(true);

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn(['sub' => $userId]);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($mfaToken, '11111111', 'Mozilla/5.0', '192.168.1.1');
})->throws(UserNotFoundException::class);

it('throws TokenInvalidException when MFA token payload lacks sub', function (): void {
    $mfaToken = 'valid-mfa-token';

    $this->jwtService->shouldReceive('validate')
        ->once()
        ->andReturn(true);

    $this->jwtService->shouldReceive('decode')
        ->once()
        ->andReturn([]);

    $this->useCase->execute($mfaToken, '11111111', 'Mozilla/5.0', '192.168.1.1');
})->throws(TokenInvalidException::class);
