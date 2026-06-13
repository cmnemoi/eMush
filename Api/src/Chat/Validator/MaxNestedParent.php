<?php

declare(strict_types=1);

namespace Mush\Chat\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MaxNestedParent extends Constraint
{
    public const MAXIMUM_NESTED_MESSAGE_ERROR = 'maximum_nested_message_error';

    public string $message = 'The message tree maximum deep is 1';
}
