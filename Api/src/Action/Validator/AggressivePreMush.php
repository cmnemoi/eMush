<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

class AggressivePreMush extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE;
}
