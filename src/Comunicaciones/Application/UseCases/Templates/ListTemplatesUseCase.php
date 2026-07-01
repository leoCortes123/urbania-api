<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Templates;

use Urbania\Comunicaciones\Application\DTOs\TemplateDto;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListTemplatesUseCase
{
    public function __construct(
        private MessageTemplateRepositoryInterface $templateRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<TemplateDto>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function execute(Uuid $condominiumId, array $filters, int $page, int $perPage): array
    {
        $result = $this->templateRepository->findByCondominiumId($condominiumId, $filters, $page, $perPage);

        $items = array_map(
            fn ($entity) => TemplateDto::fromEntity($entity),
            $result['items'],
        );

        return [
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'lastPage' => $result['lastPage'],
        ];
    }
}
