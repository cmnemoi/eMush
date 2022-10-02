<?php

namespace Mush\User\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CanGrantRole extends Constraint
{
    public const CANNOT_GRANT_ROLE = 'cannot_grant_role';

    public string $message = 'You cannot grant this role';
}
