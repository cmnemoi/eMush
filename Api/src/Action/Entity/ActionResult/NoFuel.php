<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

final class NoFuel extends Fail
{
    public function getName(): string
    {
        return ActionOutputEnum::NO_FUEL;
    }
}
