<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class EmailVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $verificationLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verificación de correo electrónico - Urbania',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "<p>Haz clic en el siguiente enlace para verificar tu correo electrónico:</p><p><a href=\"{$this->verificationLink}\">{$this->verificationLink}</a></p>",
        );
    }
}
