<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\UpdateProfileRequestDto;
use Urbania\Auth\Application\Services\AvatarStorageServiceInterface;
use Urbania\Auth\Application\UseCases\UpdateProfileUseCase;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->avatarStorage = Mockery::mock(AvatarStorageServiceInterface::class);

    $this->useCase = new UpdateProfileUseCase(
        $this->userRepository,
        $this->avatarStorage,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('updates profile with name, phone and avatar', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );

    $request = new UpdateProfileRequestDto(
        name: 'Jane Doe',
        phone: '3001234567',
        avatar: base64_encode('fake-image-data'),
    );

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->avatarStorage->shouldReceive('store')
        ->once()
        ->with($request->avatar)
        ->andReturn('https://urbania.example.com/storage/avatars/123.jpg');

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with($user);

    $response = $this->useCase->execute($request, $user->id()->toString());

    expect($response->name)->toBe('Jane Doe')
        ->and($response->phone)->toBe('3001234567')
        ->and($response->avatarUrl)->toBe('https://urbania.example.com/storage/avatars/123.jpg')
        ->and($user->changedFields())->toContain('name', 'phone', 'avatar_url');
});

it('keeps existing values when fields are not provided', function (): void {
    $user = UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
        phone: '3001234567',
    );

    $request = new UpdateProfileRequestDto;

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn($user);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with($user);

    $response = $this->useCase->execute($request, $user->id()->toString());

    expect($response->name)->toBe('John Doe')
        ->and($response->phone)->toBe('3001234567');
});

it('throws UserNotFoundException when user does not exist', function (): void {
    $request = new UpdateProfileRequestDto(name: 'Jane Doe');

    $this->userRepository->shouldReceive('findById')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request, (string) Uuid::v7());
})->throws(UserNotFoundException::class);
