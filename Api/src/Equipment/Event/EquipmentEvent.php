<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class EquipmentEvent extends EquipmentCycleEvent implements LoggableEventInterface
{
    public const EQUIPMENT_CREATED = 'equipment.created';
    public const EQUIPMENT_DESTROYED = 'equipment.destroyed';
    public const EQUIPMENT_DELETE = 'equipment.delete';
    public const EQUIPMENT_TRANSFORM = 'equipment.transform';
    public const INVENTORY_OVERFLOW = 'inventory.overflow';
    public const CHANGE_HOLDER = 'change.holder';

    private string $visibility;
    private bool $created;

    public function __construct(
        GameEquipment $equipment,
        bool $created,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        $this->visibility = $visibility;
        $this->created = $created;

        parent::__construct($equipment, $equipment->getDaedalus(), $tags, $time);
    }


    public function isCreated(): bool
    {
        return $this->created;
    }

    public function getPlace(): Place
    {
        return $this->gameEquipment->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        if ($this->created) {
            $logParameters['target_' . $this->gameEquipment->getLogKey()] = $this->gameEquipment->getLogName();
        } else {
            $logParameters[$this->gameEquipment->getLogKey()] = $this->gameEquipment->getLogName();
        }

        $holder = $this->gameEquipment->getHolder();
        if ($holder instanceof Player) {
            $logParameters[$holder->getLogKey()] = $holder->getLogName();
        }

        return $logParameters;
    }
}
