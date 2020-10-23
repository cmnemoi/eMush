<?php

namespace Mush\Action\Entity;

use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Door;
use Mush\Room\Entity\Room;

class ActionParameters
{
    private ?Room $room = null;
    private ?Player $player = null;
    private ?Item $item = null;
    private ?Door $door = null;

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): ActionParameters
    {
        $this->room = $room;
        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): ActionParameters
    {
        $this->player = $player;
        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): ActionParameters
    {
        $this->item = $item;
        return $this;
    }

    public function getDoor(): ?Door
    {
        return $this->door;
    }

    public function setDoor(?Door $door): ActionParameters
    {
        $this->door = $door;
        return $this;
    }
}
