<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;

final class DroneRepairedEvent extends AbstractDroneEvent
{
    public function __construct(
        Drone $drone,
        private GameEquipment $repairedEquipment,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($drone, $tags, $time);
    }

    public function getRepairedEquipment(): GameEquipment
    {
        return $this->repairedEquipment;
    }

    public function getLogParameters(): array
    {
        return array_merge(parent::getLogParameters(), [
            'target_' . $this->repairedEquipment->getLogKey() => $this->repairedEquipment->getLogName(),
        ]);
    }
}
