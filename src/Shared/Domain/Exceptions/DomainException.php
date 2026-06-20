<?php

declare(strict_types=1);

namespace Urbania\Shared\Domain\Exceptions;

abstract class DomainException extends \DomainException
{
    public function __construct(
        public readonly string $errorCode,
        string $message = '',
        public readonly int $httpStatusCode = 500,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
