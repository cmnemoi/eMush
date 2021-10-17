<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Player\Entity\Player;

interface GearModifierServiceInterface
{
    public function gearCreated(GameEquipment $gameEquipment): void;

    public function gearDestroyed(GameEquipment $gameEquipment): void;

    public function takeGear(GameEquipment $gameEquipment, Player $player): void;

    public function dropGear(GameEquipment $gameEquipment, Player $player): void;

    public function handleDisplacement(Player $player): void;
}
