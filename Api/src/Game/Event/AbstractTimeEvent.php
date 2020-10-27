<?php

namespace Mush\Game\Event;

use Mush\Item\Entity\GameItem;
use Symfony\Contracts\EventDispatcher\Event;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

class AbstractTimeEvent extends Event
{
    protected \DateTime $time;

    protected ?Player $player = null;

    protected ?Daedalus $daedalus = null;

    protected ?Room $room = null;

    protected ?GameItem $gameItem = null;

    public function __construct(Daedalus $daedalus, \DateTime $time)
    {
        $this->time = $time;
        $this->daedalus = $daedalus;
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

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getGameItem(): ?GameItem
    {
        return $this->gameItem;
    }

    public function setGameItem(?GameItem $gameItem): AbstractTimeEvent
    {
        $this->gameItem = $gameItem;
        return $this;
    }
}
