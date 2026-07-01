<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Repositories;

use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface SurveyRepositoryInterface
{
    public function findById(Uuid $id): ?SurveyEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<array{entity: SurveyEntity, options_count: int, responses_count: int}>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(SurveyEntity $entity): void;

    public function saveOption(SurveyOptionEntity $entity): void;

    public function saveResponse(SurveyResponseEntity $entity): void;

    /** @return array<SurveyOptionEntity> */
    public function findOptionsBySurveyId(Uuid $surveyId): array;

    public function findOptionById(Uuid $id): ?SurveyOptionEntity;

    public function hasContactResponded(Uuid $surveyId, Uuid $contactId): bool;

    /**
     * @return array<int, array{option_id: string, texto: string, count: int}>
     */
    public function resultsBySurveyId(Uuid $surveyId): array;

    public function totalResponsesBySurveyId(Uuid $surveyId): int;
}
