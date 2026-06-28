<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class GetContactPropertiesUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
        private PropertyOccupantRepository $occupantRepository,
    ) {}

    /** @return PropertyOccupant[] */
    public function execute(string $contactId): array
    {
        $contact = $this->contactRepository->findById($contactId);
        if ($contact === null) {
            throw new ContactNotFoundException($contactId);
        }

        return $this->occupantRepository->findByContact($contactId);
    }
}
