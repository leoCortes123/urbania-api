<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Application\UseCases;

use DateTimeImmutable;
use Mockery;
use Tests\TestCase;
use Urbania\Comunicaciones\Application\DTOs\CreateSurveyDto;
use Urbania\Comunicaciones\Application\DTOs\CreateSurveyResponseDto;
use Urbania\Comunicaciones\Application\UseCases\Surveys\CreateSurveyResponseUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\CreateSurveyUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\GetSurveyResultsUseCase;
use Urbania\Comunicaciones\Application\UseCases\Surveys\ListSurveysUseCase;
use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyAlreadyAnsweredException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyClosedException;
use Urbania\Comunicaciones\Domain\Exceptions\SurveyNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Shared\Domain\ValueObjects\Uuid;

uses(TestCase::class);

function createSurveyEntity(array $overrides = []): SurveyEntity
{
    return SurveyEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        pregunta: $overrides['pregunta'] ?? '¿Pregunta de encuesta?',
        tipo: $overrides['tipo'] ?? SurveyType::SIMPLE,
        cierraEl: $overrides['cierraEl'] ?? null,
        activa: $overrides['activa'] ?? true,
    );
}

beforeEach(function (): void {
    $this->surveyRepository = Mockery::mock(SurveyRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreateSurveyUseCase', function (): void {
    it('creates a survey with options', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new CreateSurveyUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(SurveyEntity::class));

        $this->surveyRepository->shouldReceive('saveOption')
            ->times(3)
            ->with(Mockery::type(SurveyOptionEntity::class));

        $dto = new CreateSurveyDto(
            condominiumId: $condominiumId,
            pregunta: '¿Cuál es tu color favorito?',
            tipo: SurveyType::SIMPLE,
            cierraEl: null,
            opciones: ['Rojo', 'Verde', 'Azul'],
        );

        $result = $useCase->execute($dto);

        expect($result->pregunta)->toBe('¿Cuál es tu color favorito?')
            ->and($result->tipo)->toBe(SurveyType::SIMPLE->value)
            ->and($result->cierraEl)->toBeNull()
            ->and($result->activa)->toBeTrue()
            ->and($result->opciones)->toBe(['Rojo', 'Verde', 'Azul']);
    });

    it('creates options with correct order', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new CreateSurveyUseCase($this->surveyRepository);

        $capturedOptions = [];

        $this->surveyRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(SurveyEntity::class));

        $this->surveyRepository->shouldReceive('saveOption')
            ->times(2)
            ->with(Mockery::on(function (SurveyOptionEntity $option) use (&$capturedOptions) {
                $capturedOptions[] = $option;

                return true;
            }));

        $dto = new CreateSurveyDto(
            condominiumId: $condominiumId,
            pregunta: '¿Sí o no?',
            tipo: SurveyType::SIMPLE,
            cierraEl: null,
            opciones: ['Sí', 'No'],
        );

        $result = $useCase->execute($dto);

        expect($result->opciones)->toBe(['Sí', 'No'])
            ->and(count($capturedOptions))->toBe(2)
            ->and($capturedOptions[0]->orden())->toBe(0)
            ->and($capturedOptions[1]->orden())->toBe(1)
            ->and($capturedOptions[0]->texto())->toBe('Sí')
            ->and($capturedOptions[1]->texto())->toBe('No');
    });
});

