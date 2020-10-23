<?php

namespace Mush\Game\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\CycleEvent;
use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

class AbstractTimeEvent extends Event
{
    protected \DateTime $time;

    protected ?Player $player = null;

    protected ?Daedalus $daedalus = null;

    protected ?Room $room = null;

    protected ?Item $item = null;

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

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }
}
