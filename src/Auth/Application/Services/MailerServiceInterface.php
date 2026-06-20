<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\Services;

interface MailerServiceInterface
{
    public function sendPasswordResetEmail(string $email, string $resetLink): void;

    public function sendVerificationEmail(string $email, string $verificationLink): void;
}
