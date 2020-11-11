<?php

namespace Mush\RoomLog\Entity;

use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;

class RoomLogParameter
{
    private ?Player $target = null;
    private ?Item $item = null;
    private ?int $number = null;

    public function getTarget(): ?Player
    {
        return $this->target;
    }

    public function setTarget(?Player $target): RoomLogParameter
    {
        $this->target = $target;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): RoomLogParameter
    {
        $this->item = $item;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): RoomLogParameter
    {
        $this->number = $number;

        return $this;
    }

    public function toArray()
    {
        return [
            'player' => $this->getTarget()->getPerson() ?? null,
            'item' => $this->getItem()->getName() ?? null,
            'number' => $this->getNumber(),
        ];
    }
}
