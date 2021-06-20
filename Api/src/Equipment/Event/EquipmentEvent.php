<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class EquipmentEvent extends Event
{
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_FIXED = 'equipment.fixed';
    public const EQUIPMENT_BROKEN = 'equipment.broken';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_TRANSFORM = 'equipment.transform';

    private GameEquipment $equipment;
    private string $visibility;
    private ?Player $player = null;
    private ?Place $place = null;
    private ?string $reason = null;
    private ?GameEquipment $replacementEquipment = null;
    private \DateTime $time;

    public function __construct(GameEquipment $equipment, string $visibility, \DateTime $time)
    {
        $this->time = $time;
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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): EquipmentEvent
    {
        $this->place = $place;

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

    public function getReplacementEquipment(): ?GameEquipment
    {
        return $this->replacementEquipment;
    }

    public function setReplacementEquipment(GameEquipment $replacement): EquipmentEvent
    {
        $this->replacementEquipment = $replacement;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }
}
