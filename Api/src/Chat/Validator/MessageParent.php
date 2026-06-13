<?php

declare(strict_types=1);

namespace Mush\Chat\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MessageParent extends Constraint
{
    public const PARENT_CANNOT_BE_SET = 'parent_cannot_be_set';

    public string $message = 'Cannot set a parent message for non public channel';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
