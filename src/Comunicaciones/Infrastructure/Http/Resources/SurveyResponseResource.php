<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\SurveyResponseDto;

/**
 * @mixin SurveyResponseDto
 */
final class SurveyResponseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SurveyResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'survey_id' => $dto->surveyId,
            'contact_id' => $dto->contactId,
            'option_id' => $dto->optionId,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
