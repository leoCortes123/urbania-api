<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyVerifiedException;
use Urbania\Auth\Domain\Exceptions\EmailVerificationInvalidException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class VerifyEmailUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $token): array
    {
        try {
            $payload = $this->jwtService->decode($token);
        } catch (\Throwable) {
            throw new EmailVerificationInvalidException;
        }

        if (($payload['scope'] ?? '') !== 'email-verification') {
            throw new EmailVerificationInvalidException;
        }

        $userId = $payload['sub'] ?? null;

        if (! is_string($userId) || $userId === '') {
            throw new EmailVerificationInvalidException;
        }

        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if ($user->emailVerifiedAt() !== null) {
            throw new EmailAlreadyVerifiedException;
        }

        $expiration = $payload['exp'] ?? null;

        if (is_int($expiration) && $expiration < time()) {
            throw new EmailVerificationInvalidException('El enlace de verificación ha expirado');
        }

        $user->markEmailAsVerified();
        $this->userRepository->update($user);

        return [
            'success' => true,
            'message' => 'Correo electrónico verificado exitosamente.',
        ];
    }
}
