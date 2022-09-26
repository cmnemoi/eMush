<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class InteractWithEquipmentEvent extends EquipmentEvent
{

    protected EquipmentHolderInterface $actor;

    public function __construct(
        GameEquipment $equipment,
        EquipmentHolderInterface $actor,
        string $visibility,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($equipment, false, $visibility, $reason, $time);

        $this->actor = $actor;
    }

    public function getActor(): EquipmentHolderInterface
    {
        return $this->actor;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        $logParameters[$this->getEquipment()->getLogKey()] = $this->getEquipment()->getLogName();

        if ($this->actor instanceof Player) {
            $logParameters[$this->actor->getLogKey()] = $this->actor->getLogName();
        }

        return $logParameters;
    }

}