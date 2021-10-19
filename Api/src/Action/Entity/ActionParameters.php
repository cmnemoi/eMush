<?php

namespace Mush\Action\Entity;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class ActionParameters
{
    private ?Place $place = null;
    private ?Player $player = null;
    private ?GameEquipment $equipment = null;
    private ?GameItem $item = null;
    private ?Door $door = null;
    private string $message = '';

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @return static
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }

    /**
     * @return static
     */
    public function setEquipment(?GameEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getItem(): ?GameItem
    {
        return $this->item;
    }

    /**
     * @return static
     */
    public function setItem(?GameItem $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getDoor(): ?Door
    {
        return $this->door;
    }

    /**
     * @return static
     */
    public function setDoor(?Door $door): self
    {
        $this->door = $door;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return static
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
