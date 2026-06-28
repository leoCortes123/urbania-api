<?php

declare(strict_types=1);

namespace Directorio\Domain\Repositories;

use Directorio\Domain\Entities\Contact;

interface ContactRepository
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Contact[]
     */
    public function findAll(array $filters = []): array;

    public function findById(string $id): ?Contact;

    public function findByDocument(string $documentType, string $documentNumber): ?Contact;

    public function findByUserId(string $userId): ?Contact;

    public function save(Contact $contact): Contact;

    public function update(Contact $contact): Contact;

    public function delete(string $id): void;

    public function count(): int;
}
