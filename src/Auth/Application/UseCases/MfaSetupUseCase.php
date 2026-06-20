<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use PragmaRX\Google2FA\Google2FA;
use Urbania\Auth\Application\DTOs\MfaSetupResponseDto;
use Urbania\Auth\Domain\Exceptions\MfaAlreadyEnabledException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MfaSetupUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $userId): MfaSetupResponseDto
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if ($user->isMfaEnabled()) {
            throw new MfaAlreadyEnabledException;
        }

        $google2fa = new Google2FA;
        $google2fa->setAlgorithm('sha256');

        $secret = $google2fa->generateSecretKey(32);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'Urbania',
            $user->email()->toString(),
            $secret,
        );

        $backupCodes = [];
        $backupHashes = [];

        for ($i = 0; $i < 10; $i++) {
            $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $backupCodes[] = $code;
            $backupHashes[] = password_hash($code, PASSWORD_ARGON2ID);
        }

        $user->setMfaSecret($secret);
        $user->setBackupCodes($backupHashes);
        $this->userRepository->update($user);

        return new MfaSetupResponseDto(
            secret: $secret,
            qrCodeUrl: $qrCodeUrl,
            backupCodes: $backupCodes,
        );
    }
}
