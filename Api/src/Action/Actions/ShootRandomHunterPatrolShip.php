<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\RoomLog\Entity\LogParameterInterface;

final class ShootRandomHunterPatrolShip extends ShootRandomHunter
{
    protected string $name = ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP;
}
