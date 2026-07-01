<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Comunicaciones\Application\DTOs\CreateSurveyDto;
use Urbania\Comunicaciones\Application\DTOs\CreateSurveyResponseDto;
use Urbania\Comunicaciones\Application\UseCases\Surveys\CreateSurveyResponseUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\CreateSurveyUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\GetSurveyResultsUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\ListSurveysUseCase;
use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\CreateSurveyRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\CreateSurveyResponseRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Requests\ListSurveysRequest;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\SurveyListResource;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\SurveyResource;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\SurveyResponseResource;
use Urbania\Comunicaciones\Infrastructure\Http\Resources\SurveyResultsResource;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class SurveyController extends Controller
{
    public function index(ListSurveysRequest $request, ListSurveysUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $page = isset($validated['page']) && is_numeric($validated['page']) ? (int) $validated['page'] : 1;

        $result = $useCase->execute(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            filters: [
                'activa' => $validated['activa'] ?? null,
            ],
            page: $page,
            perPage: 20,
        );

        $resource = new SurveyListResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function store(CreateSurveyRequest $request, CreateSurveyUseCase $useCase): JsonResponse
    {
        /** @var string $condominiumIdRaw */
        $condominiumIdRaw = $request->attributes->get('org_id');
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        /** @var string $pregunta */
        $pregunta = $validated['pregunta'];
        /** @var string $tipo */
        $tipo = $validated['tipo'];

        $opciones = [];
        if (isset($validated['opciones']) && is_array($validated['opciones'])) {
            /** @var array<string> $opciones */
            $opciones = $validated['opciones'];
        }

        $cierraEl = isset($validated['cierra_el']) && is_string($validated['cierra_el'])
            ? new \DateTimeImmutable($validated['cierra_el'])
            : null;

        $dto = new CreateSurveyDto(
            condominiumId: Uuid::fromString($condominiumIdRaw),
            pregunta: $pregunta,
            tipo: SurveyType::fromString($tipo),
            cierraEl: $cierraEl,
            opciones: $opciones,
        );

        $result = $useCase->execute($dto);
        $resource = new SurveyResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function results(string $id, GetSurveyResultsUseCase $useCase, Request $request): JsonResponse
    {
        $result = $useCase->execute(Uuid::fromString($id));
        $resource = new SurveyResultsResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ]);
    }

    public function respond(string $id, CreateSurveyResponseRequest $request, CreateSurveyResponseUseCase $useCase): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        /** @var string $userIdRaw */
        $userIdRaw = $request->attributes->get('auth_user_id');

        $contact = Contact::where('user_id', $userIdRaw)->first();

        if ($contact === null) {
            return response()->json([
                'error' => [
                    'code' => 'CONTACT_NOT_FOUND',
                    'message' => 'Usuario sin contacto asociado',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 422);
        }

        /** @var string $optionIdRaw */
        $optionIdRaw = $validated['option_id'];

        $dto = new CreateSurveyResponseDto(
            surveyId: Uuid::fromString($id),
            contactId: Uuid::fromString($contact->id),
            optionId: Uuid::fromString($optionIdRaw),
        );

        $result = $useCase->execute(
            surveyId: Uuid::fromString($id),
            dto: $dto,
        );
        $resource = new SurveyResponseResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }
}
