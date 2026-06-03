<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

class PrivateProperty extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::PRIVATE_PROPERTY_NOT_OWNER;
}
