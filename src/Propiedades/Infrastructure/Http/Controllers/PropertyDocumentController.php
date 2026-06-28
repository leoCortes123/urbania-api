<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Propiedades\Application\DTOs\UploadPropertyDocumentRequestDto;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\DeletePropertyDocumentUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\ListPropertyDocumentsUseCase;
use Urbania\Propiedades\Application\UseCases\PropertyDocuments\UploadPropertyDocumentUseCase;
use Urbania\Propiedades\Infrastructure\Http\Requests\ListPropertyDocumentsRequest;
use Urbania\Propiedades\Infrastructure\Http\Requests\UploadPropertyDocumentRequest;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyDocumentCollection;
use Urbania\Propiedades\Infrastructure\Http\Resources\PropertyDocumentResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyDocumentController extends Controller
{
    public function index(ListPropertyDocumentsRequest $request, string $propertyId, ListPropertyDocumentsUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) $validated['per_page'] : 20;

        $result = $useCase->execute($propertyId, $page, $perPage);
        $collection = new PropertyDocumentCollection($result);

        /** @var array{data: mixed, meta: array<string, mixed>} $resolved */
        $resolved = $collection->resolve($request);

        return response()->json([
            'data' => $resolved['data'],
            'meta' => array_merge(
                $resolved['meta'],
                ['trace_id' => $request->attributes->get('trace_id')],
            ),
        ]);
    }

    public function store(UploadPropertyDocumentRequest $request, string $propertyId, UploadPropertyDocumentUseCase $useCase): JsonResponse
    {
        /** @var string $propertyDocumentTypeId */
        $propertyDocumentTypeId = $request->validated('property_document_type_id');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $notes */
        $notes = $request->validated('notes');
        /** @var string $uploadedByUserId */
        $uploadedByUserId = $request->attributes->get('auth_user_id');

        $dto = new UploadPropertyDocumentRequestDto(
            propertyId: Uuid::fromString($propertyId),
            propertyDocumentTypeId: Uuid::fromString($propertyDocumentTypeId),
            name: $name,
            filePath: '',
            uploadedByUserId: Uuid::fromString($uploadedByUserId),
            notes: $notes,
        );

        $file = $request->file('file');
        $result = $useCase->execute($dto, $file);
        $resource = new PropertyDocumentResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function destroy(string $propertyId, string $docId, DeletePropertyDocumentUseCase $useCase): JsonResponse
    {
        $useCase->execute($propertyId, $docId);

        return response()->json(null, 204);
    }
}
