<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyVerifiedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ResendVerificationUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
        private MailerServiceInterface $mailer,
        private string $frontendUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $userId): array
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if ($user->emailVerifiedAt() !== null) {
            throw new EmailAlreadyVerifiedException;
        }

        $token = $this->jwtService->generateAccessToken(
            userId: $user->id()->toString(),
            role: $user->role()->value,
            mfaVerified: false,
            sessionId: SessionId::generate(),
            deviceFingerprint: '',
            organizationId: $user->organizationId(),
            scope: 'email-verification',
            ttl: 3600,
        );

        $verificationLink = $this->frontendUrl.'/verify-email?token='.$token->toString();
        $this->mailer->sendVerificationEmail($user->email()->toString(), $verificationLink);

        return [
            'success' => true,
            'message' => 'Se ha enviado un nuevo enlace de verificación.',
        ];
    }
}
