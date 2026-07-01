<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Authorization\Application\UseCases\Permissions\ListPermissionsUseCase;
use Urbania\Authorization\Infrastructure\Http\Requests\ListPermissionsRequest;
use Urbania\Authorization\Infrastructure\Http\Resources\PermissionGroupCollection;

final class PermissionController extends Controller
{
    public function index(ListPermissionsRequest $request, ListPermissionsUseCase $useCase): JsonResponse
    {
        $groups = $useCase->execute();
        $collection = new PermissionGroupCollection($groups);

        /** @var array{data: mixed} $resolved */
        $resolved = $collection->resolve($request);

        return response()->json([
            'data' => $resolved['data'],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }
}
