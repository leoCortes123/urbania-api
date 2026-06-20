<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Urbania\Auth\Infrastructure\Http\Requests\RegisterRequest;

uses(TestCase::class)->in(__DIR__);
uses(LazilyRefreshDatabase::class)->in(__DIR__);

function registerRules(array $data = []): array
{
    return Validator::make($data, (new RegisterRequest)->rules())->errors()->messages();
}

it('requires name, email, password and password confirmation', function (): void {
    $errors = registerRules();

    expect($errors)->toHaveKey('name')
        ->toHaveKey('email')
        ->toHaveKey('password');
});

it('requires password confirmation to match', function (): void {
    $errors = registerRules([
        'name' => 'Juan',
        'email' => 'juan@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Different123!',
    ]);

    expect($errors)->toHaveKey('password');
});

it('does not validate email uniqueness at request level', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $errors = registerRules([
        'name' => 'Juan',
        'email' => 'taken@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    expect($errors)->not->toHaveKey('email');
});

it('validates phone format', function (): void {
    $errors = registerRules([
        'name' => 'Juan',
        'email' => 'juan@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'phone' => 'abc-def',
    ]);

    expect($errors)->toHaveKey('phone');
});

it('passes with valid registration data', function (): void {
    $errors = registerRules([
        'name' => 'Juan Perez',
        'email' => 'juan@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'phone' => '+57 300 123 4567',
        'unit' => 'Apto 101',
    ]);

    expect($errors)->toBeEmpty();
});
