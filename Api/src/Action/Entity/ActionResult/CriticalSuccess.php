<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class CriticalSuccess extends Success
{
    public function getName(): string
    {
        return ActionOutputEnum::CRITICAL_SUCCESS;
    }
}
