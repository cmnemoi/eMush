<?php

namespace Mush\Action\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class CriticalFail extends Fail
{
    public function getName(): string
    {
        return ActionOutputEnum::CRITICAL_FAIL;
    }
}
