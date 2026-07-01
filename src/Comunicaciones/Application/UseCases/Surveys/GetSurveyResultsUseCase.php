<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Surveys;

use Urbania\Comunicaciones\Application\DTOs\SurveyResultsDto;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetSurveyResultsUseCase
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {}

    public function execute(Uuid $surveyId): SurveyResultsDto
    {
        $survey = $this->surveyRepository->findById($surveyId);

        if ($survey === null) {
            throw new SurveyNotFoundException;
        }

        $total = $this->surveyRepository->totalResponsesBySurveyId($surveyId);
        $conteos = $this->surveyRepository->resultsBySurveyId($surveyId);

        $conteosConFlag = array_map(
            fn (array $item) => array_merge($item, ['cerrada' => $survey->isClosed()]),
            $conteos,
        );

        return new SurveyResultsDto(
            surveyId: $surveyId->toString(),
            total: $total,
            conteos: $conteosConFlag,
        );
    }
}
