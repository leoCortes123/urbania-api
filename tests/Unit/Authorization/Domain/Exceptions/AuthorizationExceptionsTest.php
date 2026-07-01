<?php

declare(strict_types=1);

namespace Tests\Unit\Authorization\Domain\Exceptions;

use ReflectionClass;
use Urbania\Authorization\Domain\Exceptions\ApprovalRuleInvalidApproverException;
use Urbania\Authorization\Domain\Exceptions\AssignmentAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\AssignmentNotFoundException;
use Urbania\Authorization\Domain\Exceptions\AuthorizationContextException;
use Urbania\Authorization\Domain\Exceptions\RoleIsSystemException;
use Urbania\Authorization\Domain\Exceptions\RoleNameAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;
use Urbania\Shared\Domain\Exceptions\DomainException;

it('all authorization exceptions extend DomainException', function (): void {
    $exceptions = [
        RoleNotFoundException::class,
        RoleNameAlreadyExistsException::class,
        RoleIsSystemException::class,
        AssignmentAlreadyExistsException::class,
        AssignmentNotFoundException::class,
        ApprovalRuleInvalidApproverException::class,
        AuthorizationContextException::class,
    ];

    foreach ($exceptions as $exceptionClass) {
        $reflection = new ReflectionClass($exceptionClass);
        expect($reflection->isSubclassOf(DomainException::class))->toBeTrue();
    }
});

it('has expected error codes and http status codes', function (): void {
    $cases = [
        [RoleNotFoundException::class, 'ROLE_NOT_FOUND', 404],
        [RoleNameAlreadyExistsException::class, 'ROLE_NAME_ALREADY_EXISTS', 409],
        [RoleIsSystemException::class, 'ROLE_IS_SYSTEM', 403],
        [AssignmentAlreadyExistsException::class, 'ASSIGNMENT_ALREADY_EXISTS', 409],
        [AssignmentNotFoundException::class, 'ASSIGNMENT_NOT_FOUND', 404],
        [ApprovalRuleInvalidApproverException::class, 'APPROVAL_RULE_INVALID_APPROVER', 422],
        [AuthorizationContextException::class, 'AUTHORIZATION_CONTEXT_INVALID', 403],
    ];

    foreach ($cases as [$exceptionClass, $expectedCode, $expectedStatus]) {
        $exception = new $exceptionClass;
        expect($exception->errorCode)->toBe($expectedCode)
            ->and($exception->httpStatusCode)->toBe($expectedStatus);
    }
});

it('preserves default messages', function (): void {
    $defaultMessages = [
        RoleNotFoundException::class => 'Rol no encontrado',
        RoleNameAlreadyExistsException::class => 'Ya existe un rol con ese nombre en la organización',
        RoleIsSystemException::class => 'Los roles de sistema no pueden modificarse',
        AssignmentAlreadyExistsException::class => 'El usuario ya tiene asignado ese rol en el alcance indicado',
        AssignmentNotFoundException::class => 'Asignación de rol no encontrada',
        ApprovalRuleInvalidApproverException::class => 'El rol aprobador no existe en la organización',
        AuthorizationContextException::class => 'Contexto de autorización inválido',
    ];

    foreach ($defaultMessages as $exceptionClass => $expectedMessage) {
        $exception = new $exceptionClass;
        expect($exception->getMessage())->toBe($expectedMessage);
    }
});

it('preserves custom messages', function (): void {
    $exception = new RoleNotFoundException('Custom role not found message');

    expect($exception->getMessage())->toBe('Custom role not found message');
});
