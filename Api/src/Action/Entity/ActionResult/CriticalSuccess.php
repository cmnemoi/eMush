<?php

namespace Mush\Action\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class CriticalSuccess extends ActionResult
{
    public function getName(): string
    {
        return ActionOutputEnum::CRITICAL_SUCCESS;
    }
}
