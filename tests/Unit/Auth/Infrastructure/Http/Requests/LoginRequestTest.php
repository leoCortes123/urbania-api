<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Urbania\Auth\Infrastructure\Http\Requests\LoginRequest;

uses(TestCase::class);

function loginRules(array $data = []): array
{
    return Validator::make($data, (new LoginRequest)->rules())->errors()->messages();
}

it('requires email and password', function (): void {
    $errors = loginRules();

    expect($errors)->toHaveKey('email')
        ->toHaveKey('password');
});

it('requires a valid email format', function (): void {
    $errors = loginRules([
        'email' => 'not-an-email',
        'password' => 'Password123!',
    ]);

    expect($errors)->toHaveKey('email');
});

it('requires a password with at least 8 characters', function (): void {
    $errors = loginRules([
        'email' => 'test@example.com',
        'password' => 'short',
    ]);

    expect($errors)->toHaveKey('password');
});

it('passes with valid credentials', function (): void {
    $errors = loginRules([
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    expect($errors)->toBeEmpty();
});
