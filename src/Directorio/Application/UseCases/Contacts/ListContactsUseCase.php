<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Repositories\ContactRepository;

readonly class ListContactsUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Contact[]
     */
    public function execute(array $filters = []): array
    {
        return $this->contactRepository->findAll($filters);
    }
}
