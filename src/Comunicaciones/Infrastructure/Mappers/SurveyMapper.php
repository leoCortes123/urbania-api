<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Mappers;

use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyResponse;
use Carbon\Carbon;
use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class SurveyMapper
{
    public function toDomain(Survey $model): SurveyEntity
    {
        return SurveyEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            pregunta: $model->pregunta,
            tipo: SurveyType::fromString($model->tipo),
            cierraEl: $model->cierra_el?->toDateTimeImmutable(),
            activa: $model->activa,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    public function optionToDomain(SurveyOption $model): SurveyOptionEntity
    {
        return SurveyOptionEntity::reconstitute(
            id: Uuid::fromString($model->id),
            surveyId: Uuid::fromString($model->survey_id),
            texto: $model->texto,
            orden: $model->orden,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    public function responseToDomain(SurveyResponse $model): SurveyResponseEntity
    {
        return SurveyResponseEntity::reconstitute(
            id: Uuid::fromString($model->id),
            surveyId: Uuid::fromString($model->survey_id),
            contactId: Uuid::fromString($model->contact_id),
            optionId: Uuid::fromString($model->option_id),
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    private function toDateTimeImmutable(?Carbon $carbon): \DateTimeImmutable
    {
        return $carbon?->toDateTimeImmutable() ?? new \DateTimeImmutable;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(SurveyEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'pregunta' => $entity->pregunta(),
            'tipo' => $entity->tipo()->value,
            'cierra_el' => $entity->cierraEl()?->format('Y-m-d H:i:s'),
            'activa' => $entity->activa(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function optionToPersistence(SurveyOptionEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'survey_id' => $entity->surveyId()->toString(),
            'texto' => $entity->texto(),
            'orden' => $entity->orden(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function responseToPersistence(SurveyResponseEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'survey_id' => $entity->surveyId()->toString(),
            'contact_id' => $entity->contactId()->toString(),
            'option_id' => $entity->optionId()->toString(),
        ];
    }
}
