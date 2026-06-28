<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Directorio\Application\DTOs\CreateOccupantDTO;
use Directorio\Application\DTOs\UpdateOccupantDTO;
use Directorio\Application\UseCases\Occupants\LinkContactToUnitUseCase;
use Directorio\Application\UseCases\Occupants\ListUnitOccupantsUseCase;
use Directorio\Application\UseCases\Occupants\UnlinkOccupantUseCase;
use Directorio\Application\UseCases\Occupants\UpdateOccupantUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyOccupantController extends Controller
{
    public function __construct(
        private readonly ListUnitOccupantsUseCase $listUnitOccupantsUseCase,
        private readonly LinkContactToUnitUseCase $linkContactToUnitUseCase,
        private readonly UpdateOccupantUseCase $updateOccupantUseCase,
        private readonly UnlinkOccupantUseCase $unlinkOccupantUseCase,
    ) {}

    public function index(string $propertyId): JsonResponse
    {
        $occupants = $this->listUnitOccupantsUseCase->execute($propertyId);

        $data = array_map(fn ($o) => [
            'id' => $o->id(),
            'contact_id' => $o->contactId(),
            'occupant_type_id' => $o->occupantTypeId(),
            'is_primary' => $o->isPrimary(),
            'is_active' => $o->isActive(),
            'move_in_date' => $o->moveInDate(),
            'move_out_date' => $o->moveOutDate(),
        ], $occupants);

        return response()->json([
            'data' => array_values($data),
            'meta' => ['trace_id' => request()->header('X-Trace-Id', '')],
        ]);
    }

    public function store(Request $request, string $propertyId): JsonResponse
    {
        $dto = new CreateOccupantDTO(
            contactId: self::stringValue($request->input('contact_id')),
            occupantTypeId: self::stringValue($request->input('occupant_type_id')),
            isPrimary: $request->boolean('is_primary', false),
            moveInDate: self::nullableString($request->input('move_in_date')),
            moveOutDate: self::nullableString($request->input('move_out_date')),
        );

        $occupant = $this->linkContactToUnitUseCase->execute($propertyId, $dto);

        return response()->json([
            'data' => ['id' => $occupant->id()],
            'meta' => ['trace_id' => $request->header('X-Trace-Id', '')],
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $dto = new UpdateOccupantDTO(
            occupantTypeId: self::nullableString($request->input('occupant_type_id')),
            isPrimary: $request->boolean('is_primary'),
            moveInDate: self::nullableString($request->input('move_in_date')),
            moveOutDate: self::nullableString($request->input('move_out_date')),
            isActive: $request->boolean('is_active'),
        );

        $this->updateOccupantUseCase->execute($id, $dto);

        return response()->json(['meta' => ['trace_id' => $request->header('X-Trace-Id', '')]]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->unlinkOccupantUseCase->execute($id);

        return response()->json(null, 204);
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : null);
    }
}
