<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

class PlayerCanAffordPoints extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::INSUFFICIENT_ACTION_POINT;
}
