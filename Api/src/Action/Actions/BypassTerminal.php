<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

final class BypassTerminal extends AccessTerminal
{
    protected ActionEnum $name = ActionEnum::BYPASS_TERMINAL;

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment && $target->getName() === EquipmentEnum::BIOS_TERMINAL;
    }
}
