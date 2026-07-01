<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\UseCases\Templates;

use Urbania\Comunicaciones\Domain\Exceptions\TemplateNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeleteTemplateUseCase
{
    public function __construct(
        private MessageTemplateRepositoryInterface $templateRepository,
    ) {}

    public function execute(Uuid $id): void
    {
        $entity = $this->templateRepository->findById($id);

        if ($entity === null) {
            throw new TemplateNotFoundException;
        }

        $this->templateRepository->delete($id);
    }
}
