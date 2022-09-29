<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\Equipment;
use Mush\Player\Entity\Player;

class InteractWithEquipmentEvent extends EquipmentEvent
{
    protected EquipmentHolderInterface $actor;

    public function __construct(
        Equipment                $equipment,
        EquipmentHolderInterface $actor,
        string                   $visibility,
        string                   $reason,
        \DateTime                $time
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

        $logParameters['target_' . $this->getEquipment()->getLogKey()] = $this->getEquipment()->getLogName();

        if ($this->actor instanceof Player) {
            $logParameters[$this->actor->getLogKey()] = $this->actor->getLogName();
        }

        return $logParameters;
    }
}
