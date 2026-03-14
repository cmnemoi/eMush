<?php

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

class PlayerCanAffordSpecialistPoints extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::INSUFFICIENT_SPECIAL_POINT;
}
