<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Contacts;

use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Exceptions\ContactNotFoundException;
use Directorio\Domain\Repositories\ContactRepository;

readonly class GetContactUseCase
{
    public function __construct(
        private ContactRepository $contactRepository,
    ) {}

    public function execute(string $id): Contact
    {
        $contact = $this->contactRepository->findById($id);
        if ($contact === null) {
            throw new ContactNotFoundException($id);
        }

        return $contact;
    }
}
