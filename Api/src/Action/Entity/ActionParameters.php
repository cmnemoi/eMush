<?php

namespace Mush\Action\Entity;

use Mush\Item\Entity\Door;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

class ActionParameters
{
    private ?Room $room = null;
    private ?Player $player = null;
    private ?GameItem $item = null;
    private ?Door $door = null;
    private ?string $message = null;

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

    public function getItem(): ?GameItem
    {
        return $this->item;
    }

    public function setItem(?GameItem $item): ActionParameters
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?String $message): ActionParameters
    {
        $this->message = $message;

        return $this;
    }
}
