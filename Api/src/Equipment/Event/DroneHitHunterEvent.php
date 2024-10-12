<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Drone;
use Mush\Hunter\Entity\Hunter;

final class DroneHitHunterEvent extends AbstractDroneEvent
{
    public function __construct(
        Drone $drone,
        private Hunter $hunter,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($drone, $tags, $time);
    }

    public function getHunter(): Hunter
    {
        return $this->hunter;
    }

    public function getLogParameters(): array
    {
        return array_merge(parent::getLogParameters(), [
            $this->hunter->getLogKey() => $this->hunter->getLogName(),
        ]);
    }
}
