<?php

declare(strict_types=1);

namespace Urbania\Shared\Infrastructure\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Shared\Application\Services\TokenDecoderInterface;

final readonly class TenantMiddleware
{
    public function __construct(
        private TokenDecoderInterface $tokenDecoder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isPublicRoute($request)) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            return $this->tenantRequiredResponse($request);
        }

        if (! $this->tokenDecoder->validate($token)) {
            return $this->tenantRequiredResponse($request);
        }

        $payload = $this->tokenDecoder->decode($token);
        $orgId = $payload['org_id'] ?? null;

        if (! is_string($orgId) || $orgId === '') {
            return $this->tenantRequiredResponse($request);
        }

        $organization = Organization::find($orgId);

        if ($organization === null) {
            return $this->tenantInvalidResponse($request);
        }

        if ($organization->status === 'suspendido') {
            return $this->tenantSuspendedResponse($request);
        }

        $request->attributes->set('org_id', $orgId);

        $quotedOrgId = DB::connection()->getPdo()->quote($orgId);
        DB::statement("SET LOCAL app.org_id = {$quotedOrgId}");

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }

    private function isPublicRoute(Request $request): bool
    {
        $publicPaths = [
            'api/v1/auth/login',
            'api/v1/auth/register',
            'api/v1/auth/refresh',
            'api/v1/auth/forgot-password',
            'api/v1/auth/reset-password',
            'api/v1/auth/verify-email',
            'api/v1/auth/mfa/verify',
            'api/v1/auth/mfa/verify-backup',
            'api/v1/health',
        ];

        foreach ($publicPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    private function tenantRequiredResponse(Request $request): Response
    {
        return response()->json([
            'error' => [
                'code' => 'TENANT_REQUIRED',
                'message' => 'Organization context is required.',
                'trace_id' => $this->traceId($request),
            ],
        ], 401);
    }

    private function tenantInvalidResponse(Request $request): Response
    {
        return response()->json([
            'error' => [
                'code' => 'TENANT_INVALID',
                'message' => 'The organization context is invalid.',
                'trace_id' => $this->traceId($request),
            ],
        ], 401);
    }

    private function tenantSuspendedResponse(Request $request): Response
    {
        return response()->json([
            'error' => [
                'code' => 'TENANT_SUSPENDED',
                'message' => 'The organization is suspended.',
                'trace_id' => $this->traceId($request),
            ],
        ], 403);
    }

    private function traceId(Request $request): string
    {
        $traceId = $request->attributes->get('trace_id');

        return is_string($traceId) && $traceId !== '' ? $traceId : (string) Uuid::uuid7();
    }
}
