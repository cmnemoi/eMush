<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class EquipmentEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_TRANSFORM = 'equipment.transform';
    public const CHANGE_HOLDER = 'change.holder';

    private ?GameEquipment $newEquipment = null;
    private ?GameEquipment $existingEquipment = null;
    private string $equipmentName;
    private string $visibility;
    private EquipmentHolderInterface $holder;

    public function __construct(
        string $equipmentName,
        EquipmentHolderInterface $holder,
        string $visibility,
        string $reason,
        \DateTime $time
    ) {
        $this->equipmentName = $equipmentName;
        $this->visibility = $visibility;
        $this->holder = $holder;

        parent::__construct($reason, $time);
    }

    public function getEquipmentName(): string
    {
        return $this->equipmentName;
    }

    public function getNewEquipment(): ?GameEquipment
    {
        return $this->newEquipment;
    }

    public function setNewEquipment(GameEquipment $newEquipment): self
    {
        $this->newEquipment = $newEquipment;

        return $this;
    }

    public function getExistingEquipment(): ?GameEquipment
    {
        return $this->existingEquipment;
    }

    public function setExistingEquipment(GameEquipment $existingEquipment): self
    {
        $this->existingEquipment = $existingEquipment;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->holder->getPlace();
    }

    public function getHolder(): EquipmentHolderInterface
    {
        return $this->holder;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        if ($this->newEquipment !== null) {
            $logParameters['target_' . $this->newEquipment->getLogKey()] = $this->newEquipment->getLogName();
        }

        if ($this->existingEquipment !== null) {
            $logParameters[$this->existingEquipment->getLogKey()] = $this->existingEquipment->getLogName();
        }

        if ($this->holder instanceof Player) {
            $logParameters[$this->holder->getLogKey()] = $this->holder->getLogName();
        }

        return $logParameters;
    }
}
