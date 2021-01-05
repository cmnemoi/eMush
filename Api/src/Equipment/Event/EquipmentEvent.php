<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class EquipmentEvent extends Event
{
    public const EQUIPMENT_CREATED = 'equipment.created';

    private GameEquipment $equipment;
    private ?Player $player;

    public function __construct(GameEquipment $equipment)
    {
        $this->equipment = $equipment;
    }

    public function getEquipment(): GameEquipment
    {
        return $this->equipment;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer($player): EquipmentEvent
    {
        $this->player = $player;

        return $this;
    }
}
