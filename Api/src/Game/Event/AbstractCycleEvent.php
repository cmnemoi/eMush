<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractCycleEvent extends Event
{
    protected \DateTime $time;

    public function __construct(\DateTime $time)
    {
        $this->time = $time;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }
}
