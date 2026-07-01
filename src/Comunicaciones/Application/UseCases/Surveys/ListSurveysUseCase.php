<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Surveys;

use Urbania\Comunicaciones\Application\DTOs\SurveyListItemDto;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListSurveysUseCase
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<SurveyListItemDto>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function execute(Uuid $condominiumId, array $filters, int $page, int $perPage): array
    {
        $result = $this->surveyRepository->findByCondominiumId($condominiumId, $filters, $page, $perPage);

        $items = [];
        foreach ($result['items'] as $item) {
            $entity = $item['entity'];
            $items[] = new SurveyListItemDto(
                id: $entity->id()->toString(),
                pregunta: $entity->pregunta(),
                tipo: $entity->tipo()->value,
                cierraEl: $entity->cierraEl()?->format('c'),
                activa: $entity->activa(),
                optionsCount: $item['options_count'],
                responsesCount: $item['responses_count'],
                createdAt: $entity->createdAt()->format('c'),
            );
        }

        return [
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'lastPage' => $result['lastPage'],
        ];
    }
}
