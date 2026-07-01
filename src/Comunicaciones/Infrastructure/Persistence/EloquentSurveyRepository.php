<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Persistence;

use App\Models\Survey as SurveyModel;
use App\Models\SurveyOption as SurveyOptionModel;
use App\Models\SurveyResponse as SurveyResponseModel;
use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Comunicaciones\Infrastructure\Mappers\SurveyMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentSurveyRepository implements SurveyRepositoryInterface
{
    public function __construct(
        private SurveyMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?SurveyEntity
    {
        $model = SurveyModel::find($id->toString());

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<array{entity: SurveyEntity, options_count: int, responses_count: int}>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = SurveyModel::forCondominium($condominiumId->toString())
            ->withCount(['options', 'responses']);

        if (array_key_exists('activa', $filters) && $filters['activa'] !== null) {
            $query->where('activa', (bool) $filters['activa']);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        /** @var array<array{entity: SurveyEntity, options_count: int, responses_count: int}> $items */
        $items = [];

        foreach ($paginator->items() as $model) {
            $items[] = [
                'entity' => $this->mapper->toDomain($model),
                'options_count' => (int) $model->options_count,
                'responses_count' => (int) $model->responses_count,
            ];
        }

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function save(SurveyEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);

        SurveyModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    public function saveOption(SurveyOptionEntity $entity): void
    {
        $data = $this->mapper->optionToPersistence($entity);

        SurveyOptionModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    public function saveResponse(SurveyResponseEntity $entity): void
    {
        $data = $this->mapper->responseToPersistence($entity);

        SurveyResponseModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    /**
     * @return array<SurveyOptionEntity>
     */
    public function findOptionsBySurveyId(Uuid $surveyId): array
    {
        $models = SurveyOptionModel::where('survey_id', $surveyId->toString())
            ->orderBy('orden')
            ->get();

        return $models->map(fn (SurveyOptionModel $m) => $this->mapper->optionToDomain($m))->all();
    }

    public function findOptionById(Uuid $id): ?SurveyOptionEntity
    {
        $model = SurveyOptionModel::find($id->toString());

        return $model !== null ? $this->mapper->optionToDomain($model) : null;
    }

    public function hasContactResponded(Uuid $surveyId, Uuid $contactId): bool
    {
        return SurveyResponseModel::where('survey_id', $surveyId->toString())
            ->where('contact_id', $contactId->toString())
            ->exists();
    }

    /**
     * @return array<int, array{option_id: string, texto: string, count: int}>
     */
    public function resultsBySurveyId(Uuid $surveyId): array
    {
        $options = SurveyOptionModel::where('survey_id', $surveyId->toString())
            ->orderBy('orden')
            ->get();

        /** @var array<int, array{option_id: string, texto: string, count: int}> $results */
        $results = [];

        foreach ($options as $option) {
            $count = SurveyResponseModel::where('option_id', $option->id)->count();

            $results[] = [
                'option_id' => (string) $option->id,
                'texto' => (string) $option->texto,
                'count' => $count,
            ];
        }

        return $results;
    }

    public function totalResponsesBySurveyId(Uuid $surveyId): int
    {
        return SurveyResponseModel::where('survey_id', $surveyId->toString())->count();
    }
}
