<?php

declare(strict_types=1);

use Urbania\Auth\Domain\ValueObjects\UserRole;

it('has admin role', function (): void {
    expect(UserRole::ADMIN->value)->toBe('admin');
});

it('has user role', function (): void {
    expect(UserRole::USER->value)->toBe('user');
});

it('lists all roles', function (): void {
    $cases = UserRole::cases();

    expect($cases)->toHaveCount(2)
        ->and($cases)->toContain(UserRole::ADMIN, UserRole::USER);
});
