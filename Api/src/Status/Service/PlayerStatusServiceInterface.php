<?php

namespace Mush\Status\Service;

use Mush\Player\Entity\Player;

interface PlayerStatusServiceInterface
{
    public function handleSatietyStatus(Player $player, \DateTime $dateTime): void;

    public function handleMoralStatus(Player $player, \DateTime $dateTime): void;
}
