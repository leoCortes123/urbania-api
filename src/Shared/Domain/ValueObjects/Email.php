<?php

declare(strict_types=1);

namespace Urbania\Shared\Domain\ValueObjects;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

final readonly class Email implements \Stringable
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = mb_strtolower($value);
    }

    public static function fromString(string $email): self
    {
        $email = trim($email);
        $validator = new EmailValidator;
        if (! $validator->isValid($email, new RFCValidation)) {
            throw new \InvalidArgumentException("Invalid email: {$email}");
        }

        return new self($email);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
