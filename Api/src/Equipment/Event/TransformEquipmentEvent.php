<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Equipment;
use Mush\Player\Entity\Player;

class TransformEquipmentEvent extends EquipmentEvent
{
    protected Equipment $from;

    public function __construct(
        Equipment $equipment,
        Equipment $from,
        string    $visibility,
        string    $reason,
        \DateTime $time
    ) {
        parent::__construct($equipment, false, $visibility, $reason, $time);

        $this->from = $from;
    }

    public function getEquipmentFrom(): Equipment
    {
        return $this->from;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        $logParameters['target_' . $this->getEquipment()->getLogKey()] = $this->getEquipment()->getLogName();

        $logParameters[$this->from->getLogKey()] = $this->from->getLogName();

        $holder = $this->getEquipment()->getHolder();
        if ($holder instanceof Player) {
            $logParameters[$holder->getLogKey()] = $holder->getLogName();
        }

        return $logParameters;
    }
}
