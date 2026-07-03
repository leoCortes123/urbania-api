<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Urbania\Auth\Domain\Exceptions\ImpersonationNotAllowedException;
use Urbania\Shared\Domain\Exceptions\DomainException;

final class ImpersonationExceptionsTest extends TestCase
{
    #[Test]
    public function itExtendsDomainException(): void
    {
        $exception = new ImpersonationNotAllowedException;

        $this->assertInstanceOf(DomainException::class, $exception);
    }

    #[Test]
    public function itHasExpectedErrorCodeAndHttpStatus(): void
    {
        $exception = new ImpersonationNotAllowedException;

        $this->assertSame('IMPERSONATION_NOT_ALLOWED', $exception->errorCode);
        $this->assertSame(403, $exception->httpStatusCode);
    }

    #[Test]
    public function itPreservesCustomMessages(): void
    {
        $exception = new ImpersonationNotAllowedException('Mensaje personalizado');

        $this->assertSame('Mensaje personalizado', $exception->getMessage());
    }
}
