<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

interface DiseaseCauseServiceInterface
{
    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): ?PlayerDisease;
}
