<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;

class ExpressCook extends AbstractCook
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    protected function applyEffects(): ActionResult
    {
        parent::applyEffects();

        // @TODO add effect on the link with sol

        return new Success();
    }
}
