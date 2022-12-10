<?php

namespace Mush\Daedalus\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueName extends Constraint
{
    public const DAEDALUS_NAME_ALREADY_USED = 'daedalus_name_already_used';

    public string $message = 'There is already a daedalus with this name';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
