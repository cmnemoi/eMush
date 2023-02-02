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
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_DELETE = 'equipment.delete';
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
        array $tags,
        \DateTime $time
    ) {
        $this->equipment = $equipment;
        $this->visibility = $visibility;
        $this->created = $created;

        parent::__construct($tags, $time);
    }

    public function getEquipment(): GameEquipment
    {
        return $this->equipment;
    }

    public function isCreated(): bool
    {
        return $this->created;
    }

    public function getPlace(): Place
    {
        return $this->equipment->getPlace();
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
