<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetCurrentUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
    ) {}

    public function execute(string $bearerToken): UserResponseDto
    {
        $payload = $this->jwtService->decode($bearerToken);

        if (! isset($payload['sub']) || ! is_string($payload['sub'])) {
            throw new UserNotFoundException;
        }

        $userId = Uuid::fromString($payload['sub']);
        $user = $this->userRepository->findById($userId);

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        return new UserResponseDto(
            id: $user->id()->toString(),
            name: $user->name(),
            email: $user->email()->toString(),
            phone: null,
            unit: null,
            role: $user->role()->value,
            status: $user->status()->value,
            avatarUrl: null,
            createdAt: $user->createdAt()->format('c'),
        );
    }
}
