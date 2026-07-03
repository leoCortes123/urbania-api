<?php

declare(strict_types=1);

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\Impersonation\StopImpersonationUseCase;
use Urbania\Auth\Domain\Repositories\SecurityEventRepositoryInterface;

beforeEach(function (): void {
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->securityEventRepository = Mockery::mock(SecurityEventRepositoryInterface::class);

    $this->useCase = new StopImpersonationUseCase(
        $this->jwtService,
        $this->securityEventRepository,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('stops impersonation and logs security event', function (): void {
    $payload = [
        'sub' => 'target-user-uuid',
        'imp' => 'admin-user-uuid',
        'imp_session' => 'session-uuid',
        'iat' => (new \DateTimeImmutable('-5 minutes'))->getTimestamp(),
    ];

    $this->jwtService->shouldReceive('revoke')
        ->once()
        ->with('jti-value');

    $this->securityEventRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Urbania\Auth\Domain\Entities\SecurityEvent::class));

    $this->useCase->execute($payload, '192.168.1.1', 'jti-value');
});

it('handles missing claims gracefully', function (): void {
    $payload = [];

    $this->jwtService->shouldReceive('revoke')
        ->once()
        ->with('jti-empty');

    $this->securityEventRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Urbania\Auth\Domain\Entities\SecurityEvent::class));

    $this->useCase->execute($payload, '127.0.0.1', 'jti-empty');
});
