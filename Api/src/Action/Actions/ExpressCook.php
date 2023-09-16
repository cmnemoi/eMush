<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;

class ExpressCook extends AbstractCook
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    protected function checkResult(): ActionResult
    {
        // @TODO add effect on the link with sol
        return parent::checkResult();
    }

    protected function applyEffect(ActionResult $result): void
    {
        parent::applyEffect($result);
        // @TODO add effect on the link with sol
    }
}
