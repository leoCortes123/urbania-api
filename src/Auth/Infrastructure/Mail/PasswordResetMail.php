<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $resetLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperación de contraseña - Urbania',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p><p><a href=\"{$this->resetLink}\">{$this->resetLink}</a></p>",
        );
    }
}
