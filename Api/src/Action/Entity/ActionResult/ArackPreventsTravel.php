<?php

declare(strict_types=1);

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

final class ArackPreventsTravel extends Fail
{
    public function getName(): string
    {
        return ActionOutputEnum::ARACK_PREVENTS_TRAVEL;
    }
}
