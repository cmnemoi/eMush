<?php

declare(strict_types=1);

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

class Success extends ActionResult
{
    public function getName(): string
    {
        return ActionOutputEnum::SUCCESS;
    }
}
