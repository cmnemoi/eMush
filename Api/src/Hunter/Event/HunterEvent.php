<?php

namespace Mush\Hunter\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Hunter\Entity\Hunter;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Event\LoggableEventInterface;

class HunterEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const ASTEROID_DESTRUCTION = 'asteroid.destruction';
    public const HUNTER_DEATH = 'hunter.death';

    protected Hunter $hunter;
    protected string $visibility;

    public function __construct(Hunter $hunter, string $visibility, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->hunter = $hunter;
        $this->visibility = $visibility;
    }

    public function getHunter(): Hunter
    {
        return $this->hunter;
    }

    public function getPlace(): Place
    {
        return $this->hunter->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        return [
            $this->hunter->getLogKey() => $this->hunter->getLogName(),
        ];
    }
}
