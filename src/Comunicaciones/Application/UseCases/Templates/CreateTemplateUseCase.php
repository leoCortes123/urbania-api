<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Templates;

use Urbania\Comunicaciones\Application\DTOs\CreateTemplateDto;
use Urbania\Comunicaciones\Application\DTOs\TemplateDto;
use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;

final readonly class CreateTemplateUseCase
{
    public function __construct(
        private MessageTemplateRepositoryInterface $templateRepository,
    ) {}

    public function execute(CreateTemplateDto $dto): TemplateDto
    {
        $entity = MessageTemplateEntity::create(
            condominiumId: $dto->condominiumId,
            nombre: $dto->nombre,
            tipo: $dto->tipo,
            cuerpo: $dto->cuerpo,
        );

        $this->templateRepository->save($entity);

        return TemplateDto::fromEntity($entity);
    }
}
