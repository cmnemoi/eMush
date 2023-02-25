<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

class TransformEquipmentEvent extends EquipmentEvent
{
    protected GameEquipment $from;

    public function __construct(
        GameEquipment $equipment,
        GameEquipment $from,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($equipment, false, $visibility, $tags, $time);

        $this->from = $from;
    }

    public function getEquipmentFrom(): GameEquipment
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
