<?php

namespace Mush\RoomLog\Event;

use Mush\Place\Entity\Place;

interface LoggableEventInterface
{
    public function getPlace(): Place;

    public function getVisibility(): string;

    public function getLogParameters(): array;

    public function getTime(): \DateTime;
}
