<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;

class ExpressCook extends Cook
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    protected function checkResult(): ActionResult
    {
        // @TODO add effect on the link with sol
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        parent::applyEffect($result);
        // @TODO add effect on the link with sol
    }

}
