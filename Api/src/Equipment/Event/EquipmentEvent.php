<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class EquipmentEvent extends Event
{
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_BROKEN = 'equipment.broken';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';

    private GameEquipment $equipment;
    private string $visibility;
    private ?Player $player;
    private ?string $reason;
    private \DateTime $time;

    public function __construct(GameEquipment $equipment, string $visibility, ?\DateTime $time = null)
    {
        $this->time = $time ?? new \DateTime();
        $this->equipment = $equipment;
        $this->visibility = $visibility;
    }

    public function getEquipment(): GameEquipment
    {
        return $this->equipment;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): EquipmentEvent
    {
        $this->player = $player;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): EquipmentEvent
    {
        $this->reason = $reason;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }
}
