<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Surveys;

use Urbania\Comunicaciones\Application\DTOs\CreateSurveyDto;
use Urbania\Comunicaciones\Application\DTOs\SurveyDto;
use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;

final readonly class CreateSurveyUseCase
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {}

    public function execute(CreateSurveyDto $dto): SurveyDto
    {
        $survey = SurveyEntity::create(
            condominiumId: $dto->condominiumId,
            pregunta: $dto->pregunta,
            tipo: $dto->tipo,
            cierraEl: $dto->cierraEl,
        );

        $this->surveyRepository->save($survey);

        $opciones = [];
        foreach ($dto->opciones as $index => $texto) {
            $option = SurveyOptionEntity::create(
                surveyId: $survey->id(),
                texto: $texto,
                orden: $index,
            );
            $this->surveyRepository->saveOption($option);
            $opciones[] = $texto;
        }

        return SurveyDto::fromEntity($survey, $opciones);
    }
}
