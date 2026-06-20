<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\RegisterRequestDto;
use Urbania\Auth\Application\UseCases\RegisterUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserRegistered;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyExistsException;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new RegisterUseCase(
        $this->userRepository,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('registers a new user successfully', function (): void {
    $request = new RegisterRequestDto(
        name: 'John Doe',
        email: 'new@example.com',
        password: 'SecureP@ss123',
        passwordConfirmation: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('existsByEmail')
        ->once()
        ->andReturn(false);

    $this->userRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(UserEntity::class));

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(UserRegistered::class));

    $response = $this->useCase->execute($request);

    expect($response->name)->toBe('John Doe')
        ->and($response->email)->toBe('new@example.com')
        ->and($response->role)->toBe(UserRole::USER->value)
        ->and($response->status)->toBe('active')
        ->and($response->message)->toBe('Registro exitoso. Bienvenido a Urbania.');
});

it('throws EmailAlreadyExistsException when email is already registered', function (): void {
    $request = new RegisterRequestDto(
        name: 'John Doe',
        email: 'existing@example.com',
        password: 'SecureP@ss123',
        passwordConfirmation: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('existsByEmail')
        ->once()
        ->with(Mockery::on(fn (Email $email): bool => $email->toString() === 'existing@example.com'))
        ->andReturn(true);

    $this->useCase->execute($request);
})->throws(EmailAlreadyExistsException::class);

it('throws InvalidCredentialsException when password confirmation does not match', function (): void {
    $request = new RegisterRequestDto(
        name: 'John Doe',
        email: 'new@example.com',
        password: 'SecureP@ss123',
        passwordConfirmation: 'DifferentP@ss123',
    );

    $this->userRepository->shouldReceive('existsByEmail')
        ->once()
        ->andReturn(false);

    try {
        $this->useCase->execute($request);
    } catch (InvalidCredentialsException $e) {
        expect($e->getMessage())->toBe('Password must be at least 8 characters');

        return;
    }

    $this->fail('Expected InvalidCredentialsException was not thrown');
});

it('dispatches UserRegistered event', function (): void {
    $request = new RegisterRequestDto(
        name: 'John Doe',
        email: 'new@example.com',
        password: 'SecureP@ss123',
        passwordConfirmation: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('existsByEmail')
        ->once()
        ->andReturn(false);

    $this->userRepository->shouldReceive('save')
        ->once();

    $capturedEvent = null;
    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(UserRegistered::class))
        ->andReturnUsing(function (UserRegistered $event) use (&$capturedEvent): void {
            $capturedEvent = $event;
        });

    $this->useCase->execute($request);

    expect($capturedEvent)->toBeInstanceOf(UserRegistered::class)
        ->and($capturedEvent->email)->toBe('new@example.com');
});

it('assigns USER role by default', function (): void {
    $request = new RegisterRequestDto(
        name: 'John Doe',
        email: 'new@example.com',
        password: 'SecureP@ss123',
        passwordConfirmation: 'SecureP@ss123',
    );

    $this->userRepository->shouldReceive('existsByEmail')
        ->once()
        ->andReturn(false);

    $this->userRepository->shouldReceive('save')
        ->once();

    $this->eventBus->shouldReceive('dispatch')
        ->once();

    $response = $this->useCase->execute($request);

    expect($response->role)->toBe(UserRole::USER->value);
});
