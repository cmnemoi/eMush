<?php

namespace Mush\Game\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractTimeEvent extends Event
{
    protected \DateTime $time;
    protected ?Player $player = null;
    protected ?Daedalus $daedalus = null;
    protected ?Room $room = null;
    protected ?GameEquipment $gameEquipment = null;
    protected ?Status $status = null;

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

    public function getGameEquipment(): ?GameEquipment
    {
        return $this->gameEquipment;
    }

    public function setGameEquipment(?GameEquipment $gameEquipment): AbstractTimeEvent
    {
        $this->gameEquipment = $gameEquipment;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): AbstractTimeEvent
    {
        $this->status = $status;

        return $this;
    }
}
