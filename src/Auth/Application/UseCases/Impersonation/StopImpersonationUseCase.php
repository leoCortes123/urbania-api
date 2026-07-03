<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\Impersonation;

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\SecurityEvent;
use Urbania\Auth\Domain\Repositories\SecurityEventRepositoryInterface;

final readonly class StopImpersonationUseCase
{
    public function __construct(
        private JwtServiceInterface $jwtService,
        private SecurityEventRepositoryInterface $securityEventRepository,
    ) {}

    /**
     * @param array<string, mixed> $tokenPayload
     */
    public function execute(array $tokenPayload, string $ipAddress, string $jti): void
    {
        $impersonatedUserId = isset($tokenPayload['sub']) && is_string($tokenPayload['sub'])
            ? $tokenPayload['sub']
            : '';
        $adminUserId = isset($tokenPayload['imp']) && is_string($tokenPayload['imp'])
            ? $tokenPayload['imp']
            : '';
        $impSession = isset($tokenPayload['imp_session']) && is_string($tokenPayload['imp_session'])
            ? $tokenPayload['imp_session']
            : '';

        // Calculate duration
        $iat = isset($tokenPayload['iat']) && is_int($tokenPayload['iat'])
            ? $tokenPayload['iat']
            : 0;
        $now = (new \DateTimeImmutable)->getTimestamp();
        $durationSeconds = max(0, $now - $iat);

        // Revoke the access token
        $this->jwtService->revoke($jti);

        // Log security event
        $securityEvent = SecurityEvent::create(
            userId: $adminUserId !== '' ? $adminUserId : null,
            eventType: 'impersonation_ended',
            severity: 'high',
            ipAddress: $ipAddress,
            userAgent: null,
            details: [
                'impersonated_user_id' => $impersonatedUserId,
                'admin_user_id' => $adminUserId,
                'imp_session' => $impSession,
                'duration_seconds' => $durationSeconds,
            ],
        );
        $this->securityEventRepository->save($securityEvent);
    }
}
