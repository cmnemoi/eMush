<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractMushEvent extends Event
{
    protected \DateTime $time;
    protected string $reason;

    public function __construct(string $reason, \DateTime $time)
    {
        $this->reason = $reason;
        $this->time = $time;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
