<?php

namespace Mush\Disease\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

interface DiseaseCauseServiceInterface
{
    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void;

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void;
}
