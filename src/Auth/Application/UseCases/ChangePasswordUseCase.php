<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\ChangePasswordRequestDto;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\PasswordChanged;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\PasswordReusedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ChangePasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private PasswordHistoryServiceInterface $passwordHistoryService,
        private EventBusInterface $eventBus,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ChangePasswordRequestDto $request, string $userId): array
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if ($request->newPassword !== $request->newPasswordConfirmation) {
            throw InvalidCredentialsException::weakPassword();
        }

        if (! $user->passwordHash()->verify($request->currentPassword)) {
            throw new InvalidCredentialsException;
        }

        $this->ensurePasswordNotReused($user, $request->newPassword);

        if ($request->currentPassword === $request->newPassword) {
            throw new PasswordReusedException('La nueva contraseña no puede ser igual a la actual');
        }

        $newPassword = Password::fromPlainText($request->newPassword);
        $user->changePassword($newPassword);

        $this->userRepository->update($user);
        $this->passwordHistoryService->save($user->email(), $user->passwordHash()->toString());
        $this->refreshTokenRepository->revokeAllByUser($user->id());

        $this->eventBus->dispatch(new PasswordChanged(
            userId: $user->id()->toString(),
            ip: 'unknown',
            timestamp: new \DateTimeImmutable,
        ));

        return [
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente.',
        ];
    }

    private function ensurePasswordNotReused(UserEntity $user, string $newPassword): void
    {
        $recentHashes = $this->passwordHistoryService->getRecent($user->email(), 12);

        foreach ($recentHashes as $storedHash) {
            $password = Password::fromHash($storedHash);

            if ($password->verify($newPassword)) {
                throw new PasswordReusedException('La contraseña ya fue utilizada recientemente');
            }
        }
    }
}
