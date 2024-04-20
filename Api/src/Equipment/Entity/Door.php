<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionTargetName;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class Door extends GameEquipment
{
    #[ORM\ManyToMany(targetEntity: Place::class)]
    private Collection $rooms;

    public function __construct(EquipmentHolderInterface $equipmentHolder)
    {
        $this->rooms = new ArrayCollection();

        parent::__construct($equipmentHolder);
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function setRooms(Collection $rooms): static
    {
        $this->rooms = $rooms;

        foreach ($rooms as $room) {
            if (!$room->getDoors()->contains($this)) {
                $room->addDoor($this);
            }
        }

        return $this;
    }

    public function addRoom(Place $room): static
    {
        $this->rooms->add($room);

        if (!$room->getDoors()->contains($this)) {
            $room->addDoor($this);
        }

        return $this;
    }

    public function getOtherRoom(Place $currentRoom): Place
    {
        return $this->getRooms()->filter(static fn (Place $room) => $room !== $currentRoom)->first();
    }

    public function getLogName(): string
    {
        return EquipmentEnum::DOOR;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::EQUIPMENT;
    }

    public function getActionTargetName(array $context): string
    {
        return ActionTargetName::DOOR->value;
    }
}
