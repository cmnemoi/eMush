<?php

namespace Mush\User\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUser extends Constraint
{
    public const USER_IS_ALREADY_ON_DAEDALUS = 'user_is_already_on_daedalus';

    public string $message = 'This user already have a character on this Daedalus';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
