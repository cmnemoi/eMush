<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

interface EquipmentModifierServiceInterface
{
    public function gearCreated(GameEquipment $gameEquipment): void;

    public function gearDestroyed(GameEquipment $gameEquipment): void;

    public function takeEquipment(GameEquipment $gameEquipment, Player $player): void;

    public function dropEquipment(GameEquipment $gameEquipment, Player $player): void;

    public function equipmentEnterRoom(GameEquipment $gameEquipment, Place $place): void;

    public function equipmentLeaveRoom(GameEquipment $gameEquipment, Place $place): void;
}
