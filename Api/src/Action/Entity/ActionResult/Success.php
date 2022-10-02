<?php

namespace Mush\Action\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class Success extends ActionResult
{
    public function getName(): string
    {
        return ActionOutputEnum::SUCCESS;
    }
}
