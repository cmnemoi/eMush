<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class Fail extends ActionResult
{
    public function getName(): string
    {
        return ActionOutputEnum::FAIL;
    }
}
