<?php

declare(strict_types=1);

namespace Directorio\Domain\Repositories;

use Directorio\Domain\Entities\PropertyOccupant;

interface PropertyOccupantRepository
{
    /** @return PropertyOccupant[] */
    public function findByProperty(string $propertyId): array;

    public function findById(string $id): ?PropertyOccupant;

    /** @return PropertyOccupant[] */
    public function findByContact(string $contactId): array;

    /** @return PropertyOccupant[] */
    public function findActiveByPropertyAndType(string $propertyId, string $occupantTypeId): array;

    /** @return PropertyOccupant[] */
    public function findActiveByContact(string $contactId): array;

    public function findActivePortalPrimaryByPropertyId(string $propertyId): ?PropertyOccupant;

    /**
     * Atomic swap: sets all occupants of the property to is_portal_primary=false
     * and the given occupantId to is_portal_primary=true, in a single transaction.
     */
    public function setPortalPrimary(string $propertyId, string $occupantId): void;

    public function save(PropertyOccupant $occupant): PropertyOccupant;

    public function update(PropertyOccupant $occupant): PropertyOccupant;

    public function delete(string $id): void;

    public function countActiveOwnersByProperty(string $propertyId): int;
}
