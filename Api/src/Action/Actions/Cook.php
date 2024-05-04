<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;

class Cook extends AbstractCook
{
    protected ActionEnum $name = ActionEnum::COOK;
}
