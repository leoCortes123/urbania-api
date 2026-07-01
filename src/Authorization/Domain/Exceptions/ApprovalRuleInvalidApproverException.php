<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ApprovalRuleInvalidApproverException extends DomainException
{
    public function __construct(string $message = 'El rol aprobador no existe en la organización')
    {
        parent::__construct('APPROVAL_RULE_INVALID_APPROVER', $message, 422);
    }
}
