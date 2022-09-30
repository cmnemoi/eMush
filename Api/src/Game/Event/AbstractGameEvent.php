<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    protected \DateTime $time;
    protected string $reason;
    private string $event;

    public function __construct(string $reason, \DateTime $time)
    {
        $this->reason = $reason;
        $this->time = $time;
    }

    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): string
    {
        return $this->event;
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
