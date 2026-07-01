<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Exceptions;

use ReflectionClass;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\DocumentInvalidTypeException;
use Urbania\Propiedades\Domain\Exceptions\DocumentTooLargeException;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDuplicateUnitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyHasDependenciesException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\SameStatusException;
use Urbania\Propiedades\Domain\Exceptions\StatusHasActiveResidentsException;
use Urbania\Propiedades\Domain\Exceptions\StatusReasonRequiredException;
use Urbania\Propiedades\Domain\Exceptions\TowerHasPropertiesException;
use Urbania\Propiedades\Domain\Exceptions\TowerNameAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all propiedades exceptions extend DomainException', function (): void {
    $exceptions = [
        CondominiumNotFoundException::class,
        TowerNotFoundException::class,
        TowerNameAlreadyExistsException::class,
        TowerHasPropertiesException::class,
        PropertyTypeNotFoundException::class,
        PropertyTypeInUseException::class,
        PropertyTypeCodeAlreadyExistsException::class,
        PropertyStatusNotFoundException::class,
        PropertyStatusInUseException::class,
        PropertyStatusCodeAlreadyExistsException::class,
        PropertyNotFoundException::class,
        PropertyHasDependenciesException::class,
        PropertyDuplicateUnitException::class,
        PropertyDocumentTypeNotFoundException::class,
        PropertyDocumentTypeInUseException::class,
        PropertyDocumentTypeCodeAlreadyExistsException::class,
        PropertyDocumentNotFoundException::class,
        FloorExceedsTowerLimitException::class,
        SameStatusException::class,
        StatusReasonRequiredException::class,
        StatusHasActiveResidentsException::class,
        DocumentTooLargeException::class,
        DocumentInvalidTypeException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    $cases = [
        [CondominiumNotFoundException::class, 'CONDOMINIUM_NOT_FOUND', 404],
        [TowerNotFoundException::class, 'TOWER_NOT_FOUND', 404],
        [TowerNameAlreadyExistsException::class, 'TOWER_NAME_ALREADY_EXISTS', 409],
        [TowerHasPropertiesException::class, 'TOWER_HAS_PROPERTIES', 409],
        [PropertyTypeNotFoundException::class, 'PROPERTY_TYPE_NOT_FOUND', 404],
        [PropertyTypeInUseException::class, 'PROPERTY_TYPE_IN_USE', 409],
        [PropertyTypeCodeAlreadyExistsException::class, 'PROPERTY_TYPE_CODE_ALREADY_EXISTS', 409],
        [PropertyStatusNotFoundException::class, 'PROPERTY_STATUS_NOT_FOUND', 404],
        [PropertyStatusInUseException::class, 'PROPERTY_STATUS_IN_USE', 409],
        [PropertyStatusCodeAlreadyExistsException::class, 'PROPERTY_STATUS_CODE_ALREADY_EXISTS', 409],
        [PropertyNotFoundException::class, 'PROPERTY_NOT_FOUND', 404],
        [PropertyHasDependenciesException::class, 'PROPERTY_HAS_DEPENDENCIES', 409],
        [PropertyDuplicateUnitException::class, 'PROPERTY_DUPLICATE_UNIT', 409],
        [PropertyDocumentTypeNotFoundException::class, 'PROPERTY_DOCUMENT_TYPE_NOT_FOUND', 404],
        [PropertyDocumentTypeInUseException::class, 'PROPERTY_DOCUMENT_TYPE_IN_USE', 409],
        [PropertyDocumentTypeCodeAlreadyExistsException::class, 'PROPERTY_DOCUMENT_TYPE_CODE_ALREADY_EXISTS', 409],
        [PropertyDocumentNotFoundException::class, 'PROPERTY_DOCUMENT_NOT_FOUND', 404],
        [FloorExceedsTowerLimitException::class, 'FLOOR_EXCEEDS_TOWER_LIMIT', 422],
        [SameStatusException::class, 'SAME_STATUS', 400],
        [StatusReasonRequiredException::class, 'STATUS_REASON_REQUIRED', 422],
        [StatusHasActiveResidentsException::class, 'STATUS_HAS_ACTIVE_RESIDENTS', 400],
        [DocumentTooLargeException::class, 'DOCUMENT_TOO_LARGE', 413],
        [DocumentInvalidTypeException::class, 'DOCUMENT_INVALID_TYPE', 415],
    ];

    foreach ($cases as [$exceptionClass, $expectedCode, $expectedStatus]) {
        $exception = new $exceptionClass;
        expect($exception->errorCode)->toBe($expectedCode)
            ->and($exception->httpStatusCode)->toBe($expectedStatus);
    }
});

it('preserves custom messages', function (): void {
    $exception = new CondominiumNotFoundException('Custom condominium not found message');

    expect($exception->getMessage())->toBe('Custom condominium not found message');
});
