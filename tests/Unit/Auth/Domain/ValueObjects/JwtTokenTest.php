<?php

declare(strict_types=1);

use Urbania\Auth\Domain\ValueObjects\JwtToken;

it('creates a token from a valid string', function (): void {
    $token = JwtToken::fromString('eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature');

    expect($token->toString())->toBe('eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature');
});

it('strips bearer prefix case-insensitively', function (): void {
    $token = JwtToken::fromString('Bearer eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature');

    expect($token->toString())->toBe('eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature');
});

it('throws for empty token', function (): void {
    JwtToken::fromString('   ');
})->throws(InvalidArgumentException::class, 'JWT token cannot be empty');

it('casts to string', function (): void {
    $token = JwtToken::fromString('header.payload.signature');

    expect((string) $token)->toBe('header.payload.signature');
});
