<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

interface EquipmentModifierServiceInterface
{
    public function gearCreated(GameEquipment $gameEquipment, array $tags, \DateTime $time): void;

    public function gearDestroyed(GameEquipment $gameEquipment, array $tags, \DateTime $time): void;

    public function takeEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void;

    public function dropEquipment(GameEquipment $gameEquipment, Player $player, array $tags, \DateTime $time): void;

    public function equipmentEnterRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void;

    public function equipmentLeaveRoom(GameEquipment $gameEquipment, Place $place, array $tags, \DateTime $time): void;
}
