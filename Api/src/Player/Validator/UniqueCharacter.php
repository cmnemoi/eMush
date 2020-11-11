<?php

namespace Mush\Player\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCharacter extends Constraint
{
    public const CHARACTER_IS_NOT_UNIQUE_ERROR = 'character_is_not_unique_error';

    public string $message = 'This character already exist in this daedalus';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
