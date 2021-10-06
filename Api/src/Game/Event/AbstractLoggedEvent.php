<?php

namespace Mush\Game\Event;

use Mush\Place\Entity\Place;

interface AbstractLoggedEvent
{
    public function getPlace(): Place;

    public function getVisibility(): string;

    public function getLogParameters(): array;

    public function getTime(): \DateTime;
}
