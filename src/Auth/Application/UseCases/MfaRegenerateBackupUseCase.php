<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Domain\Exceptions\MfaNotEnabledException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MfaRegenerateBackupUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function execute(string $userId): array
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if (! $user->isMfaEnabled()) {
            throw new MfaNotEnabledException;
        }

        $backupCodes = [];
        $backupHashes = [];

        for ($i = 0; $i < 10; $i++) {
            $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $backupCodes[] = $code;
            $backupHashes[] = password_hash($code, PASSWORD_ARGON2ID);
        }

        $user->setBackupCodes($backupHashes);
        $this->userRepository->update($user);

        return $backupCodes;
    }
}
