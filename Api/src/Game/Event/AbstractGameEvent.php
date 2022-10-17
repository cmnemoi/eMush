<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    protected \DateTime $time;
    protected array $reasons;
    private string $eventName;

    public function __construct(string $reason, \DateTime $time)
    {
        $this->reasons = [$reason];
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

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function addReason(string $reason)
    {
        $this->reasons = array_merge([$reason], $this->reasons);
    }

    public function setReasons(array $reasons): self
    {
        $this->reasons = $reasons;

        return $this;
    }
}
