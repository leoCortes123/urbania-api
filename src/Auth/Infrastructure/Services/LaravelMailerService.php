<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Mail;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Infrastructure\Mail\EmailVerificationMail;
use Urbania\Auth\Infrastructure\Mail\PasswordResetMail;

final readonly class LaravelMailerService implements MailerServiceInterface
{
    public function sendPasswordResetEmail(string $email, string $resetLink): void
    {
        Mail::to($email)->send(new PasswordResetMail($resetLink));
    }

    public function sendVerificationEmail(string $email, string $verificationLink): void
    {
        Mail::to($email)->send(new EmailVerificationMail($verificationLink));
    }
}
