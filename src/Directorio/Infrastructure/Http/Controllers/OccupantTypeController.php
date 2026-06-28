<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Directorio\Application\UseCases\Catalogs\ListOccupantTypesUseCase;
use Directorio\Domain\Entities\OccupantType;
use Illuminate\Http\JsonResponse;

class OccupantTypeController extends Controller
{
    public function __construct(
        private readonly ListOccupantTypesUseCase $listOccupantTypesUseCase,
    ) {}

    public function index(): JsonResponse
    {
        $types = $this->listOccupantTypesUseCase->execute();

        $data = array_map(
            /** @param OccupantType $t */
            fn ($t) => [
                'id' => $t->id(),
                'code' => $t->code()->value(),
                'name' => $t->name(),
                'sort_order' => $t->sortOrder(),
            ],
            $types
        );

        return response()->json([
            'data' => array_values($data),
            'meta' => ['trace_id' => request()->header('X-Trace-Id', '')],
        ]);
    }
}
