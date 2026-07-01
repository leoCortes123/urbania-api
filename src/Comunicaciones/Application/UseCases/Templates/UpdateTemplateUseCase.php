<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Templates;

use Urbania\Comunicaciones\Application\DTOs\TemplateDto;
use Urbania\Comunicaciones\Application\DTOs\UpdateTemplateDto;
use Urbania\Comunicaciones\Domain\Exceptions\TemplateNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdateTemplateUseCase
{
    public function __construct(
        private MessageTemplateRepositoryInterface $templateRepository,
    ) {}

    public function execute(Uuid $id, UpdateTemplateDto $dto): TemplateDto
    {
        $entity = $this->templateRepository->findById($id);

        if ($entity === null) {
            throw new TemplateNotFoundException;
        }

        $updated = $entity->update(
            nombre: $dto->nombre,
            tipo: $dto->tipo,
            cuerpo: $dto->cuerpo,
        );

        $this->templateRepository->save($updated);

        return TemplateDto::fromEntity($updated);
    }
}
