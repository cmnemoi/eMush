<?php

namespace Mush\Equipment\Event;

use Mush\Action\Enum\ActionEnum;
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
    public const INVENTORY_OVERFLOW = 'inventory.overflow';
    public const CHANGE_HOLDER = 'change.holder';

    private GameEquipment $equipment;
    private string $visibility;
    private bool $created;

    public function __construct(
        GameEquipment $equipment,
        bool $created,
        string $visibility,
        string $reason,
        \DateTime $time
    ) {
        $this->equipment = $equipment;
        $this->visibility = $visibility;
        $this->created = $created;

        parent::__construct($reason, $time);
    }

    public function getEquipment() : GameEquipment {
        return $this->equipment;
    }

    public function isCreated(): bool
    {
        return $this->created;
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

        if ($this->created) {
            $logParameters['target_' . $this->equipment->getLogKey()] = $this->equipment->getLogName();
        } else {
            $logParameters[$this->equipment->getLogKey()] = $this->equipment->getLogName();
        }

        $holder = $this->equipment->getHolder();
        if ($holder instanceof Player) {
            $logParameters[$holder->getLogKey()] = $holder->getLogName();
        }

        return $logParameters;
    }
}
