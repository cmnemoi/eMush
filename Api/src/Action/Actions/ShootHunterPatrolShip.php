<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;

final class ShootHunterPatrolShip extends ShootHunter
{
    protected string $name = ActionEnum::SHOOT_HUNTER_PATROL_SHIP;
}
