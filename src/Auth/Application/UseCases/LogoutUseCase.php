<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\LogoutRequestDto;
use Urbania\Auth\Domain\Events\UserLoggedOut;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Shared\Application\Bus\EventBusInterface;

final readonly class LogoutUseCase
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(LogoutRequestDto $request): void
    {
        $tokenHash = hash('sha256', $request->refreshToken);
        $tokenEntity = $this->refreshTokenRepository->findByHash($tokenHash);

        if ($tokenEntity === null) {
            throw new TokenInvalidException;
        }

        $tokenEntity->revoke('logout');
        $this->refreshTokenRepository->revoke($tokenHash, 'logout');

        $this->eventBus->dispatch(new UserLoggedOut(
            userId: $tokenEntity->userId()->toString(),
            sessionId: $tokenEntity->sessionId()->toString(),
            timestamp: new \DateTimeImmutable,
        ));
    }
}
