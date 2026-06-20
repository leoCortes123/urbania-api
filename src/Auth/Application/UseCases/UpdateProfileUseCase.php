<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\UpdateProfileRequestDto;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\Services\AvatarStorageServiceInterface;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdateProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AvatarStorageServiceInterface $avatarStorage,
    ) {}

    public function execute(UpdateProfileRequestDto $request, string $userId): UserResponseDto
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        $name = $request->name ?? $user->name();
        $phone = $request->phone;
        $avatarUrl = $request->avatar !== null
            ? $this->avatarStorage->store($request->avatar)
            : $user->avatarUrl();

        $user->updateProfile($name, $phone, $avatarUrl);
        $this->userRepository->update($user);

        return new UserResponseDto(
            id: $user->id()->toString(),
            name: $user->name(),
            email: $user->email()->toString(),
            phone: $user->phone(),
            unit: $user->unit(),
            role: $user->role()->value,
            status: $user->status()->value,
            avatarUrl: $user->avatarUrl(),
            createdAt: $user->createdAt()->format('c'),
        );
    }
}
