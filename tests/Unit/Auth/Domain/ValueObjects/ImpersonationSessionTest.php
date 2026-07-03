<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Urbania\Auth\Domain\ValueObjects\ImpersonationSession;

final class ImpersonationSessionTest extends TestCase
{
    #[Test]
    public function itGeneratesANewSession(): void
    {
        $session = ImpersonationSession::generate();

        $this->assertNotEmpty($session->toString());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $session->toString(),
        );
    }

    #[Test]
    public function itCreatesFromAValidString(): void
    {
        $uuid = '018f4c80-7a3b-7f2a-9e5b-c1c8b6e5d4f3';
        $session = ImpersonationSession::fromString($uuid);

        $this->assertSame($uuid, $session->toString());
    }

    #[Test]
    public function itCastsToString(): void
    {
        $session = ImpersonationSession::generate();

        $this->assertSame($session->toString(), (string) $session);
    }
}
