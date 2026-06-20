<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\ForgotPasswordRequestDto;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

final readonly class ForgotPasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailerServiceInterface $mailer,
        private PasswordResetTokenRepositoryInterface $resetTokenRepository,
        private string $frontendUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ForgotPasswordRequestDto $request): array
    {
        $email = Email::fromString($request->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            return [
                'success' => true,
                'message' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.',
            ];
        }

        $token = bin2hex(random_bytes(64));
        $tokenHash = hash('sha256', $token);

        $this->resetTokenRepository->save($email->toString(), $tokenHash);

        $resetLink = $this->buildResetLink($email->toString(), $token);
        $this->mailer->sendPasswordResetEmail($email->toString(), $resetLink);

        return [
            'success' => true,
            'message' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.',
        ];
    }

    private function buildResetLink(string $email, string $token): string
    {
        return $this->frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($email);
    }
}
