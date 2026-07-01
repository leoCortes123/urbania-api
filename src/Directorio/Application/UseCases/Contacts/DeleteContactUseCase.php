<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Domain\Exceptions\ContactHasActiveOccupantsException;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class DeleteContactUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
        private PropertyOccupantRepository $occupantRepository,
    ) {}

    public function execute(string $id): void
    {
        $contact = $this->contactRepository->findById($id);
        if ($contact === null) {
            throw new ContactNotFoundException;
        }

        // Validar que no tenga vínculos activos
        $activeOccupants = $this->occupantRepository->findActiveByContact($id);
        if (! empty($activeOccupants)) {
            throw new ContactHasActiveOccupantsException;
        }

        $this->contactRepository->delete($id);
    }
}
