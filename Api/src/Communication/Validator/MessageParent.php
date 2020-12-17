<?php

namespace Mush\Communication\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MessageParent extends Constraint
{
    public const PARENT_CANNOT_BE_SET = 'parent_cannot_be_set';

    public string $message = 'Cannot set a parent message for non public channel';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
