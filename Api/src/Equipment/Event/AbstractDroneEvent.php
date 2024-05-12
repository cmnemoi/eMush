<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Drone;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Event\LoggableEventInterface;

abstract class AbstractDroneEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public function __construct(
        protected Drone $drone,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function getDrone(): Drone
    {
        return $this->drone;
    }

    public function getPlace(): Place
    {
        return $this->drone->getPlace();
    }

    public function getVisibility(): string
    {
        return VisibilityEnum::PUBLIC;
    }

    public function getLogParameters(): array
    {
        return [
            'drone_nickname' => $this->drone->getNickname(),
            'drone_serial_number' => $this->drone->getSerialNumber(),
        ];
    }

    public function getDaedalusLanguage(): string
    {
        return $this->drone->getDaedalus()->getLanguage();
    }
}
