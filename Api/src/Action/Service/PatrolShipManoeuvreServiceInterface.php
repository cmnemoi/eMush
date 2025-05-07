<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Player\Entity\Player;

interface PatrolShipManoeuvreServiceInterface
{
    public function handleLand(
        SpaceShip $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void;

    public function handleTakeoff(
        SpaceShip $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void;
}
