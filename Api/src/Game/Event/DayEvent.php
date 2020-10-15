<?php

namespace Mush\Game\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DayEvent extends Event
{
    public const NEW_DAY = 'new.day';

    private \DateTime $time;

    private Player $player;

    private Daedalus $daedalus;

    public function __construct(\DateTime $time)
    {
        $this->time = $time;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): DayEvent
    {
        $this->player = $player;
        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): DayEvent
    {
        $this->daedalus = $daedalus;
        return $this;
    }
}