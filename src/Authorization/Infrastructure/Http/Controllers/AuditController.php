<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use App\Models\PermissionAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Authorization\Application\UseCases\Audit\ListAuditLogUseCase;
use Urbania\Authorization\Infrastructure\Http\Requests\ListAuditLogRequest;
use Urbania\Authorization\Infrastructure\Http\Resources\AuditLogResource;

final class AuditController extends Controller
{
    use HandlesAuthorizationRequest;

    public function index(ListAuditLogRequest $request, ListAuditLogUseCase $useCase): JsonResponse
    {
        /** @var array{from?: string|null, to?: string|null, actor?: string|null, page?: int, per_page?: int} $validated */
        $validated = $request->validated();

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        $filters = [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'actor' => $validated['actor'] ?? null,
        ];

        $result = $useCase->execute(
            $this->organizationId($request),
            $filters,
            $page,
            $perPage,
        );

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->map(
            static fn (PermissionAuditLog $log): array => (new AuditLogResource($log))->resolve($request)
        )->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
                'last_page' => $result->lastPage(),
                'trace_id' => $request->attributes->get('trace_id'),
            ],
        ]);
    }
}
