<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\RoomLog\Entity\LogParameterInterface;

class ShootRandomHunter extends ShootHunter
{
    protected string $name = ActionEnum::SHOOT_RANDOM_HUNTER;

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }
}