describe('CreateSurveyResponseUseCase', function (): void {
    it('creates a survey response', function (): void {
        $surveyId = Uuid::v7();
        $survey = createSurveyEntity();
        $contactId = Uuid::v7();
        $optionId = Uuid::v7();

        $useCase = new CreateSurveyResponseUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($survey);

        $this->surveyRepository->shouldReceive('hasContactResponded')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(Uuid::class))
            ->andReturn(false);

        $this->surveyRepository->shouldReceive('saveResponse')
            ->once()
            ->with(Mockery::type(SurveyResponseEntity::class));

        $dto = new CreateSurveyResponseDto(
            surveyId: $surveyId,
            contactId: $contactId,
            optionId: $optionId,
        );

        $result = $useCase->execute($surveyId, $dto);

        expect($result->surveyId)->toBe($surveyId->toString())
            ->and($result->contactId)->toBe($contactId->toString())
            ->and($result->optionId)->toBe($optionId->toString());
    });

    it('throws SurveyNotFoundException when survey does not exist', function (): void {
        $surveyId = Uuid::v7();
        $useCase = new CreateSurveyResponseUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $dto = new CreateSurveyResponseDto(
            surveyId: $surveyId,
            contactId: Uuid::v7(),
            optionId: Uuid::v7(),
        );

        $useCase->execute($surveyId, $dto);
    })->throws(SurveyNotFoundException::class);

    it('throws SurveyClosedException when survey is inactive', function (): void {
        $surveyId = Uuid::v7();
        $survey = createSurveyEntity(['activa' => false]);
        $useCase = new CreateSurveyResponseUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($survey);

        $dto = new CreateSurveyResponseDto(
            surveyId: $surveyId,
            contactId: Uuid::v7(),
            optionId: Uuid::v7(),
        );

        $useCase->execute($surveyId, $dto);
    })->throws(SurveyClosedException::class);

    it('throws SurveyClosedException when survey has a past closing date', function (): void {
        $surveyId = Uuid::v7();
        $survey = createSurveyEntity([
            'cierraEl' => new DateTimeImmutable('-1 day'),
            'activa' => true,
        ]);
        $useCase = new CreateSurveyResponseUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($survey);

        $dto = new CreateSurveyResponseDto(
            surveyId: $surveyId,
            contactId: Uuid::v7(),
            optionId: Uuid::v7(),
        );

        $useCase->execute($surveyId, $dto);
    })->throws(SurveyClosedException::class);

    it('throws SurveyAlreadyAnsweredException when contact already responded', function (): void {
        $surveyId = Uuid::v7();
        $survey = createSurveyEntity();
        $contactId = Uuid::v7();
        $useCase = new CreateSurveyResponseUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($survey);

        $this->surveyRepository->shouldReceive('hasContactResponded')
            ->once()
            ->with(Mockery::type(Uuid::class), Mockery::type(Uuid::class))
            ->andReturn(true);

        $dto = new CreateSurveyResponseDto(
            surveyId: $surveyId,
            contactId: $contactId,
            optionId: Uuid::v7(),
        );

        $useCase->execute($surveyId, $dto);
    })->throws(SurveyAlreadyAnsweredException::class);
});

describe('GetSurveyResultsUseCase', function (): void {
    it('returns survey results with options and totals', function (): void {
        $surveyId = Uuid::v7();
        $survey = createSurveyEntity();
        $useCase = new GetSurveyResultsUseCase($this->surveyRepository);

        $conteos = [
            ['option_id' => Uuid::v7()->toString(), 'texto' => 'Sí', 'count' => 15],
            ['option_id' => Uuid::v7()->toString(), 'texto' => 'No', 'count' => 5],
        ];

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($survey);

        $this->surveyRepository->shouldReceive('totalResponsesBySurveyId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(20);

        $this->surveyRepository->shouldReceive('resultsBySurveyId')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($conteos);

        $result = $useCase->execute($surveyId);

        expect($result->surveyId)->toBe($surveyId->toString())
            ->and($result->total)->toBe(20)
            ->and($result->conteos)->toHaveCount(2)
            ->and($result->conteos[0]['texto'])->toBe('Sí')
            ->and($result->conteos[0]['count'])->toBe(15)
            ->and($result->conteos[0]['cerrada'])->toBeFalse()
            ->and($result->conteos[1]['texto'])->toBe('No')
            ->and($result->conteos[1]['count'])->toBe(5);
    });

    it('throws SurveyNotFoundException when survey does not exist', function (): void {
        $useCase = new GetSurveyResultsUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7());
    })->throws(SurveyNotFoundException::class);
});

describe('ListSurveysUseCase', function (): void {
    it('returns a paginated list of surveys', function (): void {
        $condominiumId = Uuid::v7();
        $survey = createSurveyEntity(['condominiumId' => $condominiumId]);
        $useCase = new ListSurveysUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), [], 1, 20)
            ->andReturn([
                'items' => [
                    [
                        'entity' => $survey,
                        'options_count' => 3,
                        'responses_count' => 10,
                    ],
                ],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominiumId, [], 1, 20);

        expect($result['items'])->toHaveCount(1)
            ->and($result['total'])->toBe(1)
            ->and($result['page'])->toBe(1)
            ->and($result['perPage'])->toBe(20)
            ->and($result['items'][0]->id)->toBe($survey->id()->toString())
            ->and($result['items'][0]->pregunta)->toBe($survey->pregunta())
            ->and($result['items'][0]->optionsCount)->toBe(3)
            ->and($result['items'][0]->responsesCount)->toBe(10);
    });

    it('filters by condominiumId and active status', function (): void {
        $condominiumId = Uuid::v7();
        $survey = createSurveyEntity([
            'condominiumId' => $condominiumId,
            'activa' => true,
        ]);
        $useCase = new ListSurveysUseCase($this->surveyRepository);

        $this->surveyRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), ['activa' => true], 1, 15)
            ->andReturn([
                'items' => [
                    [
                        'entity' => $survey,
                        'options_count' => 2,
                        'responses_count' => 5,
                    ],
                ],
                'total' => 1,
                'page' => 1,
                'perPage' => 15,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominiumId, ['activa' => true], 1, 15);

        expect($result['items'])->toHaveCount(1)
            ->and($result['items'][0]->activa)->toBeTrue();
    });
});
