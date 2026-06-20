<?php

declare(strict_types=1);

use Urbania\Shared\Domain\ValueObjects\Email;

it('creates an email from a valid string', function (): void {
    $email = Email::fromString('User@Example.COM');

    expect($email->toString())->toBe('user@example.com');
});

it('trims whitespace', function (): void {
    $email = Email::fromString('  user@example.com  ');

    expect($email->toString())->toBe('user@example.com');
});

it('throws when creating from invalid email', function (): void {
    Email::fromString('not-an-email');
})->throws(InvalidArgumentException::class, 'Invalid email:');

it('compares equality correctly', function (): void {
    $a = Email::fromString('user@example.com');
    $b = Email::fromString('USER@EXAMPLE.COM');
    $c = Email::fromString('other@example.com');

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

it('casts to string', function (): void {
    $email = Email::fromString('user@example.com');

    expect((string) $email)->toBe('user@example.com');
});
