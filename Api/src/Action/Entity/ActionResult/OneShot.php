<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class OneShot extends Success
{
    public function getName(): string
    {
        return ActionOutputEnum::ONE_SHOT;
    }
}
