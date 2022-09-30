<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    protected \DateTime $time;
    protected string $reason;
    private string $eventName;

    public function __construct(string $reason, \DateTime $time)
    {
        $this->reason = $reason;
        $this->time = $time;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
