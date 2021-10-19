<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class EquipmentEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_FIXED = 'equipment.fixed';
    public const EQUIPMENT_BROKEN = 'equipment.broken';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_TRANSFORM = 'equipment.transform';

    private GameEquipment $equipment;
    private string $visibility;
    private Place $place;
    private ?Player $player = null;
    private ?GameEquipment $replacementEquipment = null;

    public function __construct(
        GameEquipment $equipment,
        Place $place,
        string $visibility,
        string $reason,
        \DateTime $time
    ) {
        $this->equipment = $equipment;
        $this->visibility = $visibility;
        $this->place = $place;

        parent::__construct($reason, $time);
    }

    public function getEquipment(): GameEquipment
    {
        return $this->equipment;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getReplacementEquipment(): ?GameEquipment
    {
        return $this->replacementEquipment;
    }

    public function setReplacementEquipment(GameEquipment $replacement): self
    {
        $this->replacementEquipment = $replacement;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [
           $this->equipment->getLogKey() => $this->equipment->getLogName(),
        ];

        if ($this->player !== null) {
            $logParameters[$this->player->getLogKey()] = $this->player->getLogName();
        }

        return $logParameters;
    }
}
