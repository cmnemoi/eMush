<?php

namespace Mush\Communication\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MaxNestedParent extends Constraint
{
    public const MAXIMUM_NESTED_MESSAGE_ERROR = 'maximum_nested_message_error';

    public string $message = 'The message tree maximum deep is 1';
}
