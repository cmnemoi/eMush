<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\Equipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

interface EquipmentModifierServiceInterface
{
    public function gearCreated(Equipment $gameEquipment): void;

    public function gearDestroyed(Equipment $gameEquipment): void;

    public function takeEquipment(Equipment $gameEquipment, Player $player): void;

    public function dropEquipment(Equipment $gameEquipment, Player $player): void;

    public function equipmentEnterRoom(Equipment $gameEquipment, Place $place): void;

    public function equipmentLeaveRoom(Equipment $gameEquipment, Place $place): void;
}
