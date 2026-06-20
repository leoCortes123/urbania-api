<?php

declare(strict_types=1);

use Urbania\Auth\Application\UseCases\MfaSetupUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\MfaAlreadyEnabledException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createMfaSetupUser(): UserEntity
{
    return UserEntity::create(
        Email::fromString('mfa@example.com'),
        'MFA User',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
}

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->useCase = new MfaSetupUseCase($this->userRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('sets up MFA for a valid user', function (): void {
    $user = createMfaSetupUser();

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->with(Mockery::on(fn (Uuid $id): bool => $id->toString() === $user->id()->toString()))
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::on(fn (UserEntity $u): bool => $u->mfaSecret() !== null
            && count($u->mfaBackupCodes()) === 10));

    $response = $this->useCase->execute($user->id()->toString());

    expect($response->secret)->toBeString()
        ->and($response->qrCodeUrl)->toContain('otpauth://')
        ->and($response->backupCodes)->toHaveCount(10)
        ->and($response->backupCodes[0])->toMatch('/^\d{8}$/');
});

it('throws UserNotFoundException when user does not exist', function (): void {
    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute(Uuid::v7()->toString());
})->throws(UserNotFoundException::class);

it('throws UserNotFoundException when user is soft deleted', function (): void {
    $user = createMfaSetupUser();
    $user->softDelete();

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($user->id()->toString());
})->throws(UserNotFoundException::class);

it('throws MfaAlreadyEnabledException when MFA is already enabled', function (): void {
    $user = createMfaSetupUser();
    $user->enableMfa('secret', []);

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->useCase->execute($user->id()->toString());
})->throws(MfaAlreadyEnabledException::class);

it('generates backup codes that verify against stored hashes', function (): void {
    $user = createMfaSetupUser();

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once();

    $response = $this->useCase->execute($user->id()->toString());

    foreach ($response->backupCodes as $code) {
        expect($user->validateBackupCode($code))->toBeTrue();
    }
});
