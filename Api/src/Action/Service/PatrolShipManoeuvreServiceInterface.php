<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

interface PatrolShipManoeuvreServiceInterface
{
    public function handleLand(
        GameEquipment $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void;

    public function handleTakeoff(
        GameEquipment $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void;
}
