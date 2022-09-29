<?php

namespace Mush\Disease\Service;

use Mush\Equipment\Entity\Equipment;
use Mush\Player\Entity\Player;

interface DiseaseCauseServiceInterface
{
    public function handleSpoiledFood(Player $player, Equipment $gameEquipment): void;

    public function handleConsumable(Player $player, Equipment $gameEquipment): void;
}
