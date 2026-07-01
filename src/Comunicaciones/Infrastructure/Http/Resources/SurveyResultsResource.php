<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\SurveyResultsDto;

/**
 * @mixin SurveyResultsDto
 */
final class SurveyResultsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SurveyResultsDto $dto */
        $dto = $this->resource;

        return [
            'survey_id' => $dto->surveyId,
            'total' => $dto->total,
            'conteos' => $dto->conteos,
        ];
    }
}
