<?php

declare(strict_types=1);

use Urbania\Auth\Domain\ValueObjects\UserStatus;

it('has active status', function (): void {
    expect(UserStatus::ACTIVE->value)->toBe('active');
});

it('has pending activation status', function (): void {
    expect(UserStatus::PENDING_ACTIVATION->value)->toBe('pending_activation');
});

it('has suspended status', function (): void {
    expect(UserStatus::SUSPENDED->value)->toBe('suspended');
});

it('has inactive status', function (): void {
    expect(UserStatus::INACTIVE->value)->toBe('inactive');
});

it('lists all statuses', function (): void {
    $cases = UserStatus::cases();

    expect($cases)->toHaveCount(4)
        ->and($cases)->toContain(
            UserStatus::ACTIVE,
            UserStatus::PENDING_ACTIVATION,
            UserStatus::SUSPENDED,
            UserStatus::INACTIVE,
        );
});
