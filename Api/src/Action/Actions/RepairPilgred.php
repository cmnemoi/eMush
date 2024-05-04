<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;

final class RepairPilgred extends AbstractParticipateAction
{
    protected ActionEnum $name = ActionEnum::REPAIR_PILGRED;
}
