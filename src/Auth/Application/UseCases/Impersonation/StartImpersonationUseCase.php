<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\Impersonation;

use Urbania\Auth\Application\DTOs\StartImpersonationRequestDto;
use Urbania\Auth\Application\DTOs\StartImpersonationResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\SecurityEvent;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\ImpersonationNotAllowedException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\SecurityEventRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\ImpersonationSession;
use Urbania\Auth\Domain\ValueObjects\UserStatus;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class StartImpersonationUseCase
{
    private const int IMPERSONATION_TTL = 900; // 15 minutes

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
        private SecurityEventRepositoryInterface $securityEventRepository,
    ) {}

    public function execute(StartImpersonationRequestDto $request): StartImpersonationResponseDto
    {
        // 1. Validate admin exists and has permission
        $admin = $this->findAndValidateAdmin($request->adminUserId);

        // 2. Validate target user exists and is active
        $targetUser = $this->findAndValidateTargetUser($request->userId);

        // 3. Generate impersonation session UUID
        $impersonationSession = ImpersonationSession::generate();

        // 4. Generate impersonation JWT token
        $token = $this->jwtService->generateImpersonationToken(
            impersonatedUserId: $targetUser->id()->toString(),
            role: $targetUser->role()->value,
            organizationId: $targetUser->organizationId() ?? '',
            adminUserId: $admin->id()->toString(),
            impersonationSession: $impersonationSession,
            reason: $request->reason,
            ttl: self::IMPERSONATION_TTL,
        );

        // 5. Log security event
        $securityEvent = SecurityEvent::create(
            userId: $admin->id()->toString(),
            eventType: 'impersonation_started',
            severity: 'high',
            ipAddress: $request->adminIp,
            userAgent: null,
            details: [
                'impersonated_user_id' => $targetUser->id()->toString(),
                'admin_user_id' => $admin->id()->toString(),
                'reason' => $request->reason,
                'admin_ip' => $request->adminIp,
                'imp_session' => $impersonationSession->toString(),
            ],
        );
        $this->securityEventRepository->save($securityEvent);

        return new StartImpersonationResponseDto(
            token: $token->toString(),
            expiresIn: self::IMPERSONATION_TTL,
            sessionId: $impersonationSession->toString(),
        );
    }

    private function findAndValidateAdmin(string $adminUserId): UserEntity
    {
        $adminId = Uuid::fromString($adminUserId);
        $admin = $this->userRepository->findById($adminId);

        if ($admin === null) {
            throw new UserNotFoundException;
        }

        // The permission admin.impersonate is checked at the controller/infrastructure level
        // via the AuthorizationMiddleware. This use case assumes the caller is authorized.

        return $admin;
    }

    private function findAndValidateTargetUser(string $targetUserId): UserEntity
    {
        $targetId = Uuid::fromString($targetUserId);
        $targetUser = $this->userRepository->findById($targetId);

        if ($targetUser === null) {
            throw new UserNotFoundException;
        }

        if ($targetUser->status() !== UserStatus::ACTIVE) {
            throw new UserNotFoundException;
        }

        return $targetUser;
    }
}
