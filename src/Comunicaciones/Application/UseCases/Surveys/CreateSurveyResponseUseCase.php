<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Surveys;

use Urbania\Comunicaciones\Application\DTOs\CreateSurveyResponseDto;
use Urbania\Comunicaciones\Application\DTOs\SurveyResponseDto;
use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyAlreadyAnsweredException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyClosedException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateSurveyResponseUseCase
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {}

    public function execute(Uuid $surveyId, CreateSurveyResponseDto $dto): SurveyResponseDto
    {
        $survey = $this->surveyRepository->findById($surveyId);

        if ($survey === null) {
            throw new SurveyNotFoundException;
        }

        if ($survey->isClosed()) {
            throw new SurveyClosedException;
        }

        if ($this->surveyRepository->hasContactResponded($surveyId, $dto->contactId)) {
            throw new SurveyAlreadyAnsweredException;
        }

        $response = SurveyResponseEntity::create(
            surveyId: $surveyId,
            contactId: $dto->contactId,
            optionId: $dto->optionId,
        );

        $this->surveyRepository->saveResponse($response);

        return SurveyResponseDto::fromEntity($response);
    }
}
