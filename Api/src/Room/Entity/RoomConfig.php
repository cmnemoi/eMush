<?php


namespace Mush\Room\Entity;

class RoomConfig
{
    private string $name;

    private array $doors;

    private array $equipments;

    private array $items;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RoomConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getDoors(): array
    {
        return $this->doors;
    }

    public function setDoors(array $doors): RoomConfig
    {
        $this->doors = $doors;
        return $this;
    }

    public function getEquipments(): array
    {
        return $this->equipments;
    }

    public function setEquipments(array $equipments): RoomConfig
    {
        $this->equipments = $equipments;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): RoomConfig
    {
        $this->items = $items;
        return $this;
    }
}
