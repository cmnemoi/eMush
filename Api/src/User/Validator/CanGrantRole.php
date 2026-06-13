<?php

declare(strict_types=1);

namespace Mush\User\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class CanGrantRole extends Constraint
{
    public const CANNOT_GRANT_ROLE = 'cannot_grant_role';

    public string $message = 'You cannot grant this role';
}
