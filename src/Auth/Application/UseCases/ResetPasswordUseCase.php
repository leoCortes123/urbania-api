<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\ResetPasswordRequestDto;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Auth\Domain\Events\PasswordChanged;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\InvalidResetTokenException;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

final readonly class ResetPasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private PasswordResetTokenRepositoryInterface $resetTokenRepository,
        private PasswordHistoryServiceInterface $passwordHistoryService,
        private EventBusInterface $eventBus,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ResetPasswordRequestDto $request): array
    {
        if ($request->password !== $request->passwordConfirmation) {
            throw InvalidCredentialsException::weakPassword();
        }

        $email = Email::fromString($request->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new InvalidResetTokenException;
        }

        $tokenRecord = $this->resetTokenRepository->findByEmail($email->toString());

        if ($tokenRecord === null) {
            throw new InvalidResetTokenException;
        }

        $tokenHash = hash('sha256', $request->token);

        if (! hash_equals($tokenRecord['token'], $tokenHash)) {
            throw new InvalidResetTokenException;
        }

        $expiresAt = $tokenRecord['created_at']->modify('+60 minutes');

        if ($expiresAt < new \DateTimeImmutable) {
            throw new InvalidResetTokenException('Reset token has expired');
        }

        $newPassword = Password::fromPlainText($request->password);
        $user->changePassword($newPassword);

        $this->userRepository->update($user);
        $this->passwordHistoryService->save($email, $user->passwordHash()->toString());
        $this->refreshTokenRepository->revokeAllByUser($user->id());
        $this->resetTokenRepository->delete($email->toString());

        $this->eventBus->dispatch(new PasswordChanged(
            userId: $user->id()->toString(),
            ip: 'unknown',
            timestamp: new \DateTimeImmutable,
        ));

        return [
            'success' => true,
            'message' => 'Contraseña restablecida exitosamente.',
        ];
    }
}
