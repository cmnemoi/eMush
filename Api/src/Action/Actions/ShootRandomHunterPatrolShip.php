<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;

final class ShootRandomHunterPatrolShip extends ShootRandomHunter
{
    protected string $name = ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP;
}
