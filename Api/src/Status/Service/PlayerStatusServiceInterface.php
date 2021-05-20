<?php

namespace Mush\Status\Service;

use Mush\Player\Entity\Player;

interface PlayerStatusServiceInterface
{
    public function handleSatietyStatus(int $satietyModifier, Player $player, \DateTime $dateTime): void;

    public function handleMoralStatus(Player $player): void;
}
