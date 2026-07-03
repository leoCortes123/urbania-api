<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use Urbania\Auth\Application\DTOs\ActivateAccountRequestDto;
use Urbania\Auth\Application\DTOs\ActivateAccountResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Exceptions\AccountAlreadyActiveException;
use Urbania\Auth\Domain\Exceptions\ActivationCodeExpiredException;
use Urbania\Auth\Domain\Exceptions\InvalidActivationCodeException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Email;

final readonly class ActivateAccountUseCase
{
    private const MAX_FAILED_ATTEMPTS = 3;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
    ) {}

    public function execute(ActivateAccountRequestDto $request): ActivateAccountResponseDto
    {
        $email = Email::fromString($request->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new InvalidActivationCodeException('El email no corresponde a un usuario pendiente de activación');
        }

        if ($user->status() === \Urbania\Auth\Domain\ValueObjects\UserStatus::ACTIVE) {
            throw new AccountAlreadyActiveException;
        }

        if ($user->status() !== \Urbania\Auth\Domain\ValueObjects\UserStatus::PENDING_ACTIVATION) {
            throw new InvalidActivationCodeException('El email no corresponde a un usuario pendiente de activación');
        }

        // Verify activation code
        if ($user->activationCode() === null || $user->activationCode() !== $request->activationCode) {
            $this->recordFailedAttempt($user);
            throw new InvalidActivationCodeException;
        }

        // Verify not expired
        if ($user->isActivationCodeExpired()) {
            throw new ActivationCodeExpiredException;
        }

        // Success: complete activation
        $user->completeActivation();
        $this->userRepository->update($user);

        // Generate limited token for password change (same pattern as force password change)
        $limitedToken = $this->jwtService->generateAccessToken(
            userId: $user->id()->toString(),
            role: $user->role()->value,
            mfaVerified: false,
            sessionId: SessionId::generate(),
            deviceFingerprint: '',
            organizationId: $user->organizationId(),
            scope: 'change-password',
            ttl: 300,
        );

        return new ActivateAccountResponseDto(
            message: 'Cuenta activada exitosamente. Debes establecer tu contraseña.',
            forcePasswordChangeToken: $limitedToken->toString(),
        );
    }

    private function recordFailedAttempt(\Urbania\Auth\Domain\Entities\UserEntity $user): void
    {
        // Track failed activation attempts via the user entity
        // We use the existing failedLoginAttempts counter to track this
        // After 3 failed attempts, invalidate the code
        $reflection = new \ReflectionProperty($user, 'failedLoginAttempts');
        $reflection->setAccessible(true);
        /** @var mixed $rawValue */
        $rawValue = $reflection->getValue($user);
        $currentAttempts = is_int($rawValue) ? $rawValue : 0;
        $newAttempts = $currentAttempts + 1;

        $reflection->setValue($user, $newAttempts);

        // On 3rd failed attempt, clear the activation code
        if ($newAttempts >= self::MAX_FAILED_ATTEMPTS) {
            $user->clearActivationCode();
            $reflection->setValue($user, 0); // reset counter
        }

        $reflection = new \ReflectionProperty($user, 'updatedAt');
        $reflection->setAccessible(true);
        $reflection->setValue($user, new \DateTimeImmutable);

        $this->userRepository->update($user);
    }
}
